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
        <button type="button" class="btn btn-success fw-bold px-4" id="payWithMidtrans">
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
                                <h6 class="fw-bold mb-1">{{ $payment->method?->label() ?? 'Menunggu pilihan Midtrans' }} &bull; Rp {{ number_format($payment->amount, 0, ',', '.') }}</h6>
                                <small class="text-muted">Dibuat: {{ $payment->created_at->format('d/m/Y H:i') }}</small>
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
                        Pickup dan Delivery oleh Courier
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

<!-- Cancel Modal -->
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
@if($order->canAcceptPayment())
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
@endif
<script>
document.getElementById('payWithMidtrans')?.addEventListener('click', async function () {
    const button = this;
    let refreshToken = false;
    const restoreButton = () => {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-credit-card me-1"></i> Bayar Sekarang';
    };

    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan pembayaran';

    try {
        const response = await fetch(@json(route('customer.orders.pay', $order)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ refresh_token: refreshToken }),
        });
        const data = await response.json();
        if (!response.ok || !data.snap_token) throw new Error(data.message || 'Pembayaran tidak dapat dibuat.');
        refreshToken = false;

        window.snap.pay(data.snap_token, {
            onSuccess: () => window.location.reload(),
            onPending: () => {
                restoreButton();
                alert('Pembayaran masih menunggu penyelesaian. Anda dapat membuka Snap kembali.');
            },
            onError: () => {
                refreshToken = true;
                restoreButton();
                alert('Pembayaran gagal diproses. Silakan coba lagi.');
            },
            onClose: () => {
                refreshToken = true;
                restoreButton();
                alert('Pembayaran dibatalkan atau belum diselesaikan.');
            },
        });
    } catch (error) {
        alert(error.message);
        restoreButton();
    }
});
</script>
@endpush
@endsection
