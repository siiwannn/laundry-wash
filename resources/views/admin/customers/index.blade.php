@extends('layouts.app')
@section('title', 'Customer - Laundry Wash')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-muted mb-0">Kelola akun dan lihat riwayat order pelanggan.</p></div></div>
<div class="card"><div class="card-body border-bottom"><form class="row g-2"><div class="col-md-5"><input name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama atau email"></div><div class="col-auto"><button class="btn btn-primary">Cari</button></div></form></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Customer</th><th>Telepon</th><th>Order</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($customers as $customer)<tr><td><strong>{{ $customer->name }}</strong><div class="small text-muted">{{ $customer->email }}</div></td><td>{{ $customer->phone ?: '-' }}</td><td>{{ $customer->orders_count }}</td><td><span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="text-end"><a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-5">Belum ada customer.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $customers->links() }}</div></div>
@endsection
