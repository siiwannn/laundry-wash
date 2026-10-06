@extends('layouts.app')

@section('title', 'Riwayat Tugas Kurir - Laundry Wash')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <p class="text-muted mb-0">Catatan seluruh tugas penjemputan dan pengantaran yang telah Anda selesaikan.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('courier.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Dashboard Tugas
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>No. Order</th>
                    <th>Tipe Tugas</th>
                    <th>Pelanggan</th>
                    <th>Alamat Tujuan</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    <tr>
                        <td class="fw-bold text-dark">{{ $t->order->order_number }}</td>
                        <td>
                            <span class="badge {{ $t->type->value === 'pickup' ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                {{ $t->type->label() }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $t->order->customer->name ?? '-' }}</div>
                            <small class="text-muted">{{ $t->order->customer->phone ?? '-' }}</small>
                        </td>
                        <td class="small text-muted" style="max-width: 250px;">
                            {{ $t->type->value === 'pickup' ? ($t->order->pickupAddress->address ?? '-') : ($t->order->deliveryAddress->address ?? $t->order->pickupAddress->address ?? '-') }}
                        </td>
                        <td><small class="text-muted">{{ $t->started_at ? $t->started_at->format('d/m/Y H:i') : '-' }}</small></td>
                        <td><small class="text-muted">{{ $t->completed_at ? $t->completed_at->format('d/m/Y H:i') : '-' }}</small></td>
                        <td><span class="badge bg-success"><i class="bi bi-check2 me-1"></i> Selesai</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                            Belum ada riwayat tugas selesai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $tasks->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
