@extends('layouts.auth')

@section('title', 'Masuk - Laundry Wash')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1" style="letter-spacing:-.5px">Selamat Datang 👋</h3>
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
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small text-muted" for="remember">
                    Ingat saya di perangkat ini
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            Masuk Sekarang
        </button>
    </form>

    <div class="position-relative my-4 text-center">
        <hr>
        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">atau</span>
    </div>

    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 py-2 d-inline-flex align-items-center justify-content-center gap-2">
        <svg class="google-brand-icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.2 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.2-.1-2.3-.4-3.5z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.2 29.3 4 24 4c-7.7 0-14.4 4.4-17.7 10.7z"/>
            <path fill="#4CAF50" d="M24 44c7.5 0 14-4.9 17.3-11.8l-7.2-5.1C32.2 32.5 28.5 36 24 36c-5.3 0-9.7-3.3-11.5-7.9l-6.5 5C9.3 39.5 16.2 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.5l.1-.1 7.2 5.1C37.9 38.9 44 34 44 24c0-1.2-.1-2.3-.4-3.5z"/>
        </svg>
        <span>Masuk dengan Google</span>
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
