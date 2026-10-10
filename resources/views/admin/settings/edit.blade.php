@extends('layouts.app')
@section('title', 'Pengaturan Harga - Laundry Wash')
@section('content')
<div class="row justify-content-center"><div class="col-lg-7">
<div class="mb-4"><p class="text-muted mb-0">Harga baru digunakan sebagai snapshot pada order berikutnya.</p></div>
<div class="card"><div class="card-body p-4"><form method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
@foreach(['pickup_fee' => 'Biaya pickup', 'delivery_fee' => 'Biaya delivery'] as $field => $label)
<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><div class="input-group"><span class="input-group-text">Rp</span><input id="{{ $field }}" name="{{ $field }}" type="number" min="0" class="form-control" value="{{ old($field, $setting->$field) }}" required></div></div>
@endforeach
<button class="btn btn-primary mt-2" type="submit">Simpan pengaturan</button></form></div></div>
</div></div>
@endsection
