@php $c = $customer ?? null; @endphp

<form method="POST" action="{{ $c ? route('customers.update', $c) : route('customers.store') }}">
    @csrf
    @if ($c) @method('PUT') @endif

    @include('partials.errors')

    <div class="glass p-4 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="name" value="{{ old('name', $c?->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone', $c?->phone) }}" class="form-control" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="col-12">
                <label class="form-label">Alamat (opsional)</label>
                <textarea name="address" rows="2" class="form-control">{{ old('address', $c?->address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ae"><i class="bi bi-check2-circle"></i> Simpan</button>
        <a href="{{ route('customers.index') }}" class="btn btn-glass">Batal</a>
    </div>
</form>
