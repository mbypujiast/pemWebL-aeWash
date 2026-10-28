<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /** Daftar order + filter status & pencarian (staff/admin) */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, array_keys(Order::STATUSES), true) ? $status : null;
        $search = $request->query('q');

        $orders = Order::with('customer')
            ->status($status)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Jumlah order per status (1 query) untuk tab filter
        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('orders.index', [
            'orders'        => $orders,
            'statuses'      => Order::STATUSES,
            'statusCounts'  => $statusCounts,
            'currentStatus' => $status,
            'search'        => $search,
        ]);
    }

    /** Form order baru */
    public function create()
    {
        return view('orders.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'services'  => Service::active()->orderBy('id')->get(),
        ]);
    }

    /** Simpan order + item + riwayat status pertama */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data, $request) {

            // 1. Tentukan customer: pilih yang lama atau buat baru (cocokkan lewat no. HP)
            $customer = ! empty($data['customer_id'])
                ? Customer::findOrFail($data['customer_id'])
                : Customer::firstOrCreate(
                    ['phone' => $data['new_customer_phone']],
                    ['name'  => $data['new_customer_name']]
                );

            // 2. Ambil harga dari database (jangan percaya harga dari browser)
            $services = Service::active()
                ->whereIn('id', collect($data['items'])->pluck('service_id'))
                ->get()
                ->keyBy('id');

            // 3. Hitung item, total berat, total harga, estimasi selesai
            $totalWeight = 0;
            $totalPrice  = 0;
            $maxDuration = 1;
            $itemRows    = [];

            foreach ($data['items'] as $item) {
                $service  = $services[$item['service_id']];
                $weight   = (float) $item['weight_kg'];
                $subtotal = round($weight * (float) $service->price_per_kg, 2);

                $totalWeight += $weight;
                $totalPrice  += $subtotal;
                $maxDuration  = max($maxDuration, $service->duration_days);

                $itemRows[] = [
                    'service_id'   => $service->id,
                    'fabric_type'  => $item['fabric_type'],
                    'weight_kg'    => $weight,
                    'price_per_kg' => $service->price_per_kg,   // snapshot harga
                    'subtotal'     => $subtotal,
                ];
            }

            // 4. Simpan order
            $order = Order::create([
                'invoice_number'        => Order::generateInvoiceNumber(),
                'customer_id'           => $customer->id,
                'staff_id'              => $request->user()->id,
                'status'                => 'diterima',
                'payment_status'        => 'belum_bayar',
                'total_weight'          => $totalWeight,
                'total_price'           => $totalPrice,
                'order_date'            => now()->toDateString(),
                'estimated_finish_date' => now()->addDays($maxDuration)->toDateString(),
                'notes'                 => $data['notes'] ?? null,
            ]);

            // 5. Simpan item & riwayat status pertama
            $order->items()->createMany($itemRows);
            $order->statusHistories()->create([
                'changed_by' => $request->user()->id,
                'status'     => 'diterima',
                'note'       => 'Order diterima di kasir',
            ]);

            return $order;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', "Order {$order->invoice_number} berhasil dibuat.");
    }

    /** Detail order + timeline status (staff/admin, atau customer pemilik order) */
    public function show(Order $order)
    {
        $this->authorizeView($order);

        $order->load(['customer', 'staff', 'items.service', 'statusHistories.changer']);

        return view('orders.show', [
            'order'  => $order,
            'stages' => Order::STATUSES,
        ]);
    }

    /** Tampilan struk untuk dicetak */
    public function receipt(Order $order)
    {
        $this->authorizeView($order);

        $order->load(['customer', 'items.service']);

        return view('orders.receipt', ['order' => $order]);
    }

    /** Form edit data order (item & harga tidak diubah agar struk/invoice tetap konsisten) */
    public function edit(Order $order)
    {
        return view('orders.edit', [
            'order'     => $order->load('customer'),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'           => ['required', 'exists:customers,id'],
            'estimated_finish_date' => ['nullable', 'date', 'after_or_equal:' . $order->order_date->toDateString()],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ], ValidationMessages::id(), [
            'customer_id' => 'Customer', 'estimated_finish_date' => 'Estimasi selesai', 'notes' => 'Catatan',
        ]);

        $order->update($data);

        return redirect()->route('orders.show', $order)->with('success', 'Data order berhasil diperbarui.');
    }

    /** Hapus order (admin). Item & riwayat status ikut terhapus lewat cascade di database. */
    public function destroy(Order $order): RedirectResponse
    {
        $invoice = $order->invoice_number;
        $order->delete();

        return redirect()->route('orders.index')->with('success', "Order {$invoice} berhasil dihapus.");
    }

    /** Majukan status order & catat ke status_history */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        $keys         = array_keys(Order::STATUSES);
        $currentIndex = array_search($order->status, $keys);
        $newIndex     = array_search($validated['status'], $keys);

        // Hanya boleh maju (boleh melompat, mis. Cuci Kering tidak perlu disetrika)
        if ($newIndex <= $currentIndex) {
            return back()->with('error', 'Status hanya bisa dimajukan ke tahap berikutnya.');
        }

        DB::transaction(function () use ($order, $validated, $request) {
            $order->update(['status' => $validated['status']]);

            $order->statusHistories()->create([
                'changed_by' => $request->user()->id,
                'status'     => $validated['status'],
                'note'       => $validated['note'] ?? 'Status diperbarui oleh staff',
            ]);
        });

        return back()->with('success', 'Status diubah menjadi ' . Order::STATUSES[$validated['status']] . '.');
    }

    /** Verifikasi pembayaran oleh staff */
    public function verifyPayment(Order $order): RedirectResponse
    {
        if ($order->isPaid()) {
            return back()->with('error', 'Pembayaran order ini sudah terverifikasi.');
        }

        $order->update([
            'payment_status' => 'lunas',
            'paid_at'        => now(),
        ]);

        return back()->with('success', 'Pembayaran ' . $order->invoice_number . ' ditandai lunas.');
    }

    /** Riwayat order milik customer yang login */
    public function history(Request $request)
    {
        $customer = $request->user()->customer;

        abort_unless($customer, 403, 'Akun Anda belum terhubung dengan data pelanggan.');

        $orders = $customer->orders()
            ->when($request->query('q'), fn ($query, $q) => $query->where('invoice_number', 'like', "%{$q}%"))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('orders.history', [
            'orders'   => $orders,
            'statuses' => Order::STATUSES,
            'search'   => $request->query('q'),
        ]);
    }

    /** Staff & admin boleh melihat semua; customer hanya order miliknya */
    private function authorizeView(Order $order): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin', 'staff')) {
            return;
        }

        abort_unless(
            $order->customer?->user_id === $user->id,
            403,
            'Order ini bukan milik Anda.'
        );
    }
}
