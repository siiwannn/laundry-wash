@extends('layouts.app')

@section('title', 'Beranda Customer - Laundry Wash')

@section('content')
<!-- Hero Welcome Banner -->
<div class="card bg-primary text-white border-0 shadow-sm mb-4 overflow-hidden position-relative">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary mb-2 px-3 py-1 fw-bold">LAYANAN LAUNDRY ONLINE</span>
                <h2 class="fw-bold mb-2">Halo, {{ $customer->name }}!</h2>
                <p class="mb-4 text-white-50">Pakaian kotor menumpuk? Pesan kurir jemput sekarang, kami cuci bersih, wangi, dan setrika rapi sampai kembali ke tangan Anda.</p>
                <a href="{{ route('customer.orders.create') }}" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow">
                    <i class="bi bi-plus-circle-fill me-2"></i> Buat Pesanan Laundry Sekarang
                </a>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end">
                <i class="bi bi-droplet-half" style="font-size: 8rem; opacity: 0.15;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Active Orders Section -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> Pesanan Aktif Anda</h4>
        <a href="{{ route('customer.orders.history') }}" class="btn btn-link btn-sm text-decoration-none">Riwayat Pesanan &rarr;</a>
    </div>

    @if($activeOrders->count() > 0)
        <div class="row g-3">
            @foreach($activeOrders as $order)
                <div class="col-lg-6">
                    <div class="card h-100 border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $order->order_number }}</h5>
                                    <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }} WIB</small>
                                </div>
                                <span class="badge badge-status {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </div>

                            <div class="p-2 bg-light rounded my-3">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="text-muted d-block">Layanan:</span>
                                        <strong class="text-dark">{{ $order->items->first()->service->name ?? 'Laundry' }}</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted d-block">Total Tagihan:</span>
                                        <strong class="text-primary fs-6">Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-muted d-block">Alamat:</span>
                                        <span>{{ $order->pickupAddress->address ?? 'Drop-off langsung' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Courier Banner if Courier is moving -->
                            @if(in_array($order->status->value, ['courier_to_pickup', 'courier_to_customer']))
                                <div class="alert alert-info py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <i class="bi bi-broadcast text-danger me-1 animate-pulse"></i>
                                        <strong>Kurir sedang di jalan!</strong>
                                    </div>
                                    <a href="{{ route('customer.orders.tracking', $order) }}" class="btn btn-primary btn-sm py-1 px-3 fw-bold">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Live GPS
                                    </a>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <span class="badge badge-status {{ $order->payment_status->badgeClass() }}">
                                    {{ $order->payment_status->label() }}
                                </span>
                                <div class="d-flex gap-2">
                                    @if(in_array($order->status->value, ['courier_to_pickup', 'pickup_assigned', 'delivery_assigned', 'courier_to_customer']))
                                        <a href="{{ route('customer.orders.tracking', $order) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-map me-1"></i> Lacak Kurir
                                        </a>
                                    @endif
                                    <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                        Lihat Rincian &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card text-center py-5 border-dashed">
            <div class="card-body">
                <i class="bi bi-basket3 text-muted" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3 mb-1">Tidak ada pesanan aktif</h5>
                <p class="text-muted small mb-3">Pakaian kotor sudah siap? Buat pesanan baru dan kurir kami akan segera menjemput.</p>
                <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-plus-circle me-1"></i> Pesan Laundry
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Laundry Services Catalog Cards -->
<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="bi bi-tags me-2 text-primary"></i> Daftar Layanan Kami</h4>
    <div class="row g-3">
        @foreach($services as $svc)
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $svc->name }}</h6>
                    <p class="small text-muted mb-3 flex-grow-1">{{ $svc->description }}</p>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-bold text-primary">Rp {{ number_format($svc->price_per_kg, 0, ',', '.') }} <small class="text-muted">/kg</small></span>
                        <small class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i>{{ $svc->estimated_hours }} Jam</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
