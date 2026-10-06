@extends('layouts.app')

@section('title', 'Profil Saya - Laundry Wash')

@section('content')
<div class="workspace-page-heading d-flex align-items-center justify-content-between mb-4">
    <div><p class="text-muted mb-0">Kelola identitas dan keamanan akun.</p></div>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-4">Informasi akun</h2>
            <form method="POST" action="{{ route('customer.profile.update') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label for="name" class="form-label">Nama lengkap</label><input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                <div class="mb-4"><label for="phone" class="form-label">Nomor telepon</label><input id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required></div>
                <button class="btn btn-primary" type="submit">Simpan perubahan</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-4">Ganti password</h2>
            <form method="POST" action="{{ route('customer.profile.password') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label for="current_password" class="form-label">Password saat ini</label><input id="current_password" name="current_password" type="password" class="form-control" required></div>
                <div class="mb-3"><label for="new_password" class="form-label">Password baru</label><input id="new_password" name="password" type="password" class="form-control" required></div>
                <div class="mb-4"><label for="password_confirmation" class="form-label">Konfirmasi password</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required></div>
                <button class="btn btn-outline-primary" type="submit">Perbarui password</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
