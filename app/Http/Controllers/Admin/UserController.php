<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->query('q'), function ($query, $search) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                                           ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->query('role'), fn ($query, $role) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.create', ['availableCustomers' => $this->availableCustomers()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), ValidationMessages::id(), $this->attributes());

        $user = DB::transaction(function () use ($data) {
            $user = User::create(Arr::only($data, ['name', 'email', 'phone', 'role', 'password']));
            $this->syncCustomer($user, $data['role'], $data['customer_id'] ?? null);
            return $user;
        });

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user'               => $user,
            'availableCustomers' => $this->availableCustomers($user),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->rules($user), ValidationMessages::id(), $this->attributes());

        // Jangan sampai sistem kehilangan admin terakhir
        if ($user->isAdmin() && $data['role'] !== 'admin' && $this->isLastAdmin()) {
            return back()->withInput()->with('error', 'Role admin terakhir tidak boleh diubah.');
        }

        $payload = Arr::only($data, ['name', 'email', 'phone', 'role']);
        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];   // otomatis di-hash oleh cast di model User
        }

        DB::transaction(function () use ($user, $payload, $data) {
            $user->update($payload);
            $this->syncCustomer($user, $data['role'], $data['customer_id'] ?? null);
        });

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }
        if ($user->isAdmin() && $this->isLastAdmin()) {
            return back()->with('error', 'Admin terakhir tidak boleh dihapus.');
        }

        $user->delete();   // order & riwayat tetap ada (staff_id / changed_by menjadi NULL)

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dihapus.");
    }

    // ---------------- helper ----------------

    private function rules(?User $user = null): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role'  => ['required', Rule::in(['admin', 'staff', 'customer'])],
            // Saat edit, password boleh kosong (berarti tidak diganti)
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            // Hubungkan akun customer dengan data pelanggan yang belum punya akun
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where(
                fn ($q) => $q->where(fn ($w) => $w->whereNull('user_id')->orWhere('user_id', $user?->id))
            )],
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'Nama', 'email' => 'Email', 'phone' => 'No. HP', 'role' => 'Role',
            'password' => 'Password', 'customer_id' => 'Data customer',
        ];
    }

    private function isLastAdmin(): bool
    {
        return User::where('role', 'admin')->count() <= 1;
    }

    /** Customer yang belum punya akun (+ yang sudah terhubung ke user ini saat edit) */
    private function availableCustomers(?User $user = null)
    {
        return Customer::where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $user?->id))
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
    }

    private function syncCustomer(User $user, string $role, ?int $customerId): void
    {
        // Lepas dulu semua tautan lama, lalu pasang yang baru (jika role = customer)
        Customer::where('user_id', $user->id)->update(['user_id' => null]);

        if ($role === 'customer' && $customerId) {
            Customer::whereKey($customerId)->update(['user_id' => $user->id]);
        }
    }
}
