@extends('layouts.app')

@section('title', 'Dashboard Owner')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $today = now()->locale('id')->translatedFormat('l, d F Y');
@endphp

<div class="mb-3">
    <h3 class="fw-bold mb-0">Dashboard Owner</h3>
    <div class="text-secondary small">{{ $today }}</div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="glass kpi">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-label">Pendapatan hari ini</div>
            <div class="kpi-value">{{ $rp($revenueToday) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass kpi">
            <div class="kpi-icon sky"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-label">Pendapatan bulan ini</div>
            <div class="kpi-value">{{ $rp($revenueMonth) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass kpi">
            <div class="kpi-icon mint"><i class="bi bi-receipt"></i></div>
            <div class="kpi-label">Order bulan ini</div>
            <div class="kpi-value">{{ number_format($ordersThisMonth, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="glass kpi">
            <div class="kpi-icon rose"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-label">Belum dibayar</div>
            <div class="kpi-value">{{ $rp($unpaidTotal) }}</div>
        </div>
    </div>
</div>

{{-- Grafik + status --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="glass p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Order per Minggu</h5>
                <span class="small text-secondary">8 minggu terakhir</span>
            </div>
            <div class="chart-wrap"><canvas id="weeklyChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="glass p-4 h-100">
            <h5 class="fw-bold mb-3">Order per Status</h5>
            @foreach ($statuses as $key => $label)
                <a class="status-row" href="{{ route('orders.index', ['status' => $key]) }}">
                    @include('orders.partials.status-badge', ['status' => $key])
                    <span class="count">{{ $statusCounts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Customer loyal --}}
<div class="glass p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-trophy"></i> 5 Customer Paling Loyal</h5>
    <div class="table-responsive">
        <table class="table table-glass align-middle">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Customer</th>
                    <th class="text-end">Jumlah order</th>
                    <th class="text-end">Total belanja</th>
                    <th>Order terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topCustomers as $i => $c)
                    <tr>
                        <td><span class="rank {{ $i < 3 ? 'top' . ($i + 1) : '' }}">{{ $i + 1 }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $c->name }}</div>
                            <div class="small text-secondary">{{ $c->phone }}</div>
                        </td>
                        <td class="text-end fw-semibold">{{ $c->orders_count }}x</td>
                        <td class="text-end">{{ $rp($c->total_spent) }}</td>
                        <td class="small text-secondary">
                            {{ \Illuminate\Support\Carbon::parse($c->last_order_at)->locale('id')->translatedFormat('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">Belum ada data order.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($weeklyLabels);
    const data   = @json($weeklyOrders);

    const canvas = document.getElementById('weeklyChart');
    const gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(99, 102, 241, .95)');
    gradient.addColorStop(1, 'rgba(56, 189, 248, .55)');

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah order',
                data: data,
                backgroundColor: gradient,
                borderRadius: 12,
                borderSkipped: false,
                maxBarThickness: 46,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: items => 'Minggu mulai ' + items[0].label,
                        label: ctx => ctx.parsed.y + ' order',
                    },
                },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, color: '#6b7194' }, grid: { color: 'rgba(99, 102, 241, .1)' } },
                x: { ticks: { color: '#6b7194' }, grid: { display: false } },
            },
        },
    });
</script>
@endpush
