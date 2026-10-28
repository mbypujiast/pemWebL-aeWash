@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="glass p-4 p-md-5">

            <div class="text-center mb-4">
                <div class="fs-1 fw-bold">æ-WASH</div>
                <div class="small text-secondary">Automated Efficient Washing System</div>
                <div class="fst-italic mt-1 small">I Wish You Wash Here</div>
            </div>

            <form method="POST" action="{{ route('login.process') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="nama@email.com" required autofocus>
                    @error('email')
                        <div class="small mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" required>
                    @error('password')
                        <div class="small mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-ae w-100">Masuk</button>
            </form>

            {{-- Hapus kotak ini saat aplikasi sudah final --}}
            <div class="demo-box mt-4">
                <div class="fw-semibold mb-1">Akun demo (password: <code>password</code>)</div>
                <div>admin@aewash.test</div>
                <div>staff@aewash.test</div>
                <div>customer@aewash.test</div>
            </div>
        </div>
    </div>
</div>
@endsection
