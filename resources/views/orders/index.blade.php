@extends('layouts.app')

@php $isDashboard = request()->routeIs('staff.dashboard'); @endphp
@section('title', $isDashboard ? 'Dashboard Staff' : 'Daftar Order')

@section('content')
@php $totalAll = $statusCounts->sum(); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-0">{{ $isDashboard ? 'Dashboard Staff' : 'Daftar Order' }}</h3>
        <div class="text-secondary small">Pantau dan perbarui status cucian</div>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-ae"><i class="bi bi-plus-lg"></i> Order Baru</a>
</div>

{{-- Kartu ringkasan = filter status --}}
<div class="row g-3 mb-4">
    @foreach (['' => 'Semua'] + $statuses as $key => $label)
        <div class="col-6 col-md">
            <a href="{{ route('orders.index', array_filter(['status' => $key, 'q' => $search])) }}"
               class="glass stat-card {{ (string) $currentStatus === (string) $key ? 'active' : '' }}">
                <div class="num">{{ $key === '' ? $totalAll : ($statusCounts[$key] ?? 0) }}</div>
                <div class="lbl">{{ $label }}</div>
            </a>
        </div>
    @endforeach
</div>

<div class="glass p-3 p-md-4">
    {{-- Pencarian --}}
    <form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-3">
        @if ($currentStatus)
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif
        <div class="col-12 col-md-6 col-lg-4">
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari invoice / nama customer">
        </div>
        <div class="col-auto">
            <button class="btn btn-glass" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
        @if ($search || $currentStatus)
            <div class="col-auto"><a href="{{ route('orders.index') }}" class="btn btn-glass">Reset</a></div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-glass table-hover align-middle">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th class="text-end">Berat</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Bayar</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a class="inv" href="{{ route('orders.show', $order) }}">{{ $order->invoice_number }}</a></td>
                        <td>{{ $order->customer->name }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($order->total_weight, 2, ',', '.'), '0'), ',') }} kg</td>
                        <td class="text-end">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        <td>@include('orders.partials.status-badge', ['status' => $order->status])</td>
                        <td>@include('orders.partials.pay-badge', ['payment' => $order->payment_status])</td>
                        <td class="small text-secondary">{{ $order->order_date->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-glass btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-inbox fs-1"></i>
                                <div class="mt-2">Belum ada order yang cocok.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
