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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="preload" href="{{ asset('css/laundry-workspace.css') }}?v={{ filemtime(public_path('css/laundry-workspace.css')) }}" as="style">
    <link href="{{ asset('css/laundry-workspace.css') }}?v={{ filemtime(public_path('css/laundry-workspace.css')) }}" rel="stylesheet">

    <style>
        :root {
            --lw-primary: #c63f1f;
            --lw-secondary: #c63f1f;
            --lw-dark: #242320;
            --lw-light: #f5f4f0;
            --lw-surface: #ffffff;
            --lw-border: #e9e7e1;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--lw-light);
            color: #34332f;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .workspace-navbar {
            min-height: 76px;
            background: var(--lw-light) !important;
            border-bottom: 1px solid var(--lw-border);
            box-shadow: none !important;
        }

        .workspace-navbar .navbar-brand,
        .workspace-navbar .nav-link,
        .workspace-navbar .text-white {
            color: var(--lw-dark) !important;
        }

        .navbar-brand i {
            color: var(--lw-primary);
        }

        .card {
            border-radius: 16px;
            border: 1px solid var(--lw-border);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .btn {
            border-radius: 12px;
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
        .app-sidebar { width: 248px; position: fixed; inset: 0 auto 0 0; background: #fff; border-right: 1px solid var(--lw-border); padding: 24px 12px; overflow-y: auto; z-index: 1040; }
        .app-sidebar .nav-link { color: #605e58; border-radius: 8px; padding: 12px; margin-bottom: 3px; }
        .app-sidebar .nav-link:hover, .app-sidebar .nav-link.active { color: var(--lw-dark); background: #f0eee8; font-weight: 600; }
        .app-content { margin-left: 248px; }
        :focus-visible { outline: 3px solid rgba(198, 63, 31, .35); outline-offset: 2px; }
        @media (min-width: 992px) { #navbarMain .navbar-nav.me-auto { display: none; } }
        @media (max-width: 991.98px) {
            .app-sidebar { display: none; }
            .app-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
    @auth
        @unless(request()->routeIs('catalog'))
            <link href="{{ asset('css/workspace-consistency.css') }}?v={{ substr(sha1_file(public_path('css/workspace-consistency.css')), 0, 12) }}" rel="stylesheet">
        @endunless
    @endauth
</head>
<body class="{{ auth()->check() ? 'workspace-auth' : 'workspace-public' }} {{ request()->routeIs('catalog', 'home') ? 'catalog-page' : '' }} {{ request()->routeIs('*.dashboard') ? 'dashboard-page' : (auth()->check() && !request()->routeIs('catalog', 'home') ? 'workspace-detail-page' : '') }}">
    @auth
    <script>
        try {
            if (window.matchMedia('(min-width: 992px)').matches && window.localStorage.getItem('laundry-wash-sidebar-collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (error) {
            // The navigation script applies the default expanded state if storage is unavailable.
        }
    </script>
    @endauth
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2 workspace-navbar">
        <div class="container">
            @auth
                <span class="workspace-page-title workspace-admin-page-title">
                    @if(request()->routeIs('*.dashboard'))
                        <strong>{{ auth()->user()->isAdmin() ? 'Dashboard Laundry' : (auth()->user()->isCourier() ? 'Dashboard Kurir' : 'Dashboard Pelanggan') }}</strong>
                        @unless(auth()->user()->isCustomer())
                            <small>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</small>
                        @endunless
                    @else
                        <span>{{ \Illuminate\Support\Str::before($__env->yieldContent('title', 'Laundry Wash'), ' - ') }}</span>
                    @endif
                </span>
            @endauth
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-droplet-half fs-4"></i>
                <span>Laundry <span class="text-info">Wash</span></span>
            </a>

            @guest
                @if(request()->routeIs('catalog', 'home'))
                    <div class="catalog-primary-nav d-none d-md-flex" aria-label="Navigasi katalog">
                        <a href="#cara-kerja">Cara kerja</a>
                        <a href="#layanan">Layanan</a>
                        <a href="#tentang-kami">Tentang kami</a>
                    </div>
                @endif
            @endguest

            @auth
            <button id="mobile-sidebar-toggle" class="mobile-sidebar-toggle" type="button" aria-controls="workspace-sidebar" aria-expanded="false" aria-label="Buka navigasi" title="Buka navigasi"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i></button>
            @endauth

            <div class="navbar-collapse d-flex align-items-center" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-lg-center flex-row flex-lg-row gap-2 gap-lg-0">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item d-none d-xl-block">
                                <form class="workspace-search" action="{{ route('admin.orders.index') }}" method="GET" role="search">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                    <input type="search" name="search" placeholder="Search" aria-label="Cari nomor order atau pelanggan">
                                </form>
                            </li>
                            <li class="nav-item d-none d-xl-block">
                                <a class="workspace-filter-button" href="{{ route('admin.orders.index') }}#order-filters"><i class="bi bi-funnel" aria-hidden="true"></i> Filter</a>
                            </li>
                        @else
                            <li class="nav-item d-none d-xl-block">
                                <form id="workspace-page-search" class="workspace-search" role="search">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                    <input type="search" list="workspace-page-options" placeholder="Cari halaman" aria-label="Cari halaman sesuai akses Anda" required autocomplete="off">
                                    <datalist id="workspace-page-options"></datalist>
                                    <button type="submit" class="visually-hidden-focusable">Buka</button>
                                </form>
                            </li>
                        @endif
                        <li class="nav-item d-none d-lg-block">
                            <span class="workspace-icon-button workspace-icon-muted" role="img" aria-label="Notifikasi belum tersedia" title="Notifikasi belum tersedia"><i class="bi bi-bell" aria-hidden="true"></i></span>
                        </li>
                        @if(auth()->user()->isAdmin())
                        @elseif(auth()->user()->isCustomer())
                            <li class="nav-item d-none d-lg-block"><a class="workspace-icon-button" href="{{ route('customer.profile.edit') }}" aria-label="Pengaturan profil" title="Pengaturan profil"><i class="bi bi-gear" aria-hidden="true"></i></a></li>
                        @else
                            <li class="nav-item d-none d-lg-block"><a class="workspace-icon-button" href="{{ route('courier.dashboard') }}#courier-availability" aria-label="Status kerja" title="Status kerja"><i class="bi bi-sliders" aria-hidden="true"></i></a></li>
                        @endif
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

@auth
<aside id="workspace-sidebar" class="app-sidebar" aria-label="Navigasi utama">
    <div class="workspace-brand-row">
        <a class="workspace-brand" href="{{ url('/') }}"><i class="bi bi-droplet-half" aria-hidden="true"></i><span>Laundry Wash</span></a>
        <button id="sidebar-toggle" class="sidebar-toggle" type="button" aria-controls="workspace-sidebar" aria-expanded="true" aria-label="Ciutkan sidebar" title="Ciutkan sidebar"><i class="bi bi-layout-sidebar-inset sidebar-toggle-desktop-icon" aria-hidden="true"></i><i class="bi bi-x-lg sidebar-toggle-mobile-icon" aria-hidden="true"></i></button>
    </div>
    <div class="workspace-nav-label small text-uppercase text-muted fw-semibold px-2 mb-3">Menu {{ auth()->user()->role->label() }}</div>
    <nav class="nav flex-column">
        @if(auth()->user()->isAdmin())
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" title="Dashboard" aria-label="Dashboard"><i class="bi bi-grid me-2" aria-hidden="true"></i><span class="sidebar-link-label">Dashboard</span></a>
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}" title="Pesanan" aria-label="Pesanan"><i class="bi bi-bag-check me-2" aria-hidden="true"></i><span class="sidebar-link-label">Pesanan</span></a>
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}" title="Pelanggan" aria-label="Pelanggan"><i class="bi bi-people me-2" aria-hidden="true"></i><span class="sidebar-link-label">Pelanggan</span></a>
            <a class="nav-link {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}" href="{{ route('admin.couriers.index') }}" title="Kurir" aria-label="Kurir"><i class="bi bi-bicycle me-2" aria-hidden="true"></i><span class="sidebar-link-label">Kurir</span></a>
            <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}" title="Layanan" aria-label="Layanan"><i class="bi bi-tags me-2" aria-hidden="true"></i><span class="sidebar-link-label">Layanan</span></a>
            <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}" title="Pembayaran" aria-label="Pembayaran"><i class="bi bi-credit-card me-2" aria-hidden="true"></i><span class="sidebar-link-label">Pembayaran</span></a>
            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}" title="Laporan" aria-label="Laporan"><i class="bi bi-bar-chart me-2" aria-hidden="true"></i><span class="sidebar-link-label">Laporan</span></a>
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}" title="Pengaturan" aria-label="Pengaturan"><i class="bi bi-gear me-2" aria-hidden="true"></i><span class="sidebar-link-label">Pengaturan</span></a>
        @elseif(auth()->user()->isCourier())
            <a class="nav-link {{ request()->routeIs('courier.dashboard') ? 'active' : '' }}" href="{{ route('courier.dashboard') }}" title="Dashboard" aria-label="Dashboard"><i class="bi bi-grid me-2" aria-hidden="true"></i><span class="sidebar-link-label">Dashboard</span></a>
            <a class="nav-link {{ request()->routeIs('courier.history') ? 'active' : '' }}" href="{{ route('courier.history') }}" title="Riwayat" aria-label="Riwayat"><i class="bi bi-clock-history me-2" aria-hidden="true"></i><span class="sidebar-link-label">Riwayat</span></a>
        @else
            <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}" href="{{ route('customer.dashboard') }}" title="Dashboard" aria-label="Dashboard"><i class="bi bi-house me-2" aria-hidden="true"></i><span class="sidebar-link-label">Dashboard</span></a>
            <a class="nav-link {{ request()->routeIs('customer.orders.create') ? 'active' : '' }}" href="{{ route('customer.orders.create') }}" title="Pesanan baru" aria-label="Pesanan baru"><i class="bi bi-plus-circle me-2" aria-hidden="true"></i><span class="sidebar-link-label">Pesanan baru</span></a>
            <a class="nav-link {{ request()->routeIs('customer.orders.history') ? 'active' : '' }}" href="{{ route('customer.orders.history') }}" title="Riwayat" aria-label="Riwayat"><i class="bi bi-receipt me-2" aria-hidden="true"></i><span class="sidebar-link-label">Riwayat</span></a>
            <a class="nav-link {{ request()->routeIs('customer.addresses.*') ? 'active' : '' }}" href="{{ route('customer.addresses.index') }}" title="Alamat" aria-label="Alamat"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i><span class="sidebar-link-label">Alamat</span></a>
            <a class="nav-link {{ request()->routeIs('customer.profile.*') ? 'active' : '' }}" href="{{ route('customer.profile.edit') }}" title="Profil" aria-label="Profil"><i class="bi bi-person me-2" aria-hidden="true"></i><span class="sidebar-link-label">Profil</span></a>
        @endif
    </nav>
    <div class="sidebar-user-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="workspace-avatar"><i class="bi bi-person" aria-hidden="true"></i></span>
            <div class="min-w-0">
                <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                <small class="text-muted d-block text-truncate">{{ auth()->user()->email }}</small>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                Keluar
            </button>
        </form>
    </div>
</aside>
<div id="sidebar-backdrop" class="sidebar-backdrop" aria-hidden="true"></div>
@endauth

    <!-- Main Content Area -->
    <main id="main-content" tabindex="-1" class="py-4 flex-grow-1 app-content">
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
            <small>&copy; {{ date('Y') }} <strong>Laundry Wash</strong> &bull; Sistem Manajemen Laundry Terintegrasi</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle & Leaflet JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @auth
    <script src="{{ asset('js/workspace-navigation.js') }}?v={{ filemtime(public_path('js/workspace-navigation.js')) }}" defer></script>
    @endauth
    @stack('scripts')
</body>
</html>
