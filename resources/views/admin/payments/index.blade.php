@extends('layouts.app')
@section('title', 'Status Pembayaran - Laundry Wash')
@section('content')
<div class="workspace-page-heading justify-content-between">
    <div>
        <p class="text-muted mb-0">Status QRIS dan Virtual Account diperbarui otomatis oleh Midtrans.</p>
    </div>
    <form method="GET" action="{{ route('admin.payments.index') }}">
        <label for="payment-status-filter" class="visually-hidden">Filter status pembayaran</label>
        <select id="payment-status-filter" name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach(\App\Enums\PaymentStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <noscript><button class="btn btn-primary mt-2">Terapkan filter</button></noscript>
    </form>
</div>
<div class="card">
    <div class="table-responsive" tabindex="0" role="region" aria-label="Daftar pembayaran">
        <table class="table mb-0">
            <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Metode</th><th scope="col">Nominal</th><th scope="col">Status</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a></td>
                        <td>{{ $payment->order->customer->name }}</td>
                        <td>
                            {{ $payment->method?->label() ?? 'Menunggu pilihan Midtrans' }}
                            <div class="small text-muted">{{ $payment->gateway_transaction_id ?: $payment->gateway_order_id }}</div>
                        </td>
                        <td class="fw-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $payment->status->badgeClass() }}">{{ $payment->status->label() }}</span>
                            <div class="small text-muted mt-1">{{ $payment->gateway_status ?: '-' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">Belum ada pembayaran untuk ditampilkan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer bg-white">{{ $payments->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
