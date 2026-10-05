@extends('layouts.app')

@section('title', 'Live Tracking Kurir - Pesanan ' . $order->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5.7.3/dist/maplibre-gl.css">
<style>
    .tracking-page { background: #E8EEF7; }
    .tracking-header { display: none; }
    .tracking-shell { min-height: calc(100vh - 76px); }
    .tracking-shell > .col-lg-8 { width: 100%; padding: 0; }
    .tracking-shell > .col-lg-4 { position: fixed; z-index: 5; left: 24px; bottom: 24px; width: min(430px, calc(100vw - 48px)); padding: 0; }
    .tracking-shell > .col-lg-4 .card { margin-bottom: 8px !important; box-shadow: 0 14px 34px rgba(15, 23, 42, .18) !important; border: 0; }
    .tracking-shell > .col-lg-4 .card:first-child { margin-bottom: 0 !important; }
    .tracking-shell > .col-lg-4 .card:first-child .card-body { padding: 14px 18px; }
    .tracking-shell > .col-lg-4 .card:first-child .card-header { display: none; }
    .tracking-shell > .col-lg-4 .card:last-child { display: none; }
    .tracking-shell > .col-lg-8 > .card { min-height: calc(100vh - 76px); border: 0; border-radius: 0; box-shadow: none !important; background: #DDE7F4; }
    .tracking-map { min-height: calc(100vh - 76px); }
    .tracking-shell > .col-lg-8 .card-header { display: none; }
    .tracking-shell > .col-lg-8 .card-footer { position: absolute; z-index: 4; top: 18px; left: 24px; right: 24px; padding: 0 !important; background: transparent !important; border: 0; }
    .tracking-shell > .col-lg-8 .card-footer > div { display: flex; justify-content: flex-start !important; }
    .tracking-shell > .col-lg-8 .card-footer > div > .d-flex { background: #fff; border-radius: 16px; padding: 10px; box-shadow: 0 10px 26px rgba(15, 23, 42, .16); }
    .tracking-shell > .col-lg-8 .card-footer > div > .small { display: none; }
    .tracking-metric { min-width: 120px; }
    .tracking-metric-value { font-variant-numeric: tabular-nums; }
    .driver-marker { width: 58px; height: 58px; transform-origin: center; will-change: transform; z-index: 10; }
    .driver-marker img, .destination-marker img { width: 100%; height: 100%; display: block; }
    .destination-marker { width: 44px; height: 44px; z-index: 8; }
    .map-overlay-controls { position: absolute; z-index: 2; right: 24px; bottom: 190px; }
    @media (max-width: 575.98px) {
        .tracking-shell { min-height: calc(100vh - 60px); }
        .tracking-shell > .col-lg-8 > .card, .tracking-map { min-height: calc(100vh - 60px); }
        .tracking-shell > .col-lg-4 { left: 12px; bottom: 12px; width: calc(100vw - 24px); }
        .tracking-shell > .col-lg-8 .card-footer { top: 12px; left: 12px; right: 12px; }
        .map-overlay-controls { right: 12px; bottom: 190px; }
    }
    @media (prefers-reduced-motion: reduce) { .animate-pulse, .spinner-grow { animation: none !important; } }
</style>
@endpush

@section('content')
<div class="row align-items-center mb-3 tracking-header">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <h4 class="fw-bold mb-0">Live Tracking Posisi Kurir</h4>
            <span class="badge bg-danger animate-pulse" id="liveBadge"><i class="bi bi-broadcast me-1" aria-hidden="true"></i> LIVE GPS</span>
        </div>
        <p class="text-muted small mb-0">Pesanan <strong>{{ $order->order_number }}</strong> diperbarui setiap 10 detik.</p>
    </div>
    <div class="col-md-5 text-md-end mt-2 mt-md-0">
        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Rincian Pesanan</a>
    </div>
</div>

<div class="row g-4 tracking-shell tracking-page">
    <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 px-3" id="trackingAlert" role="status" aria-live="polite">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="small text-muted">Status perjalanan</div>
                        <strong id="trackingStatusText">{{ $trackingData['journey_status'] ?? 'Menghubungkan ke GPS kurir...' }}</strong>
                    </div>
                    <div class="small text-muted" id="lastUpdatedText">Update: {{ $trackingData['courier_location']['recorded_at'] ?? '-' }}</div>
                </div>
            </div>

            <div class="position-relative">
                <div id="liveTrackingMap" class="tracking-map w-100" aria-label="Peta posisi kurir dan rute menuju tujuan"></div>
                <div class="map-overlay-controls d-flex flex-column gap-2">
                    <button type="button" class="btn btn-primary shadow-sm" id="followButton" aria-pressed="true" aria-label="Matikan kamera mengikuti kurir">
                        <i class="bi bi-crosshair me-1" aria-hidden="true"></i><span>Ikuti Kurir</span>
                    </button>
                    <button type="button" class="btn btn-light border shadow-sm d-none" id="retryTrackingButton">
                        <i class="bi bi-arrow-clockwise me-1" aria-hidden="true"></i>Coba Lagi
                    </button>
                </div>
            </div>

            <div class="card-footer bg-white p-3">
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="tracking-metric border rounded-3 px-3 py-2">
                            <div class="small text-muted">ETA</div>
                            <div class="h5 fw-bold mb-0 tracking-metric-value" id="etaValue">{{ isset($trackingData['route']['duration_seconds']) ? max(1, (int) ceil($trackingData['route']['duration_seconds'] / 60)) . ' menit' : '--' }}</div>
                        </div>
                        <div class="tracking-metric border rounded-3 px-3 py-2">
                            <div class="small text-muted">Jarak tersisa</div>
                            <div class="h5 fw-bold mb-0 tracking-metric-value" id="distanceValue">{{ isset($trackingData['route']['distance_meters']) ? number_format($trackingData['route']['distance_meters'] / 1000, 1, ',', '.') . ' km' : '--' }}</div>
                        </div>
                    </div>
                    <div class="small text-muted"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Update berikutnya: <strong id="countdownText">10s</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0"><i class="bi bi-bicycle me-2 text-warning" aria-hidden="true"></i>Kurir Bertugas</h6></div>
            <div class="card-body">
                <div class="small text-primary fw-semibold mb-2" id="trackingStatusTextBottom">{{ $trackingData['journey_status'] ?? 'Menghubungkan ke GPS kurir...' }}</div>
                <h6 class="fw-bold mb-1" id="courierName">{{ $trackingData['courier']['name'] ?? 'Kurir' }}</h6>
                <p class="text-muted small mb-3" id="courierVehicle">{{ $trackingData['courier']['vehicle_type'] ?? 'Motor' }} &bull; {{ $trackingData['courier']['vehicle_plate'] ?? '-' }}</p>
                <div class="d-grid gap-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trackingData['courier']['phone'] ?? '') }}" target="_blank" rel="noopener" class="btn btn-success btn-sm fw-semibold"><i class="bi bi-whatsapp me-1" aria-hidden="true"></i> Chat Kurir</a>
                    <a href="tel:{{ $trackingData['courier']['phone'] ?? '' }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-telephone me-1" aria-hidden="true"></i> Telepon Kurir</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill me-2 text-danger" aria-hidden="true"></i>Titik Tujuan</h6></div>
            <div class="card-body">
                <span class="badge bg-secondary mb-2" id="destLabel">{{ $trackingData['destination']['label'] ?? 'Alamat' }}</span>
                <p class="small text-dark mb-0" id="destAddress">{{ $trackingData['destination']['address'] ?? 'Menuju alamat pelanggan.' }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/maplibre-gl@5.7.3/dist/maplibre-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trackingUrl = @json(route('api.orders.tracking', $order));
    const mapStyleUrl = @json(config('services.tracking.map_style_url'));
    const initialData = @json($trackingData);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const defaultCoordinates = [
        initialData.courier_location?.longitude ?? initialData.destination?.longitude ?? 106.8456,
        initialData.courier_location?.latitude ?? initialData.destination?.latitude ?? -6.2088
    ];

    const map = new maplibregl.Map({
        container: 'liveTrackingMap',
        style: mapStyleUrl,
        center: defaultCoordinates,
        zoom: 17.5,
        pitch: 58,
        bearing: 0,
        attributionControl: true
    });
    map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');

    const driverElement = document.createElement('div');
    driverElement.className = 'driver-marker';
    driverElement.innerHTML = '<img src="{{ asset('images/tracking/motorcycle.svg') }}" alt="">';
    const driverMarker = new maplibregl.Marker({ element: driverElement, rotationAlignment: 'map' });

    const destinationElement = document.createElement('div');
    destinationElement.className = 'destination-marker';
    destinationElement.innerHTML = '<img src="{{ asset('images/tracking/destination.svg') }}" alt="">';
    const destinationMarker = new maplibregl.Marker({ element: destinationElement });

    let currentCoordinates = null;
    let currentBearing = initialData.courier_location?.heading ?? 0;
    let lastRecordedAt = null;
    let animationFrame = null;
    let autoFollow = true;
    let pollingStopped = false;
    let countdown = 10;
    const traveledCoordinates = [];

    const statusElement = document.getElementById('trackingStatusTextBottom') || document.getElementById('trackingStatusText');
    const updatedElement = document.getElementById('lastUpdatedText');
    const etaElement = document.getElementById('etaValue');
    const distanceElement = document.getElementById('distanceValue');
    const countdownElement = document.getElementById('countdownText');
    const retryButton = document.getElementById('retryTrackingButton');
    const followButton = document.getElementById('followButton');

    function setAutoFollow(enabled) {
        autoFollow = enabled;
        followButton.setAttribute('aria-pressed', String(enabled));
        followButton.setAttribute('aria-label', enabled ? 'Matikan kamera mengikuti kurir' : 'Aktifkan kamera mengikuti kurir');
        followButton.classList.toggle('btn-primary', enabled);
        followButton.classList.toggle('btn-light', !enabled);
        followButton.querySelector('span').textContent = enabled ? 'Ikuti Kurir' : 'Ikuti Lagi';
    }

    function updateRoute(route) {
        const emptyRoute = { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: [] } };
        const feature = route?.geometry
            ? { type: 'Feature', properties: {}, geometry: route.geometry }
            : emptyRoute;
        const source = map.getSource('active-route');
        if (source) source.setData(feature);

        etaElement.textContent = route ? `${Math.max(1, Math.ceil(route.duration_seconds / 60))} menit` : '--';
        distanceElement.textContent = route ? `${(route.distance_meters / 1000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} km` : '--';
    }

    function updateTraveledRoute(coordinates) {
        if (!coordinates || (traveledCoordinates.length && traveledCoordinates.at(-1)[0] === coordinates[0] && traveledCoordinates.at(-1)[1] === coordinates[1])) return;
        traveledCoordinates.push(coordinates);
        const source = map.getSource('traveled-route');
        if (source) source.setData({ type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: traveledCoordinates } });
    }

    function followCamera(coordinates, bearing, duration = 0) {
        if (!autoFollow) return;
        map.easeTo({
            center: coordinates,
            bearing,
            pitch: 58,
            zoom: 17.5,
            offset: [0, -(map.getContainer().clientHeight * .25)],
            duration,
        });
    }

    function shortestBearing(from, to) {
        return from + ((((to - from) % 360) + 540) % 360 - 180);
    }

    function bearingBetween(from, to) {
        const startLat = from[1] * Math.PI / 180;
        const endLat = to[1] * Math.PI / 180;
        const deltaLng = (to[0] - from[0]) * Math.PI / 180;
        const y = Math.sin(deltaLng) * Math.cos(endLat);
        const x = Math.cos(startLat) * Math.sin(endLat) - Math.sin(startLat) * Math.cos(endLat) * Math.cos(deltaLng);
        return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
    }

    function pointAlongPath(path, progress) {
        if (path.length < 2) return path[0];
        const lengths = [];
        let total = 0;
        for (let index = 1; index < path.length; index += 1) {
            const dx = path[index][0] - path[index - 1][0];
            const dy = path[index][1] - path[index - 1][1];
            total += Math.hypot(dx, dy);
            lengths.push(total);
        }
        const target = total * progress;
        const segmentIndex = lengths.findIndex(length => length >= target);
        const safeIndex = segmentIndex === -1 ? lengths.length - 1 : segmentIndex;
        const previousLength = safeIndex === 0 ? 0 : lengths[safeIndex - 1];
        const segmentLength = lengths[safeIndex] - previousLength || 1;
        const localProgress = (target - previousLength) / segmentLength;
        const start = path[safeIndex];
        const end = path[safeIndex + 1];
        return [
            start[0] + (end[0] - start[0]) * localProgress,
            start[1] + (end[1] - start[1]) * localProgress,
        ];
    }

    function animateDriver(nextCoordinates, nextBearing, movementGeometry) {
        const path = movementGeometry?.coordinates?.length > 1
            ? movementGeometry.coordinates
            : [currentCoordinates, nextCoordinates].filter(Boolean);
        if (!currentCoordinates || reducedMotion) {
            currentCoordinates = nextCoordinates;
            currentBearing = nextBearing ?? currentBearing;
            driverMarker.setLngLat(nextCoordinates).setRotation(currentBearing).addTo(map);
            followCamera(nextCoordinates, currentBearing, reducedMotion ? 0 : 700);
            return;
        }

        if (animationFrame !== null) cancelAnimationFrame(animationFrame);
        const startBearing = currentBearing;
        const startedAt = performance.now();
        const duration = 8500;

        const step = (timestamp) => {
            const progress = Math.min((timestamp - startedAt) / duration, 1);
            const eased = progress < .5 ? 4 * progress * progress * progress : 1 - Math.pow(-2 * progress + 2, 3) / 2;
            const coordinates = pointAlongPath(path, eased);
            const ahead = pointAlongPath(path, Math.min(eased + .015, 1));
            const routeBearing = movementGeometry?.coordinates?.length > 1
                ? bearingBetween(coordinates, ahead)
                : (nextBearing ?? bearingBetween(coordinates, ahead));
            const bearing = shortestBearing(startBearing, routeBearing);
            driverMarker.setLngLat(coordinates).setRotation(bearing).addTo(map);
            followCamera(coordinates, bearing);
            if (progress < 1) animationFrame = requestAnimationFrame(step);
            else {
                currentCoordinates = nextCoordinates;
                currentBearing = ((bearing % 360) + 360) % 360;
                animationFrame = null;
            }
        };
        animationFrame = requestAnimationFrame(step);
    }

    function applyTrackingData(data) {
        statusElement.textContent = data.journey_status ?? data.order_status_label ?? 'Perjalanan aktif';
        updateRoute(data.route);

        if (data.destination) destinationMarker.setLngLat([data.destination.longitude, data.destination.latitude]).addTo(map);
        if (!data.courier_location) {
            updatedElement.textContent = 'Menunggu koordinat pertama dari kurir';
            return;
        }

        updatedElement.textContent = `Update: ${data.courier_location.recorded_at}`;
        if (lastRecordedAt === data.courier_location.recorded_at_iso) return;
        lastRecordedAt = data.courier_location.recorded_at_iso;
        updateTraveledRoute([data.courier_location.longitude, data.courier_location.latitude]);
        animateDriver(
            [data.courier_location.longitude, data.courier_location.latitude],
            data.courier_location.heading,
            data.movement_route?.geometry
        );
    }

    function addTrackingLayers() {
        map.addSource('active-route', {
            type: 'geojson',
            data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: [] } }
        });
        map.addSource('traveled-route', {
            type: 'geojson',
            data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: [] } }
        });
        map.addLayer({
            id: 'active-route-casing',
            type: 'line',
            source: 'active-route',
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: { 'line-color': '#FFFFFF', 'line-width': 13, 'line-opacity': .95 }
        });
        map.addLayer({
            id: 'active-route-line',
            type: 'line',
            source: 'active-route',
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: { 'line-color': '#2563EB', 'line-width': 8, 'line-opacity': .95 }
        });
        map.addLayer({
            id: 'traveled-route-line',
            type: 'line',
            source: 'traveled-route',
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: { 'line-color': '#64748B', 'line-width': 8, 'line-opacity': .92 }
        });
    }

    map.on('load', () => {
        addTrackingLayers();
        applyTrackingData(initialData);
    });

    ['dragstart', 'zoomstart', 'rotatestart', 'pitchstart'].forEach(eventName => {
        map.on(eventName, event => { if (event.originalEvent) setAutoFollow(false); });
    });
    followButton.addEventListener('click', () => {
        setAutoFollow(!autoFollow);
        if (autoFollow && currentCoordinates) followCamera(currentCoordinates, currentBearing, reducedMotion ? 0 : 600);
    });

    map.on('zoom', () => {
        const scale = Math.max(.78, Math.min(1.22, .78 + (map.getZoom() - 12) * .08));
        driverElement.querySelector('img').style.transform = `scale(${scale})`;
    });

    async function fetchTrackingData() {
        if (pollingStopped) return;
        retryButton.classList.add('d-none');
        try {
            const response = await fetch(trackingUrl, { headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Server gagal memuat posisi kurir.');
            if (!result.data.is_active) {
                pollingStopped = true;
                statusElement.textContent = result.data.journey_status ?? result.data.message;
                countdownElement.textContent = 'Selesai';
                document.getElementById('liveBadge').classList.replace('bg-danger', 'bg-secondary');
                return;
            }
            applyTrackingData(result.data);
        } catch (error) {
            statusElement.textContent = `${error.message} Periksa koneksi lalu coba lagi.`;
            retryButton.classList.remove('d-none');
        }
    }

    retryButton.addEventListener('click', fetchTrackingData);
    window.addEventListener('online', fetchTrackingData);
    window.addEventListener('beforeunload', () => {
        pollingStopped = true;
        if (animationFrame !== null) cancelAnimationFrame(animationFrame);
    });

    setInterval(() => {
        if (pollingStopped) return;
        countdown -= 1;
        if (countdown <= 0) {
            countdown = 10;
            fetchTrackingData();
        }
        countdownElement.textContent = `${countdown}s`;
    }, 1000);
    fetchTrackingData();
});
</script>
@endpush
@endsection
