@extends('layouts.app')

@section('title', 'Edit Profil - Laundry Wash')

@section('content')
<div class="workspace-profile">
    <div class="workspace-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <p class="text-muted mb-0">Perbarui data akun dan kata sandi.</p>
        </div>
        <a href="{{ route($profileRoute.'.show') }}" class="btn btn-outline-secondary">Kembali ke profil</a>
    </div>

    <div class="profile-edit-identity" aria-label="Akun yang sedang diedit">
        <div class="profile-avatar profile-avatar-small" aria-hidden="true">{{ $initials }}</div>
        <div>
            <span>{{ $user->role->label() }}</span>
            <strong>{{ $user->name }}</strong>
        </div>
    </div>

    <div class="profile-edit-layout">
        <section class="profile-panel" aria-labelledby="profile-edit-details-heading">
            <div class="profile-panel-heading">
                <div>
                    <h2 id="profile-edit-details-heading">Data profil</h2>
                    <p>Gunakan informasi yang masih aktif dan dapat dihubungi.</p>
                </div>
            </div>
            <form method="POST" action="{{ route($profileRoute.'.update') }}">
                @csrf
                @method('PUT')

                <div class="profile-form-fields">
                    <div class="profile-field-row">
                        <label for="name">Nama lengkap</label>
                        <div>
                            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="profile-field-row">
                        <label for="email">Email</label>
                        <div>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="profile-field-row">
                        <label for="phone">Nomor telepon</label>
                        <div>
                            <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" autocomplete="tel" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if($user->isCourier())
                        <div class="profile-form-divider" aria-hidden="true"></div>
                        <div class="profile-form-section-title">
                            <h3>Kendaraan</h3>
                            <p>Data ini membantu admin mengenali kendaraan untuk tugas kurir.</p>
                        </div>
                        <div class="profile-field-row">
                            <label for="vehicle_type">Jenis kendaraan</label>
                            <div>
                                <input id="vehicle_type" name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" value="{{ old('vehicle_type', $user->courierProfile?->vehicle_type) }}" autocomplete="off" required>
                                @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="profile-field-row">
                            <label for="vehicle_plate">Nomor polisi</label>
                            <div>
                                <input id="vehicle_plate" name="vehicle_plate" class="form-control @error('vehicle_plate') is-invalid @enderror" value="{{ old('vehicle_plate', $user->courierProfile?->vehicle_plate) }}" autocomplete="off" required>
                                @error('vehicle_plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="profile-form-actions">
                    <a href="{{ route($profileRoute.'.show') }}" class="btn btn-light">Batal</a>
                    <button class="btn btn-primary" type="submit">Simpan perubahan</button>
                </div>
            </form>
        </section>

        <section class="profile-panel profile-password-panel" aria-labelledby="password-form-heading">
            <div class="profile-panel-heading">
                <div>
                    <h2 id="password-form-heading">Ganti kata sandi</h2>
                    <p>Konfirmasi dengan kata sandi yang sedang digunakan.</p>
                </div>
                <i class="bi bi-lock" aria-hidden="true"></i>
            </div>
            <form method="POST" action="{{ route($profileRoute.'.password') }}" class="profile-form-fields">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="form-label">Kata sandi saat ini</label>
                    <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="password" class="form-label">Kata sandi baru</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
                </div>

                <button class="btn btn-outline-primary" type="submit">Perbarui kata sandi</button>
            </form>
        </section>
    </div>
</div>
@endsection
