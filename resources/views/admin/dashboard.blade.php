@extends('layouts.app')

@section('title', 'Admin Dashboard - Laundry Wash')

@section('content')
<div class="dashboard-heading admin-dashboard-heading">
    <div>
        <p class="mb-0">Ringkasan operasional, pembayaran, dan antrean order terbaru.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="dashboard-range" aria-label="Periode dashboard">
            <a @class(['active' => request('range', 'today') === 'today']) href="{{ route('admin.dashboard', ['range' => 'today']) }}">Hari ini</a>
            <a @class(['active' => request('range') === 'week']) href="{{ route('admin.dashboard', ['range' => 'week']) }}">7 hari</a>
            <a @class(['active' => request('range') === 'month']) href="{{ route('admin.dashboard', ['range' => 'month']) }}">30 hari</a>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">Lihat pesanan</a>
    </div>
</div>

<section class="dashboard-summary admin-summary" aria-label="Ringkasan operasional">
    <div class="row g-3 admin-summary-grid">
        <div class="col-md-4 col-xl-3"><x-dashboard-metric label="Order masuk" :value="$pendingOrders->count()" icon="basket" tone="orange" note="Perlu ditinjau admin" /></div>
        <div class="col-md-4 col-xl-3"><x-dashboard-metric label="Sedang diproses" :value="$stats['active_orders']" icon="arrow-repeat" tone="blue" note="Belum selesai atau dibatalkan" /></div>
        <div class="col-md-4 col-xl-3"><x-dashboard-metric label="Siap diantar" :value="$readyForDelivery->count()" icon="truck" tone="green" note="Sudah dibayar customer" /></div>
        <div class="col-xl-3">
            <div class="admin-revenue-card h-100 reference-revenue">
                <div class="revenue-legend"><span></span> Pembayaran diterima</div>
                <div class="revenue-total"><div class="revenue-label"><span class="metric-icon metric-icon-green" aria-hidden="true"><i class="bi bi-cash-stack"></i></span><span>Pendapatan</span></div><div class="admin-revenue-value">Rp {{ number_format($revenueChart['total'], 0, ',', '.') }}</div><span class="admin-data-note">7 hari terakhir</span></div>
                <div class="revenue-spark" role="img" aria-label="Pendapatan tujuh hari terakhir">
                    @foreach($revenueChart['days'] as $day)
                        <div title="{{ $day['label'] }}: Rp {{ number_format($day['amount'], 0, ',', '.') }}"><div class="revenue-spark-rail"><span style="height: {{ $day['amount'] / $revenueChart['max'] * 100 }}%"></span></div><small>{{ $day['label'] }}</small></div>
                    @endforeach
                </div>
                <div class="revenue-caption">Pendapatan 7 hari dari pembayaran yang telah diterima.<a href="{{ route('admin.reports.index') }}">Lihat laporan</a></div>
            </div>
        </div>
    </div>
</section>

<div class="row g-4 admin-detail-grid">
    <section class="col-xl-8" aria-labelledby="orders-title">
        <div class="card admin-volume-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div><h2 id="orders-title" class="dashboard-section-title mb-1">Volume order harian</h2><p class="small text-muted mb-0">Order terbaru yang tercatat di sistem.</p></div>
                    <a href="{{ route('admin.orders.index') }}" class="dashboard-text-link">Rincian</a>
                </div>
                @php($maxVolume = max(2, ceil(collect($dailyVolume)->max('count') / 2) * 2))
                <div class="admin-chart-frame">
                <div class="admin-chart-axis" aria-hidden="true"><span>{{ $maxVolume }}</span><span>{{ $maxVolume / 2 }}</span><span>0</span></div>
                <div class="admin-volume-chart" aria-label="Grafik volume order harian">
                    @foreach($dailyVolume as $day)
                        <div class="admin-volume-bar-group"><div class="admin-volume-bar-rail"><i style="height: {{ ($day['count'] / $maxVolume) * 100 }}%"><span>{{ $day['count'] }}</span></i></div><small>{{ $day['label'] }}</small></div>
                    @endforeach
                </div>
                </div>
                <div class="admin-dashboard-note mt-3">Jumlah order dihitung dari transaksi yang dibuat pada periode {{ $periodLabel }}.</div>
                <div class="row g-2 mt-3">
                    <div class="col-md-6"><div class="admin-mini-stat"><span>Rata-rata order per hari</span><strong>{{ number_format(collect($dailyVolume)->avg('count'), 1, ',', '.') }}</strong><small>Order pada periode aktif</small></div></div>
                    <div class="col-md-6"><div class="admin-mini-stat"><span>Order aktif</span><strong>{{ $stats['active_orders'] }}</strong><small>Belum selesai atau dibatalkan</small></div></div>
                </div>
            </div>
        </div>
    </section>

    <aside class="col-xl-4" aria-labelledby="queue-title">
        <div class="card admin-order-queue h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2"><div><h2 id="queue-title" class="dashboard-section-title mb-1">Antrean order</h2><small class="text-muted">{{ $recentOrders->count() }} order terbaru</small></div><a href="{{ route('admin.orders.index') }}" class="dashboard-text-link">Semua</a></div>
                <div class="admin-queue-list">
                    @forelse($recentOrders->take(7) as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="queue-order-item">
                            <span class="queue-order-avatar">{{ collect(explode(' ', $order->customer->name ?? 'Laundry Wash'))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('') }}</span>
                            <span class="queue-order-details"><strong>{{ $order->customer->name ?? 'Customer' }}</strong><small>{{ $order->order_number }} &middot; {{ $order->serviceItem?->service_name_snapshot ?? $order->items->first()?->service?->name ?? 'Laundry' }}</small></span>
                            <span class="queue-order-price"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong><span class="badge badge-status {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0 py-4">Belum ada antrean order.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </aside>
</div>

<section class="card admin-capacity-card mt-4" aria-labelledby="status-title">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3"><div><h2 id="status-title" class="dashboard-section-title mb-1">Status operasional</h2><p class="small text-muted mb-0">Distribusi status order yang tersimpan saat ini.</p></div><a href="{{ route('admin.orders.index') }}" class="dashboard-text-link">Kelola order</a></div>
        <div class="row g-2">
            @foreach(\App\Enums\OrderStatus::cases() as $status)
                @php($count = $stats['status_counts'][$status->value] ?? 0)
                @if($count > 0)<div class="col-sm-6 col-lg-3"><div class="admin-status-tile"><span>{{ $status->label() }}</span><strong>{{ $count }}</strong></div></div>@endif
            @endforeach
        </div>
    </div>
</section>
@endsection
