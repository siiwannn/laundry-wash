@extends('layouts.app')

@section('title', 'Buat Pesanan Laundry - Laundry Wash')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1">Buat Pesanan Laundry</h3>
                <p class="text-muted mb-0">Isi formulir berikut dan kurir kami akan menjemput pakaian kotor Anda.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Batal
            </a>
        </div>

        <form action="{{ route('customer.orders.store') }}" method="POST" id="orderForm">
            @csrf

            <!-- Step 1: Select Service -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-1-circle-fill me-2"></i> Pilih Paket Layanan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($services as $index => $service)
                            <div class="col-md-6">
                                <label class="card h-100 p-3 border cursor-pointer service-card {{ $index === 0 ? 'border-primary bg-primary-subtle' : '' }}" style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-2">
                                        <input type="radio" name="service_id" value="{{ $service->id }}" class="form-check-input mt-1 service-radio" data-price="{{ (int) $service->price_per_kg }}" {{ $index === 0 ? 'checked' : '' }}>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $service->name }}</div>
                                            <small class="text-muted d-block mb-2">{{ $service->description }}</small>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">Rp {{ number_format($service->price_per_kg, 0, ',', '.') }}/kg</span>
                                                <small class="badge bg-light text-dark border">{{ $service->estimated_hours }} Jam</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Step 2: Delivery & Pickup Method -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-2-circle-fill me-2"></i> Metode Pengiriman</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="card p-3 border cursor-pointer method-card border-primary bg-primary-subtle" style="cursor: pointer;">
                                <div class="d-flex align-items-start gap-2">
                                    <input type="radio" name="service_type" value="pickup_and_delivery" class="form-check-input mt-1" id="type_pickup" checked>
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-bicycle me-1 text-primary"></i> Antar Jemput (Kurir)</div>
                                        <small class="text-muted">Kurir menjemput cucian dan mengantarkannya kembali saat bersih (+Rp 10.000).</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="card p-3 border cursor-pointer method-card" style="cursor: pointer;">
                                <div class="d-flex align-items-start gap-2">
                                    <input type="radio" name="service_type" value="self_drop_off" class="form-check-input mt-1" id="type_dropoff">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-box-arrow-in-down me-1 text-secondary"></i> Drop-off Mandiri</div>
                                        <small class="text-muted">Anda membawa dan mengambil cucian sendiri langsung ke outlet workshop (Gratis ongkir).</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Pickup Address Selection (Hidden if self drop-off) -->
                    <div id="addressSelectionSection">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="pickup_address_id" class="form-label fw-semibold small mb-0">Alamat Penjemputan:</label>
                            <a href="{{ route('customer.addresses.create') }}" class="btn btn-link btn-sm p-0 text-decoration-none" target="_blank">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Alamat Baru
                            </a>
                        </div>
                        @if($addresses->count() > 0)
                            <select name="pickup_address_id" id="pickup_address_id" class="form-select mb-3">
                                @foreach($addresses as $addr)
                                    <option value="{{ $addr->id }}" {{ $addr->is_default ? 'selected' : '' }}>
                                        [{{ $addr->label }}] {{ $addr->address }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-warning small py-2 mb-3">
                                Anda belum memiliki alamat tersimpan. Silakan <a href="{{ route('customer.addresses.create') }}" class="alert-link">tambahkan alamat penjemputan</a> terlebih dahulu.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 3: Estimated Weight & Notes -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-3-circle-fill me-2"></i> Perkiraan Berat & Catatan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="estimated_weight" class="form-label fw-semibold small">Perkiraan Berat Cucian (Kg):</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" max="200" name="estimated_weight" id="estimated_weight" class="form-control" value="3.0" required>
                                <span class="input-group-text">Kg</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">*Berat pasti akan ditimbang ulang oleh admin saat pakaian tiba di laundry.</small>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-center">
                                <span class="text-muted small">Estimasi Total Biaya Awal:</span>
                                <h4 class="fw-bold text-primary mb-0 mt-1" id="estimatedTotalText">Rp 34.000</h4>
                                <small class="text-muted" style="font-size: 0.72rem;" id="breakdownText">(3 kg x Rp 8.000 + Ongkir Rp 10.000)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="notes" class="form-label fw-semibold small">Instruksi Khusus / Catatan Tambahan (Opsional):</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Contoh: Pisahkan pakaian putih, gunakan pelembut pakaian lebih wangi, atau patokan rumah pagar hitam.">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-5">
                <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 shadow">
                    <i class="bi bi-check2-circle me-2"></i> Konfirmasi & Buat Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceRadios = document.querySelectorAll('.service-radio');
    const weightInput = document.getElementById('estimated_weight');
    const pickupRadio = document.getElementById('type_pickup');
    const dropoffRadio = document.getElementById('type_dropoff');
    const addressSection = document.getElementById('addressSelectionSection');
    const totalText = document.getElementById('estimatedTotalText');
    const breakdownText = document.getElementById('breakdownText');

    function calculateEstimate() {
        let pricePerKg = 8000;
        serviceRadios.forEach(radio => {
            if (radio.checked) {
                pricePerKg = parseFloat(radio.dataset.price);
                // Highlight card
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('border-primary', 'bg-primary-subtle'));
                radio.closest('.service-card').classList.add('border-primary', 'bg-primary-subtle');
            }
        });

        const isPickup = pickupRadio.checked;
        const deliveryFee = isPickup ? 10000 : 0;
        const weight = parseFloat(weightInput.value) || 0;

        const subtotal = weight * pricePerKg;
        const grandTotal = subtotal + deliveryFee;

        totalText.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        breakdownText.innerText = `(${weight} kg x Rp ${pricePerKg.toLocaleString('id-ID')} + Ongkir Rp ${deliveryFee.toLocaleString('id-ID')})`;

        if (isPickup) {
            addressSection.style.display = 'block';
        } else {
            addressSection.style.display = 'none';
        }
    }

    serviceRadios.forEach(r => r.addEventListener('change', calculateEstimate));
    weightInput.addEventListener('input', calculateEstimate);
    pickupRadio.addEventListener('change', calculateEstimate);
    dropoffRadio.addEventListener('change', calculateEstimate);

    calculateEstimate();
});
</script>
@endpush
@endsection
