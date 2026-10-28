@php
    $u    = $user ?? null;
    $role = old('role', $u?->role ?? 'staff');
@endphp

<form method="POST" action="{{ $u ? route('admin.users.update', $u) : route('admin.users.store') }}">
    @csrf
    @if ($u) @method('PUT') @endif

    @include('partials.errors')

    <div class="glass p-4 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="name" value="{{ old('name', $u?->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $u?->email) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP (opsional)</label>
                <input type="text" name="phone" value="{{ old('phone', $u?->phone) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    @foreach (['admin' => 'Admin (Owner)', 'staff' => 'Staff Laundry', 'customer' => 'Customer'] as $key => $label)
                        <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Hanya untuk role customer: hubungkan ke data pelanggan --}}
            <div class="col-12" id="box-customer">
                <label class="form-label">Hubungkan dengan data customer (opsional)</label>
                <select name="customer_id" class="form-select">
                    <option value="">-- Tidak dihubungkan --</option>
                    @foreach ($availableCustomers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $u?->customer?->id) == $c->id)>{{ $c->name }} — {{ $c->phone }}</option>
                    @endforeach
                </select>
                <div class="form-text">Agar customer ini bisa melihat riwayat ordernya sendiri.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Password {{ $u ? '(kosongkan jika tidak diubah)' : '' }}</label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" {{ $u ? '' : 'required' }}>
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi password</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" {{ $u ? '' : 'required' }}>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ae"><i class="bi bi-check2-circle"></i> Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-glass">Batal</a>
    </div>
</form>

@push('scripts')
<script>
    // Kotak "hubungkan customer" hanya tampil untuk role customer
    const roleSelect  = document.getElementById('role');
    const customerBox = document.getElementById('box-customer');
    function toggleCustomerBox() {
        const isCustomer = roleSelect.value === 'customer';
        customerBox.classList.toggle('d-none', !isCustomer);
        customerBox.querySelector('select').disabled = !isCustomer;
    }
    roleSelect.addEventListener('change', toggleCustomerBox);
    toggleCustomerBox();
</script>
@endpush
