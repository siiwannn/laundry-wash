@extends('layouts.auth')

@section('title', 'Masuk - Laundry Wash')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1" style="letter-spacing:-.5px">Selamat Datang</h3>
    <p class="text-muted small mb-0">Masuk untuk mengelola laundry kamu dengan mudah.</p>
</div>

<div class="auth-body">
    @if(session('info'))
        <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 small mb-3">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" id="loginForm">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small text-muted" for="remember">
                    Ingat saya di perangkat ini
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
        </button>
    </form>

    <div class="position-relative my-4 text-center">
        <hr>
        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">atau</span>
    </div>

    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 py-2">
        <i class="bi bi-google me-2"></i>Masuk dengan Google
    </a>

    <div class="mt-4 pt-3 border-top text-center">
        <p class="small text-muted mb-3">Belum punya akun pelanggan?</p>
        <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm w-100 py-2">
            <i class="bi bi-person-plus me-1"></i> Buat Akun Customer Baru
        </a>
    </div>

    <!-- Quick Demo Accounts Switcher for Easy Review -->
</div>

@push('scripts')
<script>
function fillCredentials(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>
@endpush
@endsection
