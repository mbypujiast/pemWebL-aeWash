@extends('layouts.app')

@section('title', $order->invoice_number)

@section('content')
@php
    $isStaff      = auth()->user()->hasRole('admin', 'staff');
    $keys         = array_keys($stages);
    $currentIndex = array_search($order->status, $keys);
    $forward      = array_slice($stages, $currentIndex + 1, null, true);   // hanya tahap setelah status sekarang
    $icons        = ['diterima' => 'bi-inbox', 'dicuci' => 'bi-droplet', 'disetrika' => 'bi-fire', 'siap_ambil' => 'bi-bag-check'];
    $rp           = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

{{-- Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-1">{{ $order->invoice_number }}</h3>
        @include('orders.partials.status-badge', ['status' => $order->status])
        @include('orders.partials.pay-badge', ['payment' => $order->payment_status])
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('orders.receipt', $order) }}" target="_blank" class="btn btn-ae"><i class="bi bi-printer"></i> Cetak Struk</a>
        @if ($isStaff)
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-glass"><i class="bi bi-pencil"></i> Edit</a>
        @endif
        @if (auth()->user()->isAdmin())
            <form method="POST" action="{{ route('orders.destroy', $order) }}"
                  onsubmit="return confirm('Hapus order {{ $order->invoice_number }} beserta riwayatnya? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-glass text-danger"><i class="bi bi-trash"></i> Hapus</button>
            </form>
        @endif
        <a href="{{ $isStaff ? route('orders.index') : route('customer.orders') }}" class="btn btn-glass">Kembali</a>
    </div>
</div>

{{-- Tracking --}}
<div class="glass p-4 mb-3">
    <div class="d-flex justify-content-between mb-2">
        <span class="fw-semibold">Progress Cucian</span>
        <span class="fw-bold">{{ $order->progress_percent }}%</span>
    </div>
    <div class="track-bar"><span id="track-fill" data-percent="{{ $order->progress_percent }}"></span></div>

    <div class="tracker">
        @foreach ($stages as $key => $label)
            @php $i = array_search($key, $keys); @endphp
            <div class="step {{ $i <= $currentIndex ? 'done' : '' }} {{ $i === $currentIndex ? 'current' : '' }}">
                <div class="dot"><i class="bi {{ $icons[$key] ?? 'bi-circle' }}"></i></div>
                {{ $label }}
            </div>
        @endforeach
    </div>
</div>

<div class="row g-3">
    {{-- Kiri: item + timeline --}}
    <div class="col-lg-8">
        <div class="glass p-4 mb-3">
            <h5 class="fw-bold mb-3">Rincian Cucian</h5>
            <div class="table-responsive">
                <table class="table table-glass">
                    <thead>
                        <tr>
                            <th>Layanan</th><th>Jenis kain</th>
                            <th class="text-end">Berat</th><th class="text-end">Harga/kg</th><th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->service->name }}</td>
                                <td>{{ $item->fabric_type }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($item->weight_kg, 2, ',', '.'), '0'), ',') }} kg</td>
                                <td class="text-end">{{ $rp($item->price_per_kg) }}</td>
                                <td class="text-end">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th class="text-end fs-5">{{ $rp($order->total_price) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="glass p-4">
            <h5 class="fw-bold mb-3">Riwayat Status</h5>
            <ul class="timeline">
                @foreach ($order->statusHistories->reverse() as $h)
                    <li>
                        <span class="tl-dot"></span>
                        <div class="fw-semibold">{{ $stages[$h->status] ?? $h->status }}</div>
                        <div class="small text-secondary">
                            {{ $h->created_at->locale('id')->translatedFormat('d F Y, H:i') }}
                            @if ($h->changer) &middot; oleh {{ $h->changer->name }} @endif
                        </div>
                        @if ($h->note)<div class="small">{{ $h->note }}</div>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Kanan: info + aksi --}}
    <div class="col-lg-4">
        @if ($isStaff)
            <div class="glass p-4 mb-3">
                <h5 class="fw-bold mb-3">Update Status</h5>

                @if ($forward)
                    <form method="POST" action="{{ route('orders.status', $order) }}">
                        @csrf @method('PATCH')
                        <label class="form-label">Pindah ke tahap</label>
                        <select name="status" class="form-select mb-2">
                            @foreach ($forward as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="note" class="form-control mb-3" placeholder="Catatan (opsional)" maxlength="255">
                        <button type="submit" class="btn btn-ae w-100"><i class="bi bi-arrow-right-circle"></i> Perbarui Status</button>
                    </form>
                @else
                    <div class="text-secondary small"><i class="bi bi-check-circle"></i> Order sudah di tahap akhir.</div>
                @endif

                <hr>
                <h6 class="fw-bold">Pembayaran</h6>
                @if ($order->isPaid())
                    <div class="small text-secondary">
                        Lunas pada {{ $order->paid_at?->locale('id')->translatedFormat('d F Y, H:i') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('orders.payment', $order) }}"
                          onsubmit="return confirm('Tandai pembayaran {{ $order->invoice_number }} sebagai lunas?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-glass w-100"><i class="bi bi-cash-coin"></i> Verifikasi Pembayaran</button>
                    </form>
                @endif
            </div>
        @endif

        <div class="glass p-4 mb-3">
            <h5 class="fw-bold mb-3">Customer</h5>
            <div class="fw-semibold">{{ $order->customer->name }}</div>
            <div class="small text-secondary">{{ $order->customer->phone }}</div>
            @if ($order->customer->address)
                <div class="small text-secondary">{{ $order->customer->address }}</div>
            @endif
        </div>

        <div class="glass p-4">
            <h5 class="fw-bold mb-3">Info Order</h5>
            <div class="mb-2"><div class="info-label">Tanggal masuk</div>{{ $order->order_date->locale('id')->translatedFormat('d F Y') }}</div>
            <div class="mb-2"><div class="info-label">Estimasi selesai</div>{{ $order->estimated_finish_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</div>
            <div class="mb-2"><div class="info-label">Total berat</div>{{ rtrim(rtrim(number_format($order->total_weight, 2, ',', '.'), '0'), ',') }} kg</div>
            <div class="mb-2"><div class="info-label">Diterima oleh</div>{{ $order->staff?->name ?? '-' }}</div>
            @if ($order->notes)
                <div><div class="info-label">Catatan</div>{{ $order->notes }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Animasi progress bar: dari 0 ke persentase sebenarnya
    document.addEventListener('DOMContentLoaded', () => {
        const bar = document.getElementById('track-fill');
        setTimeout(() => bar.style.width = bar.dataset.percent + '%', 150);
    });
</script>
@endpush
