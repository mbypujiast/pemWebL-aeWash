@extends('layouts.app')

@section('title', 'Order Baru')

@section('content')
@php
    $serviceData = $services->map(fn ($s) => [
        'id'    => $s->id,
        'name'  => $s->name,
        'price' => (float) $s->price_per_kg,
        'days'  => $s->duration_days,
    ])->values();
    $oldItems = array_values(old('items', [['service_id' => '', 'fabric_type' => '', 'weight_kg' => '']]));
    $mode     = old('new_customer_name') ? 'new' : 'existing';
@endphp

<div class="mb-3">
    <h3 class="fw-bold mb-0">Order Baru</h3>
    <div class="text-secondary small">Catat cucian masuk dan hitung biaya otomatis</div>
</div>

<form method="POST" action="{{ route('orders.store') }}">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Periksa kembali isian Anda:</div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Customer --}}
    <div class="glass p-4 mb-3">
        <h5 class="fw-bold mb-3"><i class="bi bi-person"></i> Customer</h5>

        <div class="btn-group mb-3" role="group">
            <input type="radio" class="btn-check" name="customer_mode" id="mode-existing" value="existing" @checked($mode === 'existing')>
            <label class="btn btn-glass" for="mode-existing">Customer lama</label>

            <input type="radio" class="btn-check" name="customer_mode" id="mode-new" value="new" @checked($mode === 'new')>
            <label class="btn btn-glass" for="mode-new">Customer baru</label>
        </div>

        <div id="box-existing">
            <select name="customer_id" class="form-select">
                <option value="">-- Pilih customer --</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} — {{ $c->phone }}</option>
                @endforeach
            </select>
        </div>

        <div id="box-new" class="row g-3 d-none">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="new_customer_name" value="{{ old('new_customer_name') }}" class="form-control" placeholder="Nama customer">
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="new_customer_phone" value="{{ old('new_customer_phone') }}" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
        </div>
    </div>

    {{-- Item cucian --}}
    <div class="glass p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-basket"></i> Item Cucian</h5>
            <button type="button" id="btn-add" class="btn btn-glass btn-sm"><i class="bi bi-plus-lg"></i> Tambah item</button>
        </div>

        <div id="items-container"></div>

        <div class="total-box d-flex flex-wrap justify-content-between gap-3 mt-3">
            <div>
                <div class="info-label">Total berat</div>
                <div class="fw-bold fs-5" id="sum-weight">0 kg</div>
            </div>
            <div>
                <div class="info-label">Estimasi selesai</div>
                <div class="fw-bold fs-5" id="sum-days">-</div>
            </div>
            <div class="text-end">
                <div class="info-label">Estimasi total</div>
                <div class="fw-bold fs-4" id="sum-total">Rp 0</div>
            </div>
        </div>
    </div>

    {{-- Catatan --}}
    <div class="glass p-4 mb-4">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="notes" rows="2" class="form-control" placeholder="Mis. noda di kemeja putih, jangan pakai pelembut">{{ old('notes') }}</textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ae"><i class="bi bi-check2-circle"></i> Simpan Order</button>
        <a href="{{ route('orders.index') }}" class="btn btn-glass">Batal</a>
    </div>
</form>

{{-- Template satu baris item. __INDEX__ diganti oleh JavaScript --}}
<template id="item-template">
    <div class="item-row">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Layanan</label>
                <select name="items[__INDEX__][service_id]" class="form-select js-service" required>
                    <option value="">-- Pilih --</option>
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->price_per_kg, 0, ',', '.') }}/kg)</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Jenis kain</label>
                <input type="text" name="items[__INDEX__][fabric_type]" class="form-control js-fabric" placeholder="Katun, jeans, ..." required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Berat (kg)</label>
                <input type="number" name="items[__INDEX__][weight_kg]" class="form-control js-weight" step="0.1" min="0.1" max="100" placeholder="0.0" required>
            </div>
            <div class="col-4 col-md-2 text-end">
                <div class="info-label">Subtotal</div>
                <div class="fw-semibold js-subtotal">Rp 0</div>
            </div>
            <div class="col-2 col-md-1 text-end">
                <button type="button" class="btn btn-glass js-remove" title="Hapus item"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
    const services = @json($serviceData);          // [{id, name, price, days}]
    const oldItems = @json($oldItems);             // isian sebelumnya jika validasi gagal

    const container = document.getElementById('items-container');
    const template  = document.getElementById('item-template');
    let rowIndex = 0;

    const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    const findService = id => services.find(s => String(s.id) === String(id));

    function addRow(data = {}) {
        const holder = document.createElement('div');
        holder.innerHTML = template.innerHTML.replaceAll('__INDEX__', rowIndex++).trim();
        const row = holder.firstElementChild;

        row.querySelector('.js-service').value = data.service_id ?? '';
        row.querySelector('.js-fabric').value  = data.fabric_type ?? '';
        row.querySelector('.js-weight').value  = data.weight_kg ?? '';

        container.appendChild(row);
        refresh();
    }

    // Hitung subtotal tiap baris + ringkasan
    function refresh() {
        let totalWeight = 0, totalPrice = 0, maxDays = 0;
        const rows = container.querySelectorAll('.item-row');

        rows.forEach(row => {
            const service = findService(row.querySelector('.js-service').value);
            const weight  = parseFloat(row.querySelector('.js-weight').value) || 0;
            const subtotal = service ? service.price * weight : 0;

            row.querySelector('.js-subtotal').textContent = rupiah(subtotal);
            row.querySelector('.js-remove').disabled = rows.length === 1;

            totalWeight += weight;
            totalPrice  += subtotal;
            if (service && weight > 0) maxDays = Math.max(maxDays, service.days);
        });

        document.getElementById('sum-weight').textContent = totalWeight.toFixed(1).replace('.', ',') + ' kg';
        document.getElementById('sum-total').textContent  = rupiah(totalPrice);
        document.getElementById('sum-days').textContent   = maxDays ? maxDays + ' hari' : '-';
    }

    document.getElementById('btn-add').addEventListener('click', () => addRow());

    container.addEventListener('input', refresh);
    container.addEventListener('change', refresh);
    container.addEventListener('click', e => {
        const btn = e.target.closest('.js-remove');
        if (btn) { btn.closest('.item-row').remove(); refresh(); }
    });

    // Pindah antara customer lama / baru (input yang disembunyikan tidak ikut terkirim)
    function setMode(mode) {
        const existing = document.getElementById('box-existing');
        const fresh    = document.getElementById('box-new');
        existing.classList.toggle('d-none', mode !== 'existing');
        fresh.classList.toggle('d-none', mode !== 'new');
        existing.querySelectorAll('select').forEach(el => el.disabled = mode !== 'existing');
        fresh.querySelectorAll('input').forEach(el => el.disabled = mode !== 'new');
    }
    document.querySelectorAll('input[name="customer_mode"]').forEach(radio =>
        radio.addEventListener('change', e => setMode(e.target.value))
    );

    // Inisialisasi
    setMode(document.querySelector('input[name="customer_mode"]:checked').value);
    oldItems.forEach(item => addRow(item));
</script>
@endpush
