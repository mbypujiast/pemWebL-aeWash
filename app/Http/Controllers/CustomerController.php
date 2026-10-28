<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->when($request->query('q'), function ($query, $search) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                                           ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', ['customers' => $customers, 'search' => $request->query('q')]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Customer::create($this->validated($request));

        return redirect()->route('customers.index')->with('success', "Customer {$customer->name} berhasil ditambahkan.");
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request, $customer));

        return redirect()->route('customers.index')->with('success', "Customer {$customer->name} berhasil diperbarui.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        // Customer yang punya order tidak boleh dihapus agar riwayat order tetap utuh
        if ($customer->orders()->exists()) {
            return back()->with('error', "Customer \"{$customer->name}\" masih punya riwayat order, tidak bisa dihapus.");
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customer?->id)],
            'address' => ['nullable', 'string', 'max:255'],
        ], ValidationMessages::id(), [
            'name' => 'Nama', 'phone' => 'No. HP', 'address' => 'Alamat',
        ]);
    }
}
