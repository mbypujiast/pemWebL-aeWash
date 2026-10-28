@php $s = $service ?? null; @endphp

<form method="POST" action="{{ $s ? route('admin.services.update', $s) : route('admin.services.store') }}">
    @csrf
    @if ($s) @method('PUT') @endif

    @include('partials.errors')

    <div class="glass p-4 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama layanan</label>
                <input type="text" name="name" value="{{ old('name', $s?->name) }}" class="form-control" placeholder="Cuci Setrika" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga per kg (Rp)</label>
                <input type="number" name="price_per_kg" value="{{ old('price_per_kg', isset($s) ? (int) $s->price_per_kg : '') }}"
                       class="form-control" min="0" step="500" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lama pengerjaan (hari)</label>
                <input type="number" name="duration_days" value="{{ old('duration_days', $s?->duration_days ?? 2) }}"
                       class="form-control" min="1" max="30" required>
            </div>

            {{-- input hidden 0 supaya checkbox yang tidak dicentang tetap terkirim --}}
            <div class="col-md-6">
                <input type="hidden" name="is_express" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_express" value="1" id="is_express"
                           @checked(old('is_express', $s?->is_express ?? false))>
                    <label class="form-check-label" for="is_express">Layanan Express</label>
                </div>
            </div>
            <div class="col-md-6">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           @checked(old('is_active', $s?->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Aktif (muncul di form order)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ae"><i class="bi bi-check2-circle"></i> Simpan</button>
        <a href="{{ route('admin.services.index') }}" class="btn btn-glass">Batal</a>
    </div>
</form>
