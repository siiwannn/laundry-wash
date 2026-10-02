@extends('layouts.auth')

@section('title', 'Daftar Akun Baru - Laundry Wash')

@section('content')
<div class="auth-header">
    <div class="brand-icon">
        <i class="bi bi-person-plus"></i>
    </div>
    <h4 class="fw-bold mb-1">Daftar Akun Pelanggan</h4>
    <p class="text-muted small mb-0">Nikmati kemudahan laundry antar jemput dengan tracking GPS live</p>
</div>

<div class="auth-body">
    <form action="{{ route('register.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold small">Nama Lengkap</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Siti Rahmawati">
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold small">Email Aktif</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold small">No. WhatsApp / HP</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08123456789">
            </div>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label fw-semibold small">Alamat Lengkap Penjemputan</label>
            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan">{{ old('address') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small d-flex justify-content-between">
                <span>Pin Titik Lokasi Peta (Opsional / Otomatis)</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-primary" onclick="getCurrentLocation()">
                    <i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saat Ini
                </button>
            </label>
            <div id="pickerMap" style="height: 180px; border-radius: 8px; border: 1px solid #cbd5e1;" class="mb-2"></div>
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm bg-light" id="latitude" name="latitude" value="{{ old('latitude', -6.2088) }}" readonly placeholder="Latitude">
                </div>
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm bg-light" id="longitude" name="longitude" value="{{ old('longitude', 106.8456) }}" readonly placeholder="Longitude">
                </div>
            </div>
            <small class="text-muted" style="font-size: 0.75rem;">Klik pada peta untuk menyesuaikan titik penjemputan kurir.</small>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold small">Password (Min. 6 karakter)</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label fw-semibold small">Konfirmasi Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-check2-circle me-1"></i> Selesaikan Pendaftaran
        </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
        <p class="small text-muted mb-0">Sudah memiliki akun?</p>
        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none small fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Akun Anda
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = parseFloat(document.getElementById('latitude').value) || -6.2088;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || 106.8456;

    const map = L.map('pickerMap').setView([defaultLat, defaultLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        updateCoords(position.lat, position.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    window.getCurrentLocation = function () {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
                updateCoords(lat, lng);
            }, function (error) {
                alert('Tidak dapat mendeteksi lokasi otomatis: ' + error.message);
            });
        } else {
            alert('Browser Anda tidak mendukung Geolocation.');
        }
    };
});
</script>
@endpush
@endsection
