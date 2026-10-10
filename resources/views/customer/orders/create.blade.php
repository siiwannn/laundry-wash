@extends('layouts.app')

@section('title', 'Buat Pesanan Laundry - Laundry Wash')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
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
                                        <input type="radio" name="service_id" value="{{ $service->id }}" class="form-check-input mt-1 service-radio" data-price="{{ (int) $service->price_per_unit }}" data-unit="{{ $service->unit }}" {{ old('service_id', $index === 0 ? $service->id : null) == $service->id ? 'checked' : '' }}>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $service->name }}</div>
                                            <small class="text-muted d-block mb-2">{{ $service->description }}</small>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">Rp {{ number_format($service->price_per_unit, 0, ',', '.') }}/{{ $service->unit }}</span>
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

            <!-- Step 2: Pickup Schedule -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-2-circle-fill me-2"></i> Jadwal Pickup Courier</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small"><i class="bi bi-bicycle me-1"></i> Laundry akan dijemput dan diantar kembali oleh courier Laundry Wash.</div>
                    <div>
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
                        <div class="row g-3 mt-1">
                            <div class="col-md-6"><label for="pickup_date" class="form-label fw-semibold small">Tanggal Pickup</label><input id="pickup_date" name="pickup_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('pickup_date', now()->addDay()->toDateString()) }}" class="form-control"></div>
                            <div class="col-md-6"><label for="pickup_time" class="form-label fw-semibold small">Jam Pickup</label><input id="pickup_time" name="pickup_time" type="time" value="{{ old('pickup_time', '09:00') }}" class="form-control"></div>
                        </div>
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
                            <label for="estimated_quantity" class="form-label fw-semibold small">Perkiraan berat cucian (kg):</label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0.5" max="500" name="estimated_quantity" id="estimated_quantity" class="form-control" value="{{ old('estimated_quantity', '3.0') }}" required>
                                <span class="input-group-text" id="quantityUnit">kg</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;" id="quantityHelp">Berat akhir akan ditimbang oleh admin saat cucian tiba di laundry.</small>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-center">
                                <span class="text-muted small">Estimasi Total Biaya Awal:</span>
                                <h4 class="fw-bold text-primary mb-0 mt-1" id="estimatedTotalText">Menghitung…</h4>
                                <small class="text-muted" style="font-size: 0.72rem;" id="breakdownText"></small>
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
    const quantityInput = document.getElementById('estimated_quantity');
    const quantityUnit = document.getElementById('quantityUnit');
    const quantityLabel = document.querySelector('label[for="estimated_quantity"]');
    const quantityHelp = document.getElementById('quantityHelp');
    let previousUnit = null;
    const totalText = document.getElementById('estimatedTotalText');
    const breakdownText = document.getElementById('breakdownText');

    function calculateEstimate() {
        let pricePerUnit = 0;
        let unit = 'kg';
        serviceRadios.forEach(radio => {
            if (radio.checked) {
                pricePerUnit = parseFloat(radio.dataset.price);
                unit = radio.dataset.unit;
                // Highlight card
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('border-primary', 'bg-primary-subtle'));
                radio.closest('.service-card').classList.add('border-primary', 'bg-primary-subtle');
            }
        });

        quantityUnit.textContent = unit;
        quantityInput.step = unit === 'pcs' ? '1' : '0.1';
        quantityInput.min = unit === 'pcs' ? '1' : '0.5';
        if (previousUnit !== unit) {
            const currentQuantity = parseFloat(quantityInput.value) || 1;
            quantityInput.value = unit === 'pcs'
                ? String(Math.max(1, Math.round(currentQuantity)))
                : String(Math.max(0.5, currentQuantity));
            previousUnit = unit;
        }
        quantityLabel.textContent = unit === 'pcs' ? 'Perkiraan jumlah barang:' : 'Perkiraan berat cucian (kg):';
        quantityHelp.textContent = unit === 'pcs'
            ? 'Jumlah akhir akan dihitung admin saat cucian tiba di laundry.'
            : 'Berat akhir akan ditimbang oleh admin saat cucian tiba di laundry.';
        const quantity = parseFloat(quantityInput.value) || 0;
        const shippingFee = {{ $shippingFee }};

        const subtotal = Math.round(quantity * pricePerUnit);
        const grandTotal = subtotal + shippingFee;

        totalText.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        breakdownText.innerText = `(${quantity} ${unit} x Rp ${pricePerUnit.toLocaleString('id-ID')} + ongkir Rp ${shippingFee.toLocaleString('id-ID')})`;

    }

    serviceRadios.forEach(r => r.addEventListener('change', calculateEstimate));
    quantityInput.addEventListener('input', calculateEstimate);

    calculateEstimate();
});
</script>
@endpush
@endsection
