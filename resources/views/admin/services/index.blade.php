@extends('layouts.app')

@section('title', 'Master Layanan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-0">Master Layanan & Harga</h3>
        <div class="text-secondary small">Harga order lama tidak ikut berubah (tersimpan sebagai snapshot)</div>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn btn-ae"><i class="bi bi-plus-lg"></i> Tambah Layanan</a>
</div>

<div class="glass p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-glass table-hover align-middle">
            <thead>
                <tr>
                    <th>Layanan</th><th class="text-end">Harga / kg</th><th class="text-end">Durasi</th>
                    <th>Tipe</th><th>Status</th><th class="text-end">Dipakai</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $s)
                    <tr>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td class="text-end">Rp {{ number_format($s->price_per_kg, 0, ',', '.') }}</td>
                        <td class="text-end">{{ $s->duration_days }} hari</td>
                        <td>
                            @if ($s->is_express) <span class="badge-status s-diterima">Express</span>
                            @else <span class="small text-secondary">Reguler</span> @endif
                        </td>
                        <td>
                            @if ($s->is_active) <span class="badge-pay pay-lunas">Aktif</span>
                            @else <span class="badge-pay pay-belum_bayar">Nonaktif</span> @endif
                        </td>
                        <td class="text-end">{{ $s->order_items_count }}x</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.services.edit', $s) }}" class="btn btn-glass btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus layanan {{ $s->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-glass btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">Belum ada layanan.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
