@extends('layouts.app')

@section('title', 'Edit Layanan')

@section('content')
<div class="mb-3"><h3 class="fw-bold mb-0">Edit Layanan</h3></div>
@include('admin.services._form', ['service' => $service])
@endsection
