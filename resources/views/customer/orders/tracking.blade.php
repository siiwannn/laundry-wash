@extends('layouts.app')

@section('title', 'Live Tracking Kurir - Pesanan ' . $order->order_number)

@section('content')
<div class="row align-items-center mb-3">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Live Tracking Posisi Kurir</h4>
            <span class="badge bg-danger animate-pulse">
                <i class="bi bi-broadcast me-1"></i> LIVE GPS
            </span>
        </div>
        <p class="text-muted small mb-0">Pesanan <strong>{{ $order->order_number }}</strong> &bull; Memantau pergerakan kurir secara real-time.</p>
    </div>
    <div class="col-md-5 text-md-end mt-2 mt-md-0">
        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Rincian Pesanan
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Map Container -->
    <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" id="trackingAlert" role="status" aria-live="polite">
                <div class="small fw-semibold text-muted d-flex align-items-center gap-2">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                    <span>Status: <strong class="text-dark" id="trackingStatusText">Menghubungkan ke GPS kurir...</strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm d-none" id="retryTrackingButton">
                        <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                    </button>
                    <div class="small text-muted" id="lastUpdatedText">Update: -</div>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="liveTrackingMap" style="height: 480px; width: 100%; position: relative;"></div>

            <div class="card-footer bg-light py-2 px-3 small text-muted d-flex justify-content-between">
                <span><i class="bi bi-info-circle me-1"></i> Peta diperbarui otomatis setiap 10 detik via AJAX.</span>
                <span class="fw-semibold text-primary" id="countdownText">10s</span>
            </div>
        </div>
    </div>

    <!-- Courier Info & Destination Card -->
    <div class="col-lg-4">
        <!-- Courier Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bicycle me-2 text-warning"></i> Informasi Kurir Bertugas</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-warning-subtle text-dark p-3 fw-bold fs-4">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" id="courierName">{{ $trackingData['courier']['name'] ?? 'Kurir' }}</h6>
                        <span class="text-muted small" id="courierVehicle">
                            {{ $trackingData['courier']['vehicle_type'] ?? 'Motor' }} &bull; {{ $trackingData['courier']['vehicle_plate'] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trackingData['courier']['phone'] ?? '') }}" target="_blank" class="btn btn-success btn-sm fw-semibold" id="waBtn">
                        <i class="bi bi-whatsapp me-1"></i> Chat Kurir via WhatsApp
                    </a>
                    <a href="tel:{{ $trackingData['courier']['phone'] ?? '' }}" class="btn btn-outline-secondary btn-sm" id="callBtn">
                        <i class="bi bi-telephone me-1"></i> Telepon Kurir
                    </a>
                </div>

                <div class="p-2 bg-light rounded small border">
                    <div class="text-muted">Aktivitas Kurir:</div>
                    <strong class="text-primary" id="taskTypeLabel">{{ $trackingData['type_label'] ?? 'Perjalanan' }}</strong>
                </div>
            </div>
        </div>

        <!-- Destination Address Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill me-2 text-danger"></i> Titik Tujuan</h6>
            </div>
            <div class="card-body">
                <span class="badge bg-secondary mb-2" id="destLabel">{{ $trackingData['destination']['label'] ?? 'Alamat' }}</span>
                <p class="small text-dark mb-2" id="destAddress">{{ $trackingData['destination']['address'] ?? 'Menuju alamat pelanggan.' }}</p>
                <div class="small text-muted" id="destCoords">
                    @if(isset($trackingData['destination']['latitude']))
                        Koordinat: {{ $trackingData['destination']['latitude'] }}, {{ $trackingData['destination']['longitude'] }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trackingUrl = @json(route('api.orders.tracking', $order));
    const defaultLat = {{ $trackingData['courier_location']['latitude'] ?? $trackingData['destination']['latitude'] ?? -6.2088 }};
    const defaultLng = {{ $trackingData['courier_location']['longitude'] ?? $trackingData['destination']['longitude'] ?? 106.8456 }};

    // Initialize Leaflet Map
    const map = L.map('liveTrackingMap').setView([defaultLat, defaultLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Icons using HTML divIcon
    const courierIcon = L.divIcon({
        className: 'custom-courier-marker',
        html: '<div style="background-color: #0284c7; color: white; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); font-size: 18px;"><i class="bi bi-bicycle"></i></div>',
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const homeIcon = L.divIcon({
        className: 'custom-home-marker',
        html: '<div style="background-color: #dc2626; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); font-size: 16px;"><i class="bi bi-house-door-fill"></i></div>',
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    let courierMarker = null;
    let homeMarker = null;
    let polyline = null;

    // Plot initial destination marker
    @if(isset($trackingData['destination']['latitude']))
        const destLat = {{ $trackingData['destination']['latitude'] }};
        const destLng = {{ $trackingData['destination']['longitude'] }};
        homeMarker = L.marker([destLat, destLng], { icon: homeIcon })
            .addTo(map)
            .bindPopup('<strong>Tujuan:</strong> {{ addslashes($trackingData['destination']['address'] ?? "Rumah") }}');
    @endif

    // Plot initial courier marker if exists
    @if(isset($trackingData['courier_location']['latitude']))
        const initCourierLat = {{ $trackingData['courier_location']['latitude'] }};
        const initCourierLng = {{ $trackingData['courier_location']['longitude'] }};
        courierMarker = L.marker([initCourierLat, initCourierLng], { icon: courierIcon })
            .addTo(map)
            .bindPopup('<strong>Posisi Kurir:</strong> {{ addslashes($trackingData['courier']['name'] ?? "Kurir") }}')
            .openPopup();
    @endif

    // Polling Logic: fetch every 10 seconds while a courier trip is active.
    let countdown = 10;
    let countdownTimer = null;
    let pollingStopped = false;
    const countdownEl = document.getElementById('countdownText');
    const statusTextEl = document.getElementById('trackingStatusText');
    const lastUpdatedEl = document.getElementById('lastUpdatedText');
    const retryButton = document.getElementById('retryTrackingButton');

    function startCountdown() {
        if (countdownTimer !== null) return;

        countdownTimer = setInterval(() => {
            if (pollingStopped) return;

            countdown--;
            if (countdown <= 0) {
                countdown = 10;
                fetchTrackingData();
            }
            countdownEl.innerText = countdown + 's';
        }, 1000);
    }

    function stopPolling(message) {
        pollingStopped = true;
        if (countdownTimer !== null) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        countdownEl.innerText = 'Selesai';
        statusTextEl.innerText = message;
    }

    async function fetchTrackingData() {
        if (pollingStopped) return;

        retryButton.classList.add('d-none');

        try {
            const response = await fetch(trackingUrl, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Server gagal memuat posisi kurir.');
            }

            const data = result.data;

            if (!data.is_active) {
                stopPolling(data.message || 'Perjalanan kurir sudah selesai.');
                return;
            }

            statusTextEl.innerText = 'Kurir sedang dalam perjalanan (' + data.order_status_label + ')';

            if (!data.courier_location) {
                lastUpdatedEl.innerText = 'Menunggu koordinat pertama dari kurir';
                return;
            }

            const cLat = data.courier_location.latitude;
            const cLng = data.courier_location.longitude;

            lastUpdatedEl.innerText = 'Update: ' + data.courier_location.recorded_at;

            if (!courierMarker) {
                courierMarker = L.marker([cLat, cLng], { icon: courierIcon }).addTo(map);
            } else {
                courierMarker.setLatLng([cLat, cLng]);
            }

            if (homeMarker) {
                const hLatLng = homeMarker.getLatLng();
                if (polyline) map.removeLayer(polyline);
                polyline = L.polyline([[cLat, cLng], [hLatLng.lat, hLatLng.lng]], {
                    color: '#0284c7',
                    dashArray: '6, 8',
                    weight: 3
                }).addTo(map);

                const group = new L.featureGroup([courierMarker, homeMarker]);
                map.fitBounds(group.getBounds().pad(0.2));
            } else {
                map.panTo([cLat, cLng]);
            }
        } catch (error) {
            statusTextEl.innerText = `${error.message} Periksa koneksi lalu tekan Coba Lagi.`;
            retryButton.classList.remove('d-none');
        }
    }

    retryButton.addEventListener('click', () => {
        pollingStopped = false;
        countdown = 10;
        startCountdown();
        fetchTrackingData();
    });

    window.addEventListener('online', fetchTrackingData);
    window.addEventListener('beforeunload', () => {
        pollingStopped = true;
        if (countdownTimer !== null) clearInterval(countdownTimer);
    });

    startCountdown();
    fetchTrackingData();
});
</script>
@endpush
@endsection
