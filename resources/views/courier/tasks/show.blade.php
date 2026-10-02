@extends('layouts.app')

@section('title', 'Tugas Kurir - ' . $assignment->order->order_number)

@section('content')
<div class="row align-items-center mb-3">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Tugas: {{ $assignment->type->label() }}</h4>
            <span class="badge {{ $assignment->status->badgeClass() }} fs-6">
                {{ $assignment->status->label() }}
            </span>
        </div>
        <p class="text-muted small mb-0">No. Order: <strong>{{ $assignment->order->order_number }}</strong> &bull; Customer: {{ $assignment->order->customer->name }}</p>
    </div>
    <div class="col-md-5 text-md-end mt-2 mt-md-0">
        <a href="{{ route('courier.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<!-- Live Beacon Transmitter Banner (Active when on_the_way) -->
@if($assignment->status->value === 'on_the_way')
    <div id="gpsBeaconAlert" class="alert alert-info shadow-sm d-flex align-items-center justify-content-between mb-4" role="status" aria-live="polite">
        <div class="d-flex align-items-center gap-3">
            <span id="gpsBeaconSpinner" class="spinner-grow spinner-grow-sm text-info" aria-hidden="true"></span>
            <div>
                <strong id="gpsBeaconTitle"><i class="bi bi-broadcast me-1"></i> Mengaktifkan GPS...</strong>
                <div class="small" id="beaconStatusText">Izinkan akses lokasi agar posisi dapat dikirim setiap 10 detik.</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm d-none" id="retryGpsButton">
                <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
            </button>
            <span class="badge bg-secondary" id="beaconCounter">Terkirim: 0x</span>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Map Navigation View -->
    <div class="col-lg-7">
        <div class="card shadow-sm overflow-hidden mb-4">
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold text-muted"><i class="bi bi-map me-1 text-primary"></i> Peta Rute Navigasi</span>
                <span class="small text-muted">Buka di: <a href="https://www.google.com/maps/dir/?api=1&destination={{ $targetAddress->latitude ?? -6.2088 }},{{ $targetAddress->longitude ?? 106.8456 }}" target="_blank" class="fw-semibold text-primary">Google Maps &rarr;</a></span>
            </div>
            <div id="courierMap" style="height: 380px; width: 100%;"></div>
            <div class="card-footer bg-light py-2 px-3 small text-muted">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Titik Tujuan: <strong>{{ $targetAddress->address ?? 'Alamat Pelanggan' }}</strong>
            </div>
        </div>

    </div>

    <!-- Right Column: Action Buttons & Customer Contact -->
    <div class="col-lg-5">
        <!-- Action Buttons Card -->
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-check2-circle me-2"></i> Eksekusi Tugas Kurir</h6>
            </div>
            <div class="card-body p-4">
                @if($assignment->status->value === 'assigned')
                    <div class="mb-3 text-center">
                        <i class="bi bi-bicycle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold mt-2">Siap Meluncur?</h5>
                        <p class="small text-muted">Klik tombol di bawah ini saat Anda mulai berangkat menuju lokasi pelanggan. Pemancar GPS akan aktif secara otomatis.</p>
                    </div>
                    <form action="{{ route('courier.tasks.start', $assignment) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning w-100 py-3 fw-bold shadow">
                            <i class="bi bi-play-fill me-1 fs-5"></i> Mulai Perjalanan Sekarang
                        </button>
                    </form>
                @elseif($assignment->status->value === 'on_the_way')
                    <div class="mb-3">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Anda sedang dalam perjalanan. Setelah tiba di tujuan dan serah terima selesai, konfirmasikan di bawah.
                        </div>

                        <form action="{{ route('courier.tasks.complete', $assignment) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa proses {{ $assignment->type->label() }} telah selesai dilaksanakan?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold shadow">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                {{ $assignment->type->value === 'pickup' ? 'Konfirmasi Pakaian Berhasil Dijemput' : 'Konfirmasi Pakaian Berhasil Diantar' }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-success py-3 text-center mb-0">
                        <i class="bi bi-check2-all fs-2 d-block mb-1"></i>
                        <h6 class="fw-bold mb-0">Tugas Telah Selesai!</h6>
                    </div>
                @endif
            </div>
        </div>

        <!-- Customer Contact Details -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Kontak Pelanggan</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $assignment->order->customer->name }}</h5>
                <p class="small text-muted mb-3">{{ $assignment->order->customer->email }}</p>

                <div class="d-grid gap-2 mb-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $assignment->order->customer->phone) }}" target="_blank" class="btn btn-success btn-sm fw-bold">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp Customer ({{ $assignment->order->customer->phone }})
                    </a>
                    <a href="tel:{{ $assignment->order->customer->phone }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-telephone me-2"></i> Panggilan Telepon Langsung
                    </a>
                </div>

                <div class="p-3 bg-light rounded border">
                    <span class="small text-muted d-block fw-semibold mb-1">Alamat Tujuan:</span>
                    <p class="small text-dark mb-0">{{ $targetAddress->address ?? 'Alamat Pelanggan' }}</p>
                    @if($targetAddress && $targetAddress->latitude)
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-pin-map text-danger me-1"></i> Lat: {{ $targetAddress->latitude }}, Lng: {{ $targetAddress->longitude }}
                        </small>
                    @endif
                </div>

                @if($assignment->order->notes)
                    <div class="mt-3 p-2 bg-warning-subtle rounded border border-warning-subtle small">
                        <strong>Catatan Khusus dari Customer:</strong>
                        <div class="fst-italic text-dark mt-1">"{{ $assignment->order->notes }}"</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const assignmentId = {{ $assignment->id }};
    const isTripActive = {{ $assignment->status->value === 'on_the_way' ? 'true' : 'false' }};
    const targetLat = {{ $targetAddress->latitude ?? -6.2088 }};
    const targetLng = {{ $targetAddress->longitude ?? 106.8456 }};

    // Initialize Map
    const map = L.map('courierMap').setView([targetLat, targetLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const destIcon = L.divIcon({
        className: 'dest-marker',
        html: '<div style="background-color: #dc2626; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); font-size: 16px;"><i class="bi bi-house-door-fill"></i></div>',
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    const courierIcon = L.divIcon({
        className: 'courier-marker',
        html: '<div style="background-color: #0284c7; color: white; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); font-size: 18px;"><i class="bi bi-bicycle"></i></div>',
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    // Destination Marker
    const targetMarker = L.marker([targetLat, targetLng], { icon: destIcon }).addTo(map)
        .bindPopup('<strong>Tujuan:</strong> {{ addslashes($targetAddress->address ?? "Alamat") }}').openPopup();

    let courierMarker = null;
    let latestPosition = null;
    let watchId = null;
    let transmissionTimer = null;
    let isSending = false;
    let trackingStopped = false;
    let sendCount = 0;

    const alertEl = document.getElementById('gpsBeaconAlert');
    const titleEl = document.getElementById('gpsBeaconTitle');
    const statusEl = document.getElementById('beaconStatusText');
    const counterEl = document.getElementById('beaconCounter');
    const spinnerEl = document.getElementById('gpsBeaconSpinner');
    const retryButton = document.getElementById('retryGpsButton');

    function updateBeaconState(type, title, message, canRetry = false) {
        if (!alertEl) return;

        alertEl.className = `alert alert-${type} shadow-sm d-flex align-items-center justify-content-between mb-4`;
        titleEl.innerHTML = `<i class="bi bi-broadcast me-1"></i> ${title}`;
        statusEl.innerText = message;
        retryButton.classList.toggle('d-none', !canRetry);
        spinnerEl.classList.toggle('d-none', type !== 'info');
    }

    function stopTracking(message = null) {
        trackingStopped = true;

        if (watchId !== null && 'geolocation' in navigator) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }

        if (transmissionTimer !== null) {
            clearInterval(transmissionTimer);
            transmissionTimer = null;
        }

        if (message) {
            updateBeaconState('secondary', 'Pemancar GPS Dihentikan', message);
        }
    }

    async function transmitLocation() {
        if (trackingStopped || isSending || !latestPosition) return;

        if (!navigator.onLine) {
            updateBeaconState('warning', 'Koneksi Internet Terputus', 'Lokasi terakhir disimpan di browser dan akan dikirim saat koneksi kembali.');
            return;
        }

        isSending = true;
        const { latitude, longitude, accuracy } = latestPosition;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/courier/location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    assignment_id: assignmentId,
                    latitude,
                    longitude,
                    accuracy
                })
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                if (response.status === 422 && data.message) {
                    stopTracking(data.message);
                    return;
                }

                throw new Error(data.message || 'Server gagal menerima lokasi.');
            }

            sendCount++;
            counterEl.innerText = 'Terkirim: ' + sendCount + 'x';
            counterEl.className = 'badge bg-success';
            updateBeaconState(
                'success',
                'Pemancar GPS Live Aktif',
                `Koordinat terakhir dikirim pada ${new Date().toLocaleTimeString('id-ID')}.`
            );
        } catch (error) {
            updateBeaconState('warning', 'Lokasi Belum Terkirim', `${error.message} Periksa koneksi lalu coba lagi.`, true);
        } finally {
            isSending = false;
        }
    }

    function startTracking() {
        if (!isTripActive) return;

        trackingStopped = false;
        latestPosition = null;
        updateBeaconState('info', 'Mengaktifkan GPS...', 'Izinkan akses lokasi agar posisi dapat dikirim setiap 10 detik.');

        if (!('geolocation' in navigator)) {
            updateBeaconState('danger', 'GPS Tidak Didukung', 'Gunakan browser modern atau perangkat lain yang mendukung lokasi.', true);
            return;
        }

        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }

        watchId = navigator.geolocation.watchPosition(function (position) {
            latestPosition = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            };

            if (!courierMarker) {
                courierMarker = L.marker([latestPosition.latitude, latestPosition.longitude], { icon: courierIcon })
                    .addTo(map)
                    .bindPopup('<strong>Posisi Anda (Kurir)</strong>');
            } else {
                courierMarker.setLatLng([latestPosition.latitude, latestPosition.longitude]);
            }

            updateBeaconState('info', 'Lokasi GPS Ditemukan', 'Mengirim koordinat pertama ke server...');

            if (sendCount === 0) {
                transmitLocation();
            }
        }, function (error) {
            const messages = {
                1: 'Izin lokasi ditolak. Aktifkan izin lokasi pada pengaturan browser, lalu tekan Coba Lagi.',
                2: 'Posisi GPS tidak tersedia. Pastikan GPS perangkat aktif dan coba lagi.',
                3: 'Permintaan lokasi terlalu lama. Pindah ke area dengan sinyal GPS lebih baik lalu coba lagi.'
            };

            updateBeaconState('danger', 'GPS Tidak Aktif', messages[error.code] || 'Lokasi gagal dibaca. Silakan coba lagi.', true);
        }, {
            enableHighAccuracy: true,
            maximumAge: 10000,
            timeout: 15000
        });

        if (transmissionTimer === null) {
            transmissionTimer = setInterval(transmitLocation, 10000);
        }
    }

    if (retryButton) {
        retryButton.addEventListener('click', startTracking);
    }

    window.addEventListener('online', transmitLocation);
    window.addEventListener('beforeunload', () => stopTracking());

    startTracking();
});
</script>
@endpush
@endsection
