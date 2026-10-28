@extends('layouts.app')

@section('title', 'Data Customer')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-0">Data Customer</h3>
        <div class="text-secondary small">Pelanggan laundry</div>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-ae"><i class="bi bi-person-plus"></i> Tambah Customer</a>
</div>

<div class="glass p-3 p-md-4">
    <form method="GET" action="{{ route('customers.index') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-6 col-lg-4">
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nama / no. HP">
        </div>
        <div class="col-auto"><button class="btn btn-glass" type="submit"><i class="bi bi-search"></i> Cari</button></div>
        @if ($search)
            <div class="col-auto"><a href="{{ route('customers.index') }}" class="btn btn-glass">Reset</a></div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-glass table-hover align-middle">
            <thead>
                <tr><th>Nama</th><th>No. HP</th><th>Alamat</th><th class="text-end">Order</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td>{{ $c->phone }}</td>
                        <td class="small text-secondary">{{ $c->address ?? '-' }}</td>
                        <td class="text-end">{{ $c->orders_count }}x</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('customers.edit', $c) }}" class="btn btn-glass btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('customers.destroy', $c) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus customer {{ $c->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-glass btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">Tidak ada customer yang cocok.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $customers->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
