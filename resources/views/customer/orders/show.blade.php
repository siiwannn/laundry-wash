@extends('layouts.app')

@section('title', 'Pesanan ' . $order->order_number . ' - Laundry Wash')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold mb-0">{{ $order->order_number }}</h3>
            <span class="badge badge-status {{ $order->status->badgeClass() }} fs-6">
                {{ $order->status->label() }}
            </span>
            <span class="badge badge-status {{ $order->payment_status->badgeClass() }} fs-6">
                {{ $order->payment_status->label() }}
            </span>
        </div>
        <p class="text-muted mb-0">Dipesan pada {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
        @if(in_array($order->status->value, ['courier_to_pickup', 'pickup_assigned', 'delivery_assigned', 'courier_to_customer']))
            <a href="{{ route('customer.orders.tracking', $order) }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                <i class="bi bi-geo-alt-fill me-1"></i> Live Tracking Kurir
            </a>
        @endif
        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Visual Lifecycle Stepper -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-diagram-3 me-2 text-primary"></i> Status Pengerjaan Laundry:</h6>
        <div class="progress mb-3" style="height: 8px;">
            @php
                $step = $order->status->stepIndex();
                $percent = $step > 0 ? min(100, ($step / 11) * 100) : 0;
            @endphp
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted text-center flex-wrap gap-1">
            <span class="{{ $step >= 1 ? 'fw-bold text-primary' : '' }}">1. Dipesan</span>
            <span class="{{ $step >= 2 ? 'fw-bold text-primary' : '' }}">2. Terkonfirmasi</span>
            <span class="{{ $step >= 3 ? 'fw-bold text-primary' : '' }}">3. Pickup</span>
            <span class="{{ $step >= 4 ? 'fw-bold text-primary' : '' }}">4. Tiba di Laundry</span>
            <span class="{{ $step >= 5 ? 'fw-bold text-primary' : '' }}">5. Dicuci</span>
            <span class="{{ $step >= 6 ? 'fw-bold text-primary' : '' }}">6. Dikeringkan</span>
            <span class="{{ $step >= 7 ? 'fw-bold text-primary' : '' }}">7. Disetrika</span>
            <span class="{{ $step >= 8 ? 'fw-bold text-success' : '' }}">8. Siap (Ready)</span>
            <span class="{{ $step >= 9 ? 'fw-bold text-primary' : '' }}">9. Delivery</span>
            <span class="{{ $step >= 11 ? 'fw-bold text-success' : '' }}">10. Selesai</span>
        </div>
    </div>
</div>

<!-- Payment Alert Banner if Order Ready and Payment Pending -->
@if($order->canAcceptPayment())
    <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-wallet2 fs-2 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-1">Cucian Anda Telah Selesai & Siap!</h5>
                <div class="small">Silakan lakukan pembayaran sebesar <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> agar kurir dapat mengantarkan cucian ke rumah Anda.</div>
            </div>
        </div>
        <button type="button" class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
        </button>
    </div>
@endif

<div class="row g-4">
    <!-- Invoice and Order Details -->
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Rincian Tagihan Laundry</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th>Paket Layanan</th>
                            <th class="text-center">Tarif/Kg</th>
                            <th class="text-center">Berat Cucian</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->service->name ?? 'Layanan' }}</div>
                                    <small class="text-muted">{{ $item->service->description }}</small>
                                </td>
                                <td class="text-center">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-center fw-semibold">
                                    {{ $order->actual_weight ?: $item->quantity }} kg
                                    @if($order->actual_weight)
                                        <small class="text-success d-block" style="font-size: 0.72rem;">(Berat Real Ditimbang)</small>
                                    @else
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">(Perkiraan Awal)</small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end text-muted">Subtotal Cucian:</td>
                            <td class="text-end fw-bold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted">Ongkos Antar Jemput:</td>
                            <td class="text-end fw-bold">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->additional_fee > 0)
                            <tr>
                                <td colspan="3" class="text-end text-muted">Biaya Tambahan:</td>
                                <td class="text-end fw-bold">Rp {{ number_format($order->additional_fee, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="table-primary border-top border-primary">
                            <td colspan="3" class="text-end fw-bold fs-6">TOTAL PEMBAYARAN:</td>
                            <td class="text-end fw-bold fs-5 text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($order->notes)
                <div class="card-footer bg-light py-2 px-3">
                    <small class="text-muted fw-semibold">Catatan Khusus Anda:</small>
                    <p class="mb-0 small text-dark fst-italic">"{{ $order->notes }}"</p>
                </div>
            @endif
        </div>

        <!-- Payment Records -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-success"></i> Status Pembayaran</h6>
                <span class="badge badge-status {{ $order->payment_status->badgeClass() }}">
                    {{ $order->payment_status->label() }}
                </span>
            </div>
            <div class="card-body">
                @if($order->payments->count() > 0)
                    @foreach($order->payments as $payment)
                        <div class="p-3 border rounded mb-2 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $payment->method->label() }} &bull; Rp {{ number_format($payment->amount, 0, ',', '.') }}</h6>
                                <small class="text-muted">Diajukan: {{ $payment->created_at->format('d/m/Y H:i') }}</small>
                                @if($payment->proof_file)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank" class="small text-info text-decoration-none">
                                            <i class="bi bi-image me-1"></i> Bukti Transfer Terlampir
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <span class="badge {{ $payment->status->badgeClass() }} py-2 px-3">
                                {{ $payment->status->label() }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted small mb-0">Pembayaran dilakukan setelah proses laundry selesai (Status Siap/Ready).</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Timeline & Address Card -->
    <div class="col-lg-5">
        <!-- Address & Courier Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill me-2 text-danger"></i> Alamat Penjemputan / Pengantaran</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="small text-muted d-block">Metode Layanan:</span>
                    <strong class="text-dark">
                        {{ $order->isPickupAndDelivery() ? 'Antar Jemput oleh Kurir' : 'Antar & Ambil Sendiri ke Workshop' }}
                    </strong>
                </div>
                @if($order->pickupAddress)
                    <div class="mb-3">
                        <span class="small text-muted d-block">Alamat Lengkap:</span>
                        <p class="small fw-semibold mb-1">{{ $order->pickupAddress->address }}</p>
                        @if($order->pickupAddress->latitude)
                            <small class="text-muted">
                                <i class="bi bi-pin-map-fill text-danger me-1"></i> Titik Koordinat Tersimpan (GPS)
                            </small>
                        @endif
                    </div>
                @endif

                @if($order->activeAssignment)
                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning text-dark"><i class="bi bi-bicycle me-1"></i> Kurir Bertugas</span>
                            <span class="badge {{ $order->activeAssignment->status->badgeClass() }}">{{ $order->activeAssignment->status->label() }}</span>
                        </div>
                        <h6 class="fw-bold mb-1">{{ $order->activeAssignment->courier->name }}</h6>
                        <small class="text-muted d-block mb-2">{{ $order->activeAssignment->courier->courierProfile->vehicle_type ?? 'Motor' }} ({{ $order->activeAssignment->courier->courierProfile->vehicle_plate ?? '-' }})</small>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->activeAssignment->courier->phone) }}" target="_blank" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="bi bi-whatsapp me-1"></i> Hubungi Kurir via WhatsApp
                        </a>
                        <a href="{{ route('customer.orders.tracking', $order) }}" class="btn btn-primary btn-sm w-100 fw-bold">
                            <i class="bi bi-map-fill me-1"></i> Buka Live Map Tracking
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline Audit -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Status Pesanan</h6>
            </div>
            <div class="card-body">
                <div class="timeline position-relative ps-4" style="border-left: 2px solid #e2e8f0; margin-left: 10px;">
                    @foreach($order->statusHistories as $hist)
                        <div class="mb-3 position-relative">
                            <div class="position-absolute" style="left: -29px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: #0d6efd; border: 2px solid #ffffff;"></div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="badge {{ $hist->status->badgeClass() }}">{{ $hist->status->label() }}</span>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $hist->created_at->format('d/m H:i') }}</small>
                            </div>
                            <p class="small text-dark mt-1 mb-0">{{ $hist->note }}</p>
                        </div>
                    @endforeach
                </div>

                @if($order->canBeCancelled())
                    <div class="pt-3 border-top mt-3 text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan Ini
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Payment Modal -->
@if($order->canAcceptPayment())
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('customer.orders.pay', $order) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 text-success me-2"></i> Pembayaran Laundry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded text-center mb-3">
                    <span class="text-muted small">Total yang harus dibayar:</span>
                    <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($order->total, 0, ',', '.') }}</h3>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Pilih Metode Pembayaran:</label>
                    <div class="d-grid gap-2">
                        <label class="p-3 border rounded cursor-pointer d-flex align-items-center gap-3">
                            <input type="radio" name="method" value="transfer" class="form-check-input mt-0" checked onchange="togglePaymentInstructions('transfer')">
                            <div>
                                <strong class="d-block text-dark"><i class="bi bi-bank me-1 text-primary"></i> Transfer Bank Manual</strong>
                                <small class="text-muted">BCA / Mandiri / BRI</small>
                            </div>
                        </label>
                        <label class="p-3 border rounded cursor-pointer d-flex align-items-center gap-3">
                            <input type="radio" name="method" value="qris" class="form-check-input mt-0" onchange="togglePaymentInstructions('qris')">
                            <div>
                                <strong class="d-block text-dark"><i class="bi bi-qr-code-scan me-1 text-danger"></i> QRIS (Semua E-Wallet)</strong>
                                <small class="text-muted">GoPay, OVO, Dana, ShopeePay, BCA Mobile</small>
                            </div>
                        </label>
                        <label class="p-3 border rounded cursor-pointer d-flex align-items-center gap-3">
                            <input type="radio" name="method" value="cash" class="form-check-input mt-0" onchange="togglePaymentInstructions('cash')">
                            <div>
                                <strong class="d-block text-dark"><i class="bi bi-cash-stack me-1 text-success"></i> Tunai (Cash on Delivery)</strong>
                                <small class="text-muted">Bayar langsung ke kurir saat pakaian diantar</small>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Transfer Instructions -->
                <div id="transferInstructions" class="p-3 bg-light rounded border mb-3">
                    <h6 class="fw-bold mb-2 small text-uppercase">Nomor Rekening Resmi:</h6>
                    <div class="small mb-1"><strong>Bank BCA:</strong> 8890 1234 5678 (a.n Laundry Wash)</div>
                    <div class="small"><strong>Bank Mandiri:</strong> 1320 0098 7654 (a.n Laundry Wash)</div>
                </div>

                <!-- QRIS Instructions -->
                <div id="qrisInstructions" class="p-3 bg-light rounded border mb-3 text-center" style="display: none;">
                    <h6 class="fw-bold mb-2 small text-uppercase">Scan QRIS Laundry Wash:</h6>
                    <div class="p-2 bg-white d-inline-block rounded border mb-2">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=LAUNDRY-WASH-ORDER-{{ $order->order_number }}-AMOUNT-{{ (int) $order->total }}" alt="QRIS Code" class="img-fluid" style="width: 160px; height: 160px;">
                    </div>
                    <div class="small text-muted">Scan menggunakan aplikasi mobile banking atau e-wallet Anda.</div>
                </div>

                <!-- Proof Upload (for Transfer & QRIS) -->
                <div id="proofUploadSection" class="mb-3">
                    <label for="proof_file" class="form-label fw-semibold small">Unggah Bukti Transfer / Resi:</label>
                    <input type="file" name="proof_file" id="proof_file" class="form-control form-control-sm" accept="image/*">
                    <small class="text-muted" style="font-size: 0.72rem;">Format: JPG, PNG (Maksimal 4 MB)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-success fw-bold px-4">Kirim Konfirmasi Bayar</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal 2: Cancel Modal -->
@if($order->canBeCancelled())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('customer.orders.cancel', $order) }}" method="POST" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger">Batalkan Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Apakah Anda yakin ingin membatalkan pesanan ini? Aksi ini tidak dapat dibatalkan.</p>
                <div class="mb-3">
                    <label for="reason" class="form-label fw-semibold small">Alasan Pembatalan:</label>
                    <textarea name="reason" id="reason" class="form-control" rows="2" required placeholder="Contoh: Salah pilih layanan / pakaian belum siap."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button>
                <button type="submit" class="btn btn-danger fw-semibold">Ya, Batalkan Pesanan</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function togglePaymentInstructions(method) {
    const transferBox = document.getElementById('transferInstructions');
    const qrisBox = document.getElementById('qrisInstructions');
    const proofBox = document.getElementById('proofUploadSection');

    if (method === 'transfer') {
        transferBox.style.display = 'block';
        qrisBox.style.display = 'none';
        proofBox.style.display = 'block';
    } else if (method === 'qris') {
        transferBox.style.display = 'none';
        qrisBox.style.display = 'block';
        proofBox.style.display = 'block';
    } else {
        transferBox.style.display = 'none';
        qrisBox.style.display = 'none';
        proofBox.style.display = 'none';
    }
}
</script>
@endpush
@endsection
