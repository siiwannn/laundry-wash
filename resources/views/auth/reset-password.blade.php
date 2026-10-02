@extends('layouts.auth')

@section('title', 'Reset Password - Laundry Wash')

@section('content')
<div class="auth-header">
    <div class="brand-icon"><i class="bi bi-shield-lock"></i></div>
    <h4 class="fw-bold mb-1">Buat password baru</h4>
    <p class="text-muted small mb-0">Gunakan minimal 8 karakter yang sulit ditebak.</p>
</div>
<div class="auth-body">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password baru</label>
            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Simpan password baru</button>
    </form>
</div>
@endsection
