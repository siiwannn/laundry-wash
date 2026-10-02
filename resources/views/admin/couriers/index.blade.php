@extends('layouts.app')

@section('title', 'Kelola Kurir - Admin')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1">Manajemen Kurir Lapangan</h3>
        <p class="text-muted mb-0">Kelola armada kurir penjemputan dan pengantaran laundry terintegrasi GPS.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i> Daftarkan Kurir Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>Nama Kurir</th>
                    <th>Kontak HP / WA</th>
                    <th>Kendaraan</th>
                    <th>Status Kerja</th>
                    <th>Status Akun</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($couriers as $courier)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $courier->name }}</div>
                            <small class="text-muted">{{ $courier->email }}</small>
                        </td>
                        <td>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $courier->phone) }}" target="_blank" class="text-decoration-none text-success fw-semibold">
                                <i class="bi bi-whatsapp me-1"></i> {{ $courier->phone }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-bicycle me-1"></i> {{ $courier->courierProfile->vehicle_type ?? 'Motor' }}
                            </span>
                            <small class="text-muted d-block mt-1">{{ $courier->courierProfile->vehicle_plate ?? '-' }}</small>
                        </td>
                        <td>
                            @php $profileStatus = $courier->courierProfile->status ?? null; @endphp
                            @if($profileStatus)
                                <span class="badge {{ $profileStatus->badgeClass() }}">
                                    {{ $profileStatus->label() }}
                                </span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($courier->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non-aktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.couriers.toggle', $courier) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $courier->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $courier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada kurir terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($couriers->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $couriers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
