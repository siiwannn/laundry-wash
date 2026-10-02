@extends('layouts.auth')

@section('title', 'Lupa Password - Laundry Wash')

@section('content')
<div class="auth-header">
    <div class="brand-icon"><i class="bi bi-key"></i></div>
    <h4 class="fw-bold mb-1">Lupa password?</h4>
    <p class="text-muted small mb-0">Masukkan email akun untuk menerima tautan reset.</p>
</div>
<div class="auth-body">
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Kirim tautan reset</button>
    </form>
    <a href="{{ route('login') }}" class="btn btn-link w-100 mt-3 text-decoration-none">Kembali ke login</a>
</div>
@endsection
