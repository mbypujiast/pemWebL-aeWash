@extends('layouts.app')

@section('title', 'Riwayat Order')

@section('content')
<div class="mb-3">
    <h3 class="fw-bold mb-0">Riwayat Order Saya</h3>
    <div class="text-secondary small">Lihat status terkini cucian Anda</div>
</div>

<div class="glass p-3 p-md-4">
    <form method="GET" action="{{ route('customer.orders') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-6 col-lg-4">
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nomor invoice">
        </div>
        <div class="col-auto"><button class="btn btn-glass" type="submit"><i class="bi bi-search"></i> Cari</button></div>
        @if ($search)
            <div class="col-auto"><a href="{{ route('customer.orders') }}" class="btn btn-glass">Reset</a></div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-glass table-hover align-middle">
            <thead>
                <tr>
                    <th>Invoice</th><th>Tanggal</th><th style="min-width:160px">Progress</th>
                    <th>Status</th><th class="text-end">Total</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a class="inv" href="{{ route('orders.show', $order) }}">{{ $order->invoice_number }}</a></td>
                        <td class="small text-secondary">{{ $order->order_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="track-bar"><span style="width: {{ $order->progress_percent }}%"></span></div>
                        </td>
                        <td>@include('orders.partials.status-badge', ['status' => $order->status])</td>
                        <td class="text-end">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        <td class="text-end"><a href="{{ route('orders.show', $order) }}" class="btn btn-glass btn-sm">Lacak</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state"><i class="bi bi-inbox fs-1"></i><div class="mt-2">Belum ada order.</div></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
