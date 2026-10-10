@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Customer')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <p class="text-muted mb-0">Daftar seluruh transaksi dan status pesanan yang pernah Anda lakukan.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Baru
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>No. Order</th>
                    <th>Layanan</th>
                    <th>Kuantitas</th>
                    <th>Total Tagihan</th>
                    <th>Status Cucian</th>
                    <th>Status Bayar</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('customer.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('d/m/Y H:i') }} WIB</div>
                        </td>
                        <td>{{ $order->serviceItem?->service_name_snapshot ?? $order->serviceItem?->service?->name ?? 'Layanan' }}</td>
                        <td>{{ $order->serviceItem?->unit === 'pcs' ? number_format($order->serviceItem?->actual_quantity ?? $order->serviceItem?->estimated_quantity, 0, ',', '.') : ($order->serviceItem?->actual_quantity ?? $order->serviceItem?->estimated_quantity ?? '-') }} {{ $order->serviceItem?->unit }}</td>
                        <td class="fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
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
                            <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                Rincian &bull; Nota
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Anda belum memiliki riwayat pesanan.
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
