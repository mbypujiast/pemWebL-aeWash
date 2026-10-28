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
