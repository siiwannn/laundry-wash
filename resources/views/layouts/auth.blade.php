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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            padding: 2rem 1rem;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }

        .auth-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }

        .auth-body {
            padding: 2rem;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: #3B82F6;
            color: #ffffff;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.4);
        }

        .form-control:focus, .form-select:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.2);
        }

        .btn-primary {
            background-color: #3B82F6;
            border-color: #3B82F6;
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #0369a1;
            border-color: #0369a1;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-card">
        @yield('content')
    </div>

    <!-- Bootstrap JS & Leaflet JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
