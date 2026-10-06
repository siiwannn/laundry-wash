<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login - Laundry Wash')</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS for map picker -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body {
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            background: #ffffff;
        }

        .auth-split { display: flex; min-height: 100vh; }

        .auth-side {
            flex: 1 1 50%;
            background: linear-gradient(150deg, #a93015 0%, #c63f1f 55%, #f2794b 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .auth-side::before, .auth-side::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
        }
        .auth-side::before { width: 300px; height: 300px; top: -150px; left: -150px; }
        .auth-side::after { width: 220px; height: 220px; bottom: -80px; left: -80px; }

        .rain { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .rain span {
            position: absolute; top: -30px; width: 4px; height: 36px; border-radius: 4px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
            box-shadow: 0 0 6px rgba(255,255,255,.6);
            animation: rain-fall linear infinite;
        }
        @keyframes rain-fall {
            to { transform: translateY(110vh); }
        }

        .auth-side .bubble {
            position: absolute; border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.35);
            pointer-events: none;
        }

        .auth-form-panel { position: relative; }

        .auth-side-brand { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.3rem; }
        .auth-side-brand span { width: 44px; height: 44px; border-radius: 12px; background: #fff; color: #c63f1f; display: grid; place-items: center; font-size: 1.4rem; }

        .auth-side-hero { text-align: center; position: relative; z-index: 1; margin-top: -40px; }
        .auth-side-hero .big-icon {
            width: 320px; height: 320px; margin: 0 auto 28px;
            display: grid; place-items: center;
        }
        .auth-side-hero h1 { font-size: 2rem; font-weight: 700; letter-spacing: -1px; margin-bottom: 0; }
        .auth-side-hero p { color: rgba(255, 255, 255, 0.85); max-width: 400px; margin: 14px auto 0; line-height: 1.7; font-size: .95rem; }

        .auth-side-foot { color: rgba(255, 255, 255, 0.75); font-size: 0.8rem; }

        .auth-form-panel {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
            max-height: 100vh;
            overflow-y: auto;
            background: #ffffff;
            min-height: 100vh;
            align-self: stretch;
        }
        .auth-form-inner {
            width: 100%; max-width: 440px;
            background: #ffffff;
            padding: 0;
            margin: auto 0;
        }

        .brand-icon {
            width: 56px; height: 56px;
            background: #c63f1f; color: #ffffff;
            border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.75rem; margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgba(198, 63, 31, 0.4);
        }

        .form-control:focus, .form-select:focus {
            border-color: #c63f1f;
            box-shadow: 0 0 0 0.25rem rgba(198, 63, 31, 0.15);
        }

        .btn-primary { background-color: #c63f1f; border-color: #c63f1f; font-weight: 600; }
        .btn-primary:hover { background-color: #a93015; border-color: #a93015; }

        .text-primary { color: #c63f1f !important; }
        .btn-outline-primary { --bs-btn-color: #b4361a; --bs-btn-border-color: #e8d2ca; --bs-btn-hover-bg: #c63f1f; --bs-btn-hover-color: #fff; --bs-btn-hover-border-color: #c63f1f; }
        .input-group-text { border-color: transparent; color: #c63f1f; }
        .form-control, .form-select { min-height: 46px; }
        .form-control, .input-group-text { background: #f7f5f1; border: 1px solid transparent; }
        .form-control:focus { background: #fff; }
        .btn { min-height: 46px; }
        .auth-header { padding: 0 0 1.5rem; text-align: center; }
        .auth-body { padding: 0; }

        @media (min-width: 992px) {
            body { overflow: hidden; }
            .form-control, .btn, .input-group-text { min-height: 42px; }
            .auth-form-panel { padding: 40px 48px; }
        }
        @media (max-width: 991.98px) {
            .auth-side { display: none; }
            .auth-form-panel { padding: 32px 20px; background: #ffffff; }
            body { background: radial-gradient(circle at 12% 18%, rgba(255,255,255,.24) 0 28px, transparent 29px), radial-gradient(circle at 88% 78%, rgba(255,255,255,.16) 0 52px, transparent 53px), linear-gradient(150deg, #b7381b 0%, #d84b25 55%, #f47c4c 100%); }
            .auth-form-panel { background: transparent; }
            .auth-form-inner { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,.25); }
        }
        @media (max-width: 575.98px) {
            body { overflow-x: hidden; }
            .auth-form-panel { min-height: 100dvh; padding: 24px 16px; align-items: flex-start; }
            .auth-form-inner { max-width: none; padding: 24px 20px; margin: 0; border-radius: 14px; }
            .auth-header { padding-bottom: 1.25rem; }
            .brand-icon { width: 50px; height: 50px; font-size: 1.5rem; }
            .auth-body .btn { width: 100%; min-height: 46px; }
            .auth-body .input-group .btn { width: auto; }
            .auth-body .d-flex { flex-wrap: wrap; gap: 10px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-split">
        <div class="auth-side" aria-hidden="true">
            <div class="rain" aria-hidden="true">
                <span style="left:8%;animation-duration:0.9s;animation-delay:0.12s"></span>
                <span style="left:17%;animation-duration:0.97s;animation-delay:0.24s"></span>
                <span style="left:26%;animation-duration:1.04s;animation-delay:0.36s"></span>
                <span style="left:35%;animation-duration:1.11s;animation-delay:0.48s"></span>
                <span style="left:44%;animation-duration:1.18s;animation-delay:0.6s"></span>
                <span style="left:53%;animation-duration:1.25s;animation-delay:0.72s"></span>
                <span style="left:62%;animation-duration:1.32s;animation-delay:0.84s"></span>
                <span style="left:71%;animation-duration:1.39s;animation-delay:0.96s"></span>
                <span style="left:80%;animation-duration:1.46s;animation-delay:1.08s"></span>
                <span style="left:90%;animation-duration:1.53s;animation-delay:1.2s"></span>
            </div>
            <div class="auth-side-brand"><span><i class="bi bi-droplet-half"></i></span> Laundry Wash</div>
            <div class="auth-side-hero">
                <div class="big-icon"><img src="{{ asset('images/laundry-hero-t.png') }}" alt="Laundry Wash" style="width:320px;height:320px;object-fit:contain;"></div>
                <h1>Cucian Beres, Hari Kamu Tenang</h1>
                <p>Jemput, cuci, antar — pantau setiap proses laundry kamu secara real-time dalam satu aplikasi.</p>
            </div>
            <span class="bubble" style="width:60px;height:60px;top:25%;left:12%;"></span>
            <span class="bubble" style="width:28px;height:28px;top:32%;left:22%;"></span>
            <span class="bubble" style="width:44px;height:44px;bottom:22%;right:15%;"></span>
            <div class="auth-side-foot">&copy; {{ date('Y') }} Laundry Wash &middot; Smart Laundry Management</div>
        </div>
        <div class="auth-form-panel">
            <div class="auth-form-inner">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Leaflet JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
