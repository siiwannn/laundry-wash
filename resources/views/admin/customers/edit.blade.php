@extends('layouts.app')
@section('title', 'Edit Customer - Laundry Wash')
@section('content')
<div class="row justify-content-center"><div class="col-lg-7"><h1 class="h3 fw-bold mb-4">Edit Customer</h1><div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('admin.customers.update', $customer) }}">@csrf @method('PUT')
@foreach(['name' => 'Nama', 'email' => 'Email', 'phone' => 'Nomor telepon'] as $field => $label)<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" class="form-control" value="{{ old($field, $customer->$field) }}" required></div>@endforeach
<div class="d-flex gap-2"><button class="btn btn-primary">Simpan</button><a class="btn btn-light" href="{{ route('admin.customers.show', $customer) }}">Batal</a></div></form></div></div></div></div>
@endsection
