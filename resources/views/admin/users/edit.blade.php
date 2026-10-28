@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="mb-3">
    <h3 class="fw-bold mb-0">Edit User</h3>
    <div class="text-secondary small">{{ $user->email }}</div>
</div>
@include('admin.users._form', ['user' => $user])
@endsection
