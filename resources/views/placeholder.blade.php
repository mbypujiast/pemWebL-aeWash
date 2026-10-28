@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="glass p-4 p-md-5">
    <h2 class="fw-bold mb-1">{{ $title }}</h2>
    <p class="text-secondary">{{ $desc ?? '' }}</p>
    <hr class="opacity-25">
    <p class="mb-1">Halo, <strong>{{ auth()->user()->name }}</strong></p>
    <p class="mb-0">Role: <span class="badge badge-role">{{ auth()->user()->role }}</span></p>
</div>
@endsection
