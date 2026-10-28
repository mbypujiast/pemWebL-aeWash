@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="mb-3"><h3 class="fw-bold mb-0">Edit Customer</h3></div>
@include('customers._form', ['customer' => $customer])
@endsection
