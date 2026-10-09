@extends('layouts.app')

@section('title', 'Beranda Customer - Laundry Wash')

@section('content')
<section class="customer-welcome-banner" aria-labelledby="customer-welcome-title">
    <div class="customer-welcome-copy">
        <h1 id="customer-welcome-title">Hai, {{ \Illuminate\Support\Str::before(trim($customer->name), ' ') }}! <span class="customer-greeting-emoji" aria-hidden="true">&#128075;</span></h1>
        <p>Pantau cucian yang sedang diproses atau mulai pesanan baru.</p>
        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary customer-welcome-button">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Buat pesanan
        </a>
    </div>
    <section
        class="customer-weather"
        @if($weatherLocation && $weatherLocation->latitude !== null && $weatherLocation->longitude !== null)
            data-weather-url="{{ route('customer.weather') }}"
            data-weather-location="{{ $weatherLocation->label }}"
            data-weather-date="{{ now()->locale('id')->translatedFormat('d M Y') }}"
        @endif
        aria-label="{{ $weatherLocation && $weatherLocation->latitude !== null && $weatherLocation->longitude !== null ? 'Cuaca saat ini di alamat utama' : 'Suhu tidak tersedia karena titik lokasi alamat utama belum diatur' }}"
    >
        <span class="visually-hidden" id="customer-weather-status" role="status">Memuat cuaca</span>
        <span class="customer-weather-current">
            <i class="bi bi-cloud-fill customer-weather-icon" id="customer-weather-icon" aria-hidden="true"></i>
            <span class="customer-weather-temperature" id="customer-weather-temperature" aria-live="polite">--&#176;</span>
        </span>
        <span class="customer-weather-meta" id="customer-weather-meta">{{ now()->locale('id')->translatedFormat('d M Y') }}</span>
        <a class="customer-weather-attribution" href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a>
    </section>
</section>
<section class="dashboard-summary" aria-label="Ringkasan pelanggan">
    <div class="role-summary-grid">
        <x-dashboard-metric label="Pesanan aktif" :value="$activeOrders->count()" icon="basket2" tone="orange" note="Belum selesai" />
        <x-dashboard-metric label="Layanan tersedia" :value="$services->count()" icon="grid" tone="blue" note="Pilihan laundry" />
    </div>
</section>
<div class="customer-order-workspace">

<!-- Active Orders Section -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="dashboard-section-title mb-0">Pesanan saya</h2>
        <a href="{{ route('customer.orders.history') }}" class="btn btn-link btn-sm text-decoration-none">Riwayat Pesanan</a>
    </div>

    @if($activeOrders->count() > 0)
        <div class="row g-3">
            @foreach($activeOrders as $order)
                <div class="col-lg-6">
                    <div class="card h-100 border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h3 class="h5 fw-bold mb-0 text-dark">{{ $order->items->first()->service->name ?? 'Pesanan Laundry' }}</h3>
                                    <small class="text-muted">Dipesan {{ $order->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB</small>
                                </div>
                                <span class="badge badge-status {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </div>

                            <div class="p-2 bg-light rounded my-3">
                                <div class="row g-2 small">
                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Total pesanan</span>
                                        <strong class="text-primary fs-6">Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-muted d-block">Alamat penjemputan:</span>
                                        <span>{{ $order->pickupAddress->address ?? 'Alamat belum tersedia' }}</span>
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
                                        Lihat Rincian
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

@if($weatherLocation && $weatherLocation->latitude !== null && $weatherLocation->longitude !== null)
    @push('scripts')
        <script>
            (() => {
                const panel = document.querySelector('.customer-weather[data-weather-url]');
                if (!panel) return;

                const status = document.getElementById('customer-weather-status');
                const icon = document.getElementById('customer-weather-icon');
                const descriptions = new Map([
                    [0, ['Cerah', 'bi-sun-fill']], [1, ['Cerah berawan', 'bi-cloud-sun-fill']],
                    [2, ['Berawan sebagian', 'bi-cloud-sun-fill']], [3, ['Mendung', 'bi-cloud-fill']],
                    [45, ['Berkabut', 'bi-cloud-fog2-fill']], [48, ['Berkabut', 'bi-cloud-fog2-fill']],
                    [51, ['Gerimis', 'bi-cloud-drizzle-fill']], [53, ['Gerimis', 'bi-cloud-drizzle-fill']],
                    [55, ['Gerimis lebat', 'bi-cloud-drizzle-fill']], [56, ['Gerimis beku', 'bi-cloud-rain-fill']],
                    [57, ['Gerimis beku', 'bi-cloud-rain-fill']], [61, ['Hujan ringan', 'bi-cloud-rain-fill']],
                    [63, ['Hujan', 'bi-cloud-rain-fill']], [65, ['Hujan lebat', 'bi-cloud-rain-heavy-fill']],
                    [66, ['Hujan beku', 'bi-cloud-rain-fill']], [67, ['Hujan beku lebat', 'bi-cloud-rain-heavy-fill']],
                    [71, ['Salju ringan', 'bi-cloud-snow-fill']], [73, ['Salju', 'bi-cloud-snow-fill']],
                    [75, ['Salju lebat', 'bi-cloud-snow-fill']], [77, ['Butiran salju', 'bi-cloud-snow-fill']],
                    [80, ['Hujan lokal', 'bi-cloud-rain-fill']], [81, ['Hujan lokal', 'bi-cloud-rain-fill']],
                    [82, ['Hujan lokal lebat', 'bi-cloud-rain-heavy-fill']], [85, ['Hujan salju', 'bi-cloud-snow-fill']],
                    [86, ['Hujan salju lebat', 'bi-cloud-snow-fill']], [95, ['Badai petir', 'bi-cloud-lightning-rain-fill']],
                    [96, ['Badai petir dan hujan es', 'bi-cloud-lightning-rain-fill']],
                    [99, ['Badai petir dan hujan es', 'bi-cloud-lightning-rain-fill']],
                ]);

                fetch(panel.dataset.weatherUrl, { headers: { Accept: 'application/json' } })
                    .then(response => {
                        if (!response.ok) throw new Error('Cuaca belum dapat dimuat. Coba muat ulang halaman.');
                        return response.json();
                    })
                    .then(weather => {
                        const description = descriptions.get(Number(weather.weather_code)) || ['Kondisi cuaca berubah', 'bi-cloud-fill'];
                        const temperature = `${Math.round(weather.temperature)}\u00B0`;
                        document.getElementById('customer-weather-temperature').textContent = temperature;
                        document.getElementById('customer-weather-meta').textContent = `${description[0]} · ${panel.dataset.weatherDate}`;
                        icon.className = `bi ${description[1]} customer-weather-icon`;
                        panel.setAttribute('aria-label', `Cuaca ${description[0]}, ${temperature}, sekitar ${panel.dataset.weatherLocation}`);
                        status.textContent = `Cuaca diperbarui: ${description[0]}, ${temperature}`;
                    })
                    .catch(() => {
                        icon.className = 'bi bi-cloud-slash-fill customer-weather-icon';
                        panel.setAttribute('aria-label', 'Data cuaca tidak tersedia');
                        status.textContent = 'Data cuaca tidak tersedia';
                    })
                    .finally(() => { status.classList.add('visually-hidden'); });
            })();
        </script>
    @endpush
@endif

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
</div>
@endsection
