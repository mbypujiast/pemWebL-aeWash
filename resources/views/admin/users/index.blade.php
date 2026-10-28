@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="fw-bold mb-0">Kelola User</h3>
        <div class="text-secondary small">Akun admin, staff, dan customer</div>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-ae"><i class="bi bi-person-plus"></i> Tambah User</a>
</div>

<div class="glass p-3 p-md-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-5 col-lg-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / email">
        </div>
        <div class="col-8 col-md-3 col-lg-2">
            <select name="role" class="form-select">
                <option value="">Semua role</option>
                @foreach (['admin', 'staff', 'customer'] as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-glass" type="submit"><i class="bi bi-search"></i> Cari</button></div>
        @if (request('q') || request('role'))
            <div class="col-auto"><a href="{{ route('admin.users.index') }}" class="btn btn-glass">Reset</a></div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-glass table-hover align-middle">
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>No. HP</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td class="fw-semibold">
                            {{ $u->name }}
                            @if ($u->id === auth()->id()) <span class="small text-secondary">(Anda)</span> @endif
                        </td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge badge-role">{{ ucfirst($u->role) }}</span></td>
                        <td class="small text-secondary">{{ $u->phone ?? '-' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-glass btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-glass btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">Tidak ada user yang cocok.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
