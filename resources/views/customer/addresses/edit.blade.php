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
                            <label class="form-label fw-semibold small mb-0">Lokasi Saya:</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary" onclick="getCurrentLocation()">
                                <i class="bi bi-crosshair me-1"></i> Deteksi Lokasi Saya
                            </button>
                        </div>
                        <div id="addressPickerMap"
                             data-forward-url="{{ route('customer.addresses.geocode', [], false) }}"
                             data-reverse-url="{{ route('customer.addresses.reverse-geocode', [], false) }}"
                             data-initial-address="{{ $address->address }}"
                             style="height: 240px; border-radius: 8px; border: 1px solid #cbd5e1;" class="mb-2"></div>
                        <div class="form-text mb-2">Pencarian alamat memakai Photon; posisi pin memakai Nominatim/OpenStreetMap. Alamat dan koordinat dikirim ke layanan publik tersebut.</div>
                        <div id="addressLookupStatus" class="alert alert-info py-2 small d-none" role="status" aria-live="polite"></div>
                        <div id="addressPinWarning" class="alert alert-warning py-2 small d-none" role="status" aria-live="polite">
                            Alamat atau pin berubah. Pastikan alamat lengkap sesuai dengan titik peta sebelum menyimpan.
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
<script src="{{ asset('js/customer-address-picker.js') }}?v={{ filemtime(public_path('js/customer-address-picker.js')) }}"></script>
@endpush
@endsection
