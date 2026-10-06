@extends('layouts.app')

@section('title', 'Buku Alamat - Customer')

@section('content')
<div class="workspace-page-heading row align-items-center mb-4">
    <div class="col-md-7">
        <p class="text-muted mb-0">Kelola daftar alamat dan titik koordinat GPS untuk kemudahan penjemputan oleh kurir.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('customer.addresses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Alamat Baru
        </a>
    </div>
</div>

<div class="row g-3">
    @forelse($addresses as $addr)
        <div class="col-md-6">
            <div class="card h-100 p-3 shadow-sm {{ $addr->is_default ? 'border-primary border-2' : '' }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">{{ $addr->label }}</span>
                        @if($addr->is_default)
                            <span class="badge bg-primary"><i class="bi bi-star-fill me-1"></i> Utama</span>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(!$addr->is_default)
                                <li>
                                    <form action="{{ route('customer.addresses.default', $addr) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">Jadikan Alamat Utama</button>
                                    </form>
                                </li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('customer.addresses.edit', $addr) }}">Edit Alamat</a></li>
                            <li>
                                <form action="{{ route('customer.addresses.destroy', $addr) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="small text-dark mb-2 flex-grow-1">{{ $addr->address }}</p>

                <div class="pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                    <span>
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        {{ $addr->latitude ? "{$addr->latitude}, {$addr->longitude}" : 'Titik peta belum di-set' }}
                    </span>
                    <a href="{{ route('customer.addresses.edit', $addr) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                        Ubah Pin
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card text-center py-5">
                <i class="bi bi-geo-alt text-muted fs-1 mb-2"></i>
                <h5 class="fw-bold">Belum Ada Alamat Tersimpan</h5>
                <p class="text-muted small">Tambahkan alamat pertama Anda agar kurir dapat menjemput pakaian kotor.</p>
                <a href="{{ route('customer.addresses.create') }}" class="btn btn-primary btn-sm mx-auto">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Alamat Sekarang
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection
