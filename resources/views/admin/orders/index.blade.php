@extends('layouts.app')

@section('title', 'Kelola Pesanan - Admin Laundry Wash')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-6">
        <p class="text-muted mb-0">Pantau dan kelola seluruh siklus pesanan dari pelanggan.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body">
        <form id="order-filters" action="{{ route('admin.orders.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <label for="order-search" class="form-label">Cari pesanan</label>
                <input id="order-search" type="search" name="search" class="form-control form-control-sm" placeholder="Nomor pesanan, nama, atau telepon" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="order-status" class="form-label">Status pesanan</label>
                <select id="order-status" name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status Order --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="order-payment-status" class="form-label">Pembayaran</label>
                <select id="order-payment-status" name="payment_status" class="form-select form-select-sm">
                    <option value="">-- Status Pembayaran --</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm border" title="Reset filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>No. Order</th>
                    <th>Customer</th>
                    <th>Tipe Layanan</th>
                    <th>Berat</th>
                    <th>Total Biaya</th>
                    <th>Status Cucian</th>
                    <th>Pembayaran</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $order->customer->name ?? '-' }}</div>
                            <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i>{{ $order->customer->phone ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-bicycle me-1"></i> Pickup &amp; Delivery Courier
                            </span>
                        </td>
                        <td>
                            @if($order->serviceItem?->actual_quantity !== null)
                                <span class="fw-bold text-success">{{ $order->serviceItem->unit === 'pcs' ? number_format($order->serviceItem->actual_quantity, 0, ',', '.') : $order->serviceItem->actual_quantity }} {{ $order->serviceItem->unit }}</span>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">(Aktual)</small>
                            @elseif($order->serviceItem?->estimated_quantity)
                                <span class="text-muted">{{ $order->serviceItem->unit === 'pcs' ? number_format($order->serviceItem->estimated_quantity, 0, ',', '.') : $order->serviceItem->estimated_quantity }} {{ $order->serviceItem->unit }}</span>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">(Perkiraan)</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="badge badge-status {{ $order->status->badgeClass() }}">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-status {{ $order->payment_status->badgeClass() }}">
                                {{ $order->payment_status->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                Kelola &bull; Proses
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data pesanan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
