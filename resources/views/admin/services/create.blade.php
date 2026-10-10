@extends('layouts.app')

@section('title', 'Tambah Layanan - Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Tambah Layanan Laundry Baru</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.services.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Nama Layanan</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="255" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small">Deskripsi Layanan</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Rincian proses atau keunggulan layanan">{{ old('description') }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="unit" class="form-label fw-semibold small">Satuan</label>
                            <select name="unit" id="unit" class="form-select" required>
                                <option value="kg" @selected(old('unit', 'kg') === 'kg')>Kilogram (kg)</option>
                                <option value="pcs" @selected(old('unit') === 'pcs')>Barang (pcs)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="price_per_unit" class="form-label fw-semibold small">Harga per satuan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="1" min="1" name="price_per_unit" id="price_per_unit" class="form-control @error('price_per_unit') is-invalid @enderror" value="{{ old('price_per_unit') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="estimated_hours" class="form-label fw-semibold small">Estimasi Durasi (Jam)</label>
                            <div class="input-group">
                                <input type="number" min="1" name="estimated_hours" id="estimated_hours" class="form-control" value="{{ old('estimated_hours', 48) }}" required>
                                <span class="input-group-text">Jam</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.services.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
