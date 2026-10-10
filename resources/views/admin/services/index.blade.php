@extends('layouts.app')

@section('title', 'Layanan Laundry - Admin')

@section('content')
<div class="workspace-page-heading mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <p class="text-muted mb-0">Kelola layanan, tarif per satuan, dan estimasi durasi.</p>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary fw-semibold">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah Layanan
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>Nama Layanan</th>
                    <th>Deskripsi</th>
                    <th>Tarif / Satuan</th>
                    <th>Estimasi Waktu</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td class="fw-bold text-dark">{{ $service->name }}</td>
                        <td><small class="text-muted">{{ $service->description ?? '-' }}</small></td>
                        <td class="fw-semibold text-primary">
                            @if($service->price_per_unit > 0)
                                Rp {{ number_format($service->price_per_unit, 0, ',', '.') }} / {{ $service->unit }}
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis">Tarif belum diatur</span>
                            @endif
                        </td>
                        <td>{{ $service->estimated_hours ? $service->estimated_hours . ' Jam' : '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus layanan ini dari katalog? Order lama tetap menyimpan rincian layanannya.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada layanan laundry terdaftar.</td>
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
