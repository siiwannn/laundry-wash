@extends('layouts.app')

@section('title', 'Layanan Laundry - Admin')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <p class="text-muted mb-0">Kelola tarif harga per kilogram, estimasi durasi pencucian, dan status ketersediaan.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Layanan Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>Nama Layanan</th>
                    <th>Deskripsi</th>
                    <th>Harga / Kg</th>
                    <th>Estimasi Waktu</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td class="fw-bold text-dark">{{ $service->name }}</td>
                        <td><small class="text-muted">{{ $service->description ?? '-' }}</small></td>
                        <td class="fw-semibold text-primary">Rp {{ number_format($service->price_per_kg, 0, ',', '.') }}</td>
                        <td>{{ $service->estimated_hours ? $service->estimated_hours . ' Jam' : '-' }}</td>
                        <td>
                            @if($service->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non-aktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status layanan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada layanan laundry terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($services->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $services->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
