@extends('layouts.app')

@section('title', 'Edit ' . $order->invoice_number)

@section('content')
<div class="mb-3">
    <h3 class="fw-bold mb-1">Edit Order</h3>
    <div class="text-secondary small">{{ $order->invoice_number }} &middot; item cucian & harga tidak dapat diubah</div>
</div>

<form method="POST" action="{{ route('orders.update', $order) }}">
    @csrf @method('PUT')

    @include('partials.errors')

    <div class="glass p-4 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select" required>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $order->customer_id) == $c->id)>{{ $c->name }} — {{ $c->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Estimasi selesai</label>
                <input type="date" name="estimated_finish_date"
                       value="{{ old('estimated_finish_date', $order->estimated_finish_date?->toDateString()) }}"
                       min="{{ $order->order_date->toDateString() }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ae"><i class="bi bi-check2-circle"></i> Simpan Perubahan</button>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-glass">Batal</a>
    </div>
</form>
@endsection
