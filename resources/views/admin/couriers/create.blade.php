@extends('layouts.app')

@section('title', 'Daftarkan Kurir Baru - Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Pendaftaran Akun Kurir Baru</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.couriers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Nama Lengkap Kurir</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small">Email Login</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="kurir@laundrywash.com">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold small">No. WhatsApp / HP</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required placeholder="08123456789">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="vehicle_type" class="form-label fw-semibold small">Tipe Kendaraan</label>
                            <input type="text" name="vehicle_type" id="vehicle_type" class="form-control" value="{{ old('vehicle_type', 'Honda Vario') }}" required placeholder="Contoh: Honda Vario 160">
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_plate" class="form-label fw-semibold small">Nomor Polisi (Plat)</label>
                            <input type="text" name="vehicle_plate" id="vehicle_plate" class="form-control" value="{{ old('vehicle_plate') }}" required placeholder="Contoh: B 1234 XYZ">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold small">Password Akun (Min. 6 Karakter)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.couriers.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">Daftarkan Kurir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
