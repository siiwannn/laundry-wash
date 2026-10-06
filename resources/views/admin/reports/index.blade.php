@extends('layouts.app')

@section('title', 'Laporan & Analitik - Admin')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <p class="text-muted mb-0">Analisis pendapatan, perputaran pesanan, dan efektivitas kurir lapangan.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Mulai:</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $revenueReport['start_date'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Selesai:</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $revenueReport['end_date'] }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Terapkan Rentang
                </button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-light btn-sm border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Revenue Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3 border-start border-success border-4">
            <span class="text-muted small text-uppercase fw-semibold">Total Pendapatan Terverifikasi (Lunas)</span>
            <h3 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($revenueReport['total_revenue'], 0, ',', '.') }}</h3>
            <small class="text-muted mt-1 d-block">Pada periode {{ date('d M Y', strtotime($revenueReport['start_date'])) }} s.d {{ date('d M Y', strtotime($revenueReport['end_date'])) }}</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 border-start border-primary border-4">
            <span class="text-muted small text-uppercase fw-semibold">Jumlah Pesanan Selesai / Terbayar</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $revenueReport['total_orders'] }} Transaksi</h3>
            <small class="text-muted mt-1 d-block">Rata-rata omzet per transaksi: Rp {{ $revenueReport['total_orders'] > 0 ? number_format($revenueReport['total_revenue'] / $revenueReport['total_orders'], 0, ',', '.') : 0 }}</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Transactions Table -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Rincian Transaksi Terbayar</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th>No. Order</th>
                            <th>Pelanggan</th>
                            <th>Berat</th>
                            <th>Total Tagihan</th>
                            <th>Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueReport['orders'] as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td>{{ $order->actual_weight ?: $order->estimated_weight ?: '-' }} kg</td>
                                <td class="fw-bold text-success">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td><small class="text-muted">{{ $order->updated_at->format('d/m/Y H:i') }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada transaksi terbayar pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Courier Performance -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-award me-2 text-warning"></i> Kinerja Kurir</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($courierStats as $c)
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0">{{ $c['name'] }}</h6>
                                <span class="badge bg-light text-dark border">{{ $c['courier_profile']['vehicle_plate'] ?? '-' }}</span>
                            </div>
                            <div class="small text-muted mb-2">{{ $c['email'] }}</div>
                            <div class="row g-2 text-center" style="font-size: 0.8rem;">
                                <div class="col-4">
                                    <div class="bg-light p-1 rounded border">
                                        <div class="fw-bold text-primary">{{ $c['completed_pickups'] }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Jemput</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-light p-1 rounded border">
                                        <div class="fw-bold text-success">{{ $c['completed_deliveries'] }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Antar</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-light p-1 rounded border">
                                        <div class="fw-bold text-warning">{{ $c['active_tasks'] }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Aktif</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-3 text-muted">Belum ada data kurir.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
