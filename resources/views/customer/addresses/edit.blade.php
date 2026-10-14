@extends('layouts.app')

@section('title', 'Edit Alamat - Customer')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Ubah Alamat: {{ $address->label }}</h5>
            </div>
            <div class="card-body p-4">
                <form id="addressEditForm" action="{{ route('customer.addresses.update', $address) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="location_confirmed" id="location_confirmed" value="{{ old('location_confirmed', '0') }}">

                    <div class="mb-3">
                        <label for="label" class="form-label fw-semibold small">Label Alamat</label>
                        <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $address->label) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="recipient_name" class="form-label fw-semibold small">Nama Penerima</label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control" value="{{ old('recipient_name', $address->recipient_name ?: auth()->user()->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="recipient_phone" class="form-label fw-semibold small">Nomor Telepon Penerima</label>
                            <input type="tel" name="recipient_phone" id="recipient_phone" class="form-control" value="{{ old('recipient_phone', $address->recipient_phone ?: auth()->user()->phone) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold small">Alamat Lengkap</label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $address->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Leaflet Coordinates Picker -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold small mb-0">Pin Lokasi Peta (GPS Kurir):</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary" onclick="getCurrentLocation()">
                                <i class="bi bi-crosshair me-1"></i> Deteksi Lokasi Saya
                            </button>
                        </div>
                        <div id="addressPickerMap" style="height: 240px; border-radius: 8px; border: 1px solid #cbd5e1;" class="mb-2"></div>
                        <div id="addressPinWarning" class="alert alert-warning py-2 small d-none" role="status" aria-live="polite">
                            Alamat berubah. Klik peta, geser pin, atau deteksi lokasi untuk memastikan titik kurir sesuai.
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" name="latitude" id="latitude" class="form-control form-control-sm bg-light" value="{{ old('latitude', $address->latitude ?? '') }}" readonly placeholder="Latitude">
                            </div>
                            <div class="col-6">
                                <input type="text" name="longitude" id="longitude" class="form-control form-control-sm bg-light" value="{{ old('longitude', $address->longitude ?? '') }}" readonly placeholder="Longitude">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="is_default">
                            Jadikan sebagai alamat utama
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('customer.addresses.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addressInput = document.getElementById('address');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationConfirmedInput = document.getElementById('location_confirmed');
    const pinWarning = document.getElementById('addressPinWarning');
    const initialAddress = @json($address->address);
    const lat = Number.parseFloat(latitudeInput.value);
    const lng = Number.parseFloat(longitudeInput.value);
    const mapLat = Number.isFinite(lat) ? lat : -6.2088;
    const mapLng = Number.isFinite(lng) ? lng : 106.8456;
    let confirmedAddress = locationConfirmedInput.value === '1' ? addressInput.value.trim() : initialAddress.trim();

    const map = L.map('addressPickerMap').setView([mapLat, mapLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([mapLat, mapLng], { draggable: true }).addTo(map);

    function setCoords(posLat, posLng) {
        latitudeInput.value = posLat.toFixed(7);
        longitudeInput.value = posLng.toFixed(7);
        confirmedAddress = addressInput.value.trim();
        locationConfirmedInput.value = '1';
        pinWarning.classList.add('d-none');
    }

    addressInput.addEventListener('input', function () {
        const addressIsConfirmed = addressInput.value.trim() === confirmedAddress;
        locationConfirmedInput.value = addressIsConfirmed ? '1' : '0';
        pinWarning.classList.toggle('d-none', addressIsConfirmed);
    });

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        setCoords(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        setCoords(e.latlng.lat, e.latlng.lng);
    });

    window.getCurrentLocation = function () {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (pos) {
                const cLat = pos.coords.latitude;
                const cLng = pos.coords.longitude;
                marker.setLatLng([cLat, cLng]);
                map.setView([cLat, cLng], 16);
                setCoords(cLat, cLng);
            }, function (err) {
                alert('Gagal mendeteksi lokasi: ' + err.message);
            });
        }
    };
});
</script>
@endpush
@endsection
