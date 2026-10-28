@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
<div class="glass p-5 text-center mx-auto" style="max-width: 520px;">
    <div class="display-3 mb-2"><i class="bi bi-shield-lock"></i></div>
    <h2 class="fw-bold">403 · Akses Ditolak</h2>
    <p class="opacity-75">{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>
    @auth
        <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn btn-ae mt-2">Kembali ke halaman saya</a>
    @else
        <a href="{{ route('login') }}" class="btn btn-ae mt-2">Ke halaman login</a>
    @endauth
</div>
@endsection
