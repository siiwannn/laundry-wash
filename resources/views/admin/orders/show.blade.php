@extends('layouts.app')

@section('title', 'Kelola Pesanan ' . $order->order_number . ' - Admin')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge badge-status {{ $order->status->badgeClass() }} fs-6">
                {{ $order->status->label() }}
            </span>
            <span class="badge badge-status {{ $order->payment_status->badgeClass() }} fs-6">
                {{ $order->payment_status->label() }}
            </span>
        </div>
        <p class="text-muted mb-0">Dibuat pada {{ $order->created_at->format('d F Y, H:i') }} WIB &bull; Pelanggan: <strong>{{ $order->customer->name }}</strong></p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<!-- Workflow Action Toolbar (Contextual to Current Status) -->
<div class="card border-primary mb-4 bg-primary-subtle shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Aksi Operasional yang Diperlukan:</h6>
                <div class="small text-dark">
                    @if($order->status->value === 'pending')
                        Pesanan baru masuk dari customer. Verifikasi dan konfirmasi agar dapat diproses.
                    @elseif($order->status->value === 'confirmed')
                        Pesanan terkonfirmasi. Tugaskan kurir pickup untuk menjemput pakaian ke lokasi customer.
                    @elseif($order->status->value === 'picked_up')
                        Pakaian telah dijemput kurir. Konfirmasi kedatangan pakaian di outlet workshop.
                    @elseif($order->status->value === 'received_at_laundry')
                        Pakaian telah tiba. Masukkan <strong>berat aktual (kg)</strong> untuk menghitung total tagihan akhir.
                    @elseif(in_array($order->status->value, ['washing', 'drying', 'ironing']))
                        Laundry sedang dalam proses pengerjaan. Perbarui tahapan cuci saat selesai satu siklus.
                    @elseif($order->status->value === 'ready')
                        Laundry telah selesai dan siap. Menunggu customer mengirim pembayaran.
                    @elseif($order->status->value === 'waiting_payment')
                        Pembayaran sedang diproses otomatis oleh Midtrans.
                    @elseif($order->status->value === 'paid')
                        Pembayaran sudah lunas. Tugaskan kurir delivery untuk mengantar laundry.
                    @elseif($order->status->value === 'completed')
                        Pesanan telah selesai secara tuntas dan pembayaran lunas.
                    @else
                        Status pesanan: {{ $order->status->label() }}.
                    @endif
                </div>
            </div>
            <div class="col-lg-5 text-lg-end d-flex flex-wrap justify-content-lg-end gap-2">
                <!-- Action 1: Confirm Order -->
                @if($order->status->value === 'pending')
                    <form action="{{ route('admin.orders.confirm', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Konfirmasi Pesanan Ini
                        </button>
                    </form>
                @endif

                <!-- Action 2: Assign Pickup Courier -->
                @if($order->status->value === 'confirmed')
                    <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#assignCourierModal" onclick="setAssignType('pickup')">
                        <i class="bi bi-bicycle me-1"></i> Tugaskan Kurir Pickup
                    </button>
                @endif

                <!-- Action 3: Confirm Received at Laundry Workshop -->
                @if(in_array($order->status->value, ['picked_up', 'confirmed']))
                    <form action="{{ route('admin.orders.receive', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-info btn-sm fw-semibold text-white">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Terima di Workshop Laundry
                        </button>
                    </form>
                @endif

                <!-- Action 4: Input / Update Weight Modal -->
                @if($order->status->value === 'received_at_laundry')
                    <button type="button" class="btn btn-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#weightModal">
                        <i class="bi bi-speedometer2 me-1"></i> {{ $order->actual_weight ? 'Perbarui Berat / Biaya' : 'Timbang Berat Aktual' }}
                    </button>
                @endif

                <!-- Action 5: Advance Processing Stage -->
                @if(in_array($order->status->value, ['received_at_laundry', 'washing', 'drying', 'ironing']))
                    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#stageModal">
                        <i class="bi bi-arrow-repeat me-1"></i> Update Tahapan Cuci
                    </button>
                @endif

                <!-- Action 6: Assign Delivery Courier -->
                @if($order->status->value === 'paid')
                    <button type="button" class="btn btn-info btn-sm fw-semibold text-white" data-bs-toggle="modal" data-bs-target="#assignCourierModal" onclick="setAssignType('delivery')">
                        <i class="bi bi-bicycle me-1"></i> Tugaskan Kurir Antar (Delivery)
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Order Items, Price Summary & Payment Verification -->
    <div class="col-lg-7">
        <!-- Order Items & Billing Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt-cutoff me-2 text-primary"></i> Rincian Paket Layanan & Biaya</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th>Layanan Laundry</th>
                            <th class="text-center">Tarif / Kg</th>
                            <th class="text-center">Kuantitas / Berat</th>
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
                                    @if(!$order->actual_weight)
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">(Estimasi)</small>
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
                                <td colspan="3" class="text-end text-muted">Biaya Tambahan (Parfum/Kotor Tebal):</td>
                                <td class="text-end fw-bold">Rp {{ number_format($order->additional_fee, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="table-primary border-top border-primary">
                            <td colspan="3" class="text-end fw-bold fs-6">TOTAL TAGIHAN:</td>
                            <td class="text-end fw-bold fs-5 text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($order->notes)
                <div class="card-footer bg-light py-2 px-3">
                    <small class="text-muted fw-semibold">Catatan dari Pelanggan:</small>
                    <p class="mb-0 small text-dark fst-italic">"{{ $order->notes }}"</p>
                </div>
            @endif
        </div>

        <!-- Payments Section -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-success" aria-hidden="true"></i> Riwayat pembayaran</h5>
                <span class="badge badge-status {{ $order->payment_status->badgeClass() }}">
                    {{ $order->payment_status->label() }}
                </span>
            </div>
            <div class="card-body">
                @if($order->payments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($order->payments as $payment)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <div>
                                    <div class="fw-bold text-dark mb-1">
                                        {{ $payment->method?->label() ?? 'Menunggu pilihan Midtrans' }} &bull; Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="small text-muted">
                                        Waktu: {{ $payment->created_at->format('d/m/Y H:i') }}
                                        @if($payment->gateway_transaction_id) &bull; ID: {{ $payment->gateway_transaction_id }} @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="badge {{ $payment->status->badgeClass() }} py-2 px-3">
                                        {{ $payment->status->label() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-hourglass-split fs-2 d-block mb-2"></i>
                        Belum ada transaksi Midtrans untuk pesanan ini.
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Status Audit History Timeline -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> Timeline Riwayat Perubahan Status</h5>
            </div>
            <div class="card-body">
                <div class="timeline position-relative ps-4" style="border-left: 2px solid #e2e8f0; margin-left: 10px;">
                    @foreach($order->statusHistories as $history)
                        <div class="mb-3 position-relative">
                            <div class="position-absolute" style="left: -29px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: #0d6efd; border: 2px solid #ffffff;"></div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="badge {{ $history->status->badgeClass() }}">{{ $history->status->label() }}</span>
                                <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i') }} ({{ $history->created_at->diffForHumans() }})</small>
                            </div>
                            <p class="small text-dark mt-1 mb-0">{{ $history->note }}</p>
                            <small class="text-muted" style="font-size: 0.72rem;">Oleh: {{ $history->user->name ?? 'Sistem' }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer Info, Addresses & Courier Assignment Status -->
    <div class="col-lg-5">
        <!-- Customer Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i> Informasi Pelanggan</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fw-bold fs-4">
                        {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $order->customer->name }}</h6>
                        <span class="text-muted small">{{ $order->customer->email }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <span class="small text-muted d-block">No. WhatsApp / Telepon:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer->phone) }}" target="_blank" class="fw-semibold text-success text-decoration-none">
                        <i class="bi bi-whatsapp me-1"></i> {{ $order->customer->phone }}
                    </a>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Alamat Penjemputan / Pengantaran:</span>
                    <p class="small fw-semibold mb-1">{{ $order->pickupAddress->address ?? 'Drop-off langsung di workshop' }}</p>
                    @if($order->pickupAddress && $order->pickupAddress->latitude)
                        <small class="text-muted">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            Koordinat: {{ $order->pickupAddress->latitude }}, {{ $order->pickupAddress->longitude }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Assigned Courier Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-bicycle me-2 text-warning"></i> Penugasan Kurir Lapangan</h6>
                @if($order->activeAssignment)
                    <span class="badge bg-primary">Aktif</span>
                @endif
            </div>
            <div class="card-body">
                @if($order->assignments->count() > 0)
                    @foreach($order->assignments as $assign)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $assign->type->value === 'pickup' ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                    {{ $assign->type->label() }}
                                </span>
                                <span class="badge {{ $assign->status->badgeClass() }}">
                                    {{ $assign->status->label() }}
                                </span>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $assign->courier->name }}</h6>
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-bicycle me-1"></i> {{ $assign->courier->courierProfile->vehicle_type ?? 'Motor' }} ({{ $assign->courier->courierProfile->vehicle_plate ?? '-' }})
                            </small>
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-whatsapp text-success me-1"></i> {{ $assign->courier->phone }}
                            </small>

                            @if($assign->latestLocation)
                                <div class="p-2 bg-white rounded border small mt-2">
                                    <div class="text-success fw-semibold"><i class="bi bi-broadcast me-1"></i> Posisi Terakhir Kurir:</div>
                                    <span class="text-muted">Lat: {{ $assign->latestLocation->latitude }}, Lng: {{ $assign->latestLocation->longitude }}</span>
                                    <div class="text-muted" style="font-size: 0.72rem;">Dicatat: {{ $assign->latestLocation->recorded_at->format('H:i:s') }} ({{ $assign->latestLocation->recorded_at->diffForHumans() }})</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                        Belum ada kurir yang ditugaskan untuk pesanan ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Assign Courier Modal -->
<div class="modal fade" id="assignCourierModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.assign-courier', $order) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="type" id="modalAssignType" value="pickup">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalAssignTitle">Tugaskan Kurir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Pilih kurir yang sedang tersedia untuk melaksanakan tugas ini.</p>
                <div class="mb-3">
                    <label for="courier_id" class="form-label fw-semibold small">Pilih Kurir:</label>
                    <select name="courier_id" id="courier_id" class="form-select" required>
                        <option value="">-- Pilih Kurir --</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier->id }}">
                                {{ $courier->name }} &bull; {{ $courier->courierProfile?->vehicle_plate ?? 'Kendaraan belum diatur' }} ({{ $courier->courierProfile?->status?->label() ?? 'Profil belum tersedia' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Input / Update Weight Modal -->
<div class="modal fade" id="weightModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.weight', $order) }}" method="POST" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Penimbangan Berat Aktual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Total biaya cucian akan dihitung ulang secara otomatis berdasarkan berat aktual.</p>
                
                <div class="mb-3">
                    <label for="actual_weight" class="form-label fw-semibold small">Berat Aktual (Kg):</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0.1" name="actual_weight" id="actual_weight" class="form-control" value="{{ old('actual_weight', $order->actual_weight ?: $order->estimated_weight) }}" required>
                        <span class="input-group-text">Kg</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="additional_fee" class="form-label fw-semibold small">Biaya Tambahan (Opsional):</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="1000" min="0" name="additional_fee" id="additional_fee" class="form-control" value="{{ old('additional_fee', $order->additional_fee) }}">
                    </div>
                    <small class="text-muted">Misal untuk deterjen noda membandel atau pewangi ekstra.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-semibold">Simpan & Hitung Ulang Total</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Update Laundry Stage Modal -->
<div class="modal fade" id="stageModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.stage', $order) }}" method="POST" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update Tahapan Cuci</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="stage_status" class="form-label fw-semibold small">Tahapan Selanjutnya:</label>
                    <select name="status" id="stage_status" class="form-select" required>
                        @if($order->status->value === 'received_at_laundry')
                            <option value="washing">Washing (Sedang Dicuci)</option>
                        @elseif($order->status->value === 'washing')
                            <option value="drying">Drying (Sedang Dikeringkan)</option>
                        @elseif($order->status->value === 'drying')
                            <option value="ironing">Ironing (Sedang Disetrika)</option>
                        @elseif($order->status->value === 'ironing')
                            <option value="ready">Ready (Selesai & Siap Dibayar)</option>
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label for="stage_note" class="form-label fw-semibold small">Catatan Tambahan (Opsional):</label>
                    <input type="text" name="note" id="stage_note" class="form-control" placeholder="Contoh: Pakaian telah bersih dan dipindahkan ke rak siap kirim.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Update Status</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function setAssignType(type) {
    document.getElementById('modalAssignType').value = type;
    const title = type === 'pickup' ? 'Tugaskan Kurir Penjemputan (Pickup)' : 'Tugaskan Kurir Pengantaran (Delivery)';
    document.getElementById('modalAssignTitle').innerText = title;
}
</script>
@endpush
@endsection
