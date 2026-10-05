@extends('layouts.app')
@section('title', 'Status Pembayaran - Laundry Wash')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h3 fw-bold mb-1">Status Pembayaran</h1><p class="text-muted mb-0">Status QRIS dan Virtual Account diperbarui otomatis oleh Midtrans.</p></div><form><select name="status" class="form-select" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(\App\Enums\PaymentStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Order</th><th>Customer</th><th>Metode</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($payments as $payment)<tr><td><a href="{{ route('admin.orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a></td><td>{{ $payment->order->customer->name }}</td><td>{{ $payment->method?->label() ?? 'Menunggu pilihan Midtrans' }}<div class="small text-muted">{{ $payment->gateway_transaction_id ?: $payment->gateway_order_id }}</div></td><td class="fw-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td><td><span class="badge {{ $payment->status->badgeClass() }}">{{ $payment->status->label() }}</span><div class="small text-muted mt-1">{{ $payment->gateway_status ?: '-' }}</div></td><td><span class="small text-muted">Otomatis oleh Midtrans</span></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-5">Belum ada pembayaran.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $payments->links() }}</div></div>
@endsection
