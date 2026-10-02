<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laundry Wash - Smart Laundry Management')</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --lw-primary: #0d6efd;
            --lw-secondary: #0dcaf0;
            --lw-dark: #1e293b;
            --lw-light: #f8fafc;
            --lw-surface: #ffffff;
            --lw-border: #e2e8f0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--lw-light);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            color: var(--lw-secondary);
        }

        .card {
            border-radius: 12px;
            border: 1px solid var(--lw-border);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .badge-status {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }

        .nav-pills .nav-link {
            border-radius: 8px;
            color: #64748b;
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background-color: var(--lw-primary);
            color: #ffffff;
        }

        .stat-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }

        #map, .leaflet-container {
            border-radius: 10px;
            z-index: 1;
        }

        footer {
            margin-top: auto;
            border-top: 1px solid var(--lw-border);
            background: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-droplet-half fs-4"></i>
                <span>Laundry <span class="text-info">Wash</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active text-info fw-semibold' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active text-info fw-semibold' : '' }}" href="{{ route('admin.orders.index') }}">
                                    <i class="bi bi-bag-check me-1"></i> Kelola Order
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active text-info fw-semibold' : '' }}" href="{{ route('admin.services.index') }}">
                                    <i class="bi bi-tags me-1"></i> Layanan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.couriers.*') ? 'active text-info fw-semibold' : '' }}" href="{{ route('admin.couriers.index') }}">
                                    <i class="bi bi-bicycle me-1"></i> Kurir
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active text-info fw-semibold' : '' }}" href="{{ route('admin.reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan
                                </a>
                            </li>
                        @elseif(auth()->user()->isCourier())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('courier.dashboard') ? 'active text-info fw-semibold' : '' }}" href="{{ route('courier.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Tugas Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('courier.history') ? 'active text-info fw-semibold' : '' }}" href="{{ route('courier.history') }}">
                                    <i class="bi bi-clock-history me-1"></i> Riwayat Tugas
                                </a>
                            </li>
                        @elseif(auth()->user()->isCustomer())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active text-info fw-semibold' : '' }}" href="{{ route('customer.dashboard') }}">
                                    <i class="bi bi-house me-1"></i> Beranda
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.orders.create') ? 'active text-info fw-semibold' : '' }}" href="{{ route('customer.orders.create') }}">
                                    <i class="bi bi-plus-circle me-1"></i> Order Baru
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.orders.history') ? 'active text-info fw-semibold' : '' }}" href="{{ route('customer.orders.history') }}">
                                    <i class="bi bi-receipt me-1"></i> Riwayat Order
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.addresses.*') ? 'active text-info fw-semibold' : '' }}" href="{{ route('customer.addresses.index') }}">
                                    <i class="bi bi-geo-alt me-1"></i> Buku Alamat
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="badge {{ auth()->user()->role->badgeClass() ?? 'bg-secondary' }}">
                                    {{ auth()->user()->role->label() }}
                                </span>
                                <span>{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li class="px-3 py-2 border-bottom">
                                    <small class="text-muted d-block">Masuk sebagai</small>
                                    <span class="fw-semibold">{{ auth()->user()->email }}</span>
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2">
                                            <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">Daftar Pelanggan</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="py-4 flex-grow-1">
        <div class="container">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="fw-semibold mb-1"><i class="bi bi-x-octagon-fill me-1"></i> Terdapat kesalahan pada pengisian form:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted">
        <div class="container">
            <small>&copy; {{ date('Y') }} <strong>Laundry Wash</strong> &bull; Sistem Manajemen Laundry Terintegrasi &bull; Versi 1.0 (Laravel 12)</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle & Leaflet JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
