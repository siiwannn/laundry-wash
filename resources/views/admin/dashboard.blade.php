@extends('layouts.app')

@section('title', 'Admin Dashboard - Laundry Wash')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1">Dashboard Operasional</h3>
        <p class="text-muted mb-0">Ringkasan status transaksi, kurir lapangan, dan progres laundry hari ini.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm me-2">
            <i class="bi bi-list-check me-1"></i> Semua Pesanan
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Keuangan
        </a>
    </div>
</div>

<!-- Key Performance Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card p-3 h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Pendapatan</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h4>
                </div>
                <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-check-circle text-success me-1"></i> Dari order terbayar lunas</small>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card p-3 h-100 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Pesanan Aktif</span>
                    <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['active_orders'] }}</h4>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-arrow-repeat me-1"></i> Sedang dalam proses cuci/antar</small>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card p-3 h-100 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Pesanan</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['total_orders'] }}</h4>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success">
                    <i class="bi bi-bag-check fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-graph-up me-1"></i> Keseluruhan transaksi sistem</small>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card p-3 h-100 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Kurir Lapangan</span>
                    <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['total_couriers'] }}</h4>
                </div>
                <div class="rounded-circle bg-info-subtle p-3 text-info">
                    <i class="bi bi-bicycle fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-geo-alt me-1"></i> Siap bertugas antar jemput</small>
        </div>
    </div>
</div>

<!-- Action Required Tabs / Alerts -->
@if($pendingOrders->count() > 0)
    <div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-bell-fill fs-4 text-warning"></i>
            <div>
                <strong>{{ $pendingOrders->count() }} Pesanan Baru Menunggu Konfirmasi!</strong>
                <div class="small">Segera verifikasi pesanan agar dapat diproses atau ditugaskan ke kurir pickup.</div>
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm fw-semibold">
            Tinjau Sekarang
        </a>
    </div>
@endif

<div class="row g-4">
    <!-- Recent Orders Table -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Pesanan Terbaru</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-link btn-sm text-decoration-none p-0">Lihat Semua &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>No. Order</th>
                            <th>Customer</th>
                            <th>Layanan</th>
                            <th>Status Order</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $order->order_number }}</span>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->customer->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $order->customer->phone ?? '-' }}</small>
                                </td>
                                <td>
                                    @foreach($order->items as $item)
                                        <span class="badge bg-light text-dark border">{{ $item->service->name ?? 'Layanan' }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $order->payment_status->badgeClass() }}">
                                        {{ $order->payment_status->label() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                        Detail &bull; Kelola
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Operations & Courier Status -->
    <div class="col-lg-4">
        <!-- Quick Action Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-1 text-warning"></i> Operasional Cepat</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.services.create') }}" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-plus-circle me-2 text-primary"></i> Tambah Layanan Laundry Baru
                    </a>
                    <a href="{{ route('admin.couriers.create') }}" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-person-plus me-2 text-success"></i> Daftarkan Akun Kurir Baru
                    </a>
                    <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-bicycle me-2 text-warning"></i> Penugasan Kurir Pickup
                    </a>
                    <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-box-seam me-2 text-info"></i> Penugasan Kurir Delivery
                    </a>
                </div>
            </div>
        </div>

        <!-- Workflow Status Breakdown Card -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-1 text-primary"></i> Distribusi Status Order</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach(\App\Enums\OrderStatus::cases() as $st)
                        @php $cnt = $stats['status_counts'][$st->value] ?? 0; @endphp
                        @if($cnt > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <span class="small">{{ $st->label() }}</span>
                                <span class="badge {{ $st->badgeClass() }}">{{ $cnt }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
