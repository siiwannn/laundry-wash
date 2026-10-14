@extends('layouts.app')

@section('title', 'Profil Akun - Laundry Wash')

@section('content')
<div class="workspace-profile">
    <div class="workspace-page-heading mb-3">
        <div>
            <p class="text-muted mb-0">Informasi akun yang digunakan di Laundry Wash.</p>
        </div>
    </div>

    <div class="profile-layout">
        <section class="profile-identity" aria-labelledby="profile-name">
            <div class="profile-avatar" aria-hidden="true">{{ $initials }}</div>
            <div class="profile-identity-copy">
                <p class="profile-identity-label">{{ $user->role->label() }}</p>
                <h2 id="profile-name">{{ $user->name }}</h2>
                <p class="profile-identity-email">{{ $user->email }}</p>
            </div>
            <a href="{{ route($profileRoute.'.edit') }}" class="btn btn-primary profile-edit-button">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                <span>Edit profil</span>
            </a>
        </section>

        <section class="profile-panel" aria-label="Rincian akun">
            <div class="profile-details-grid">
                <div class="profile-detail-section">
                    <h3>Kontak</h3>
                    <dl class="profile-detail-list mb-0">
                        <div class="profile-detail-row">
                            <dt>Email</dt>
                            <dd>{{ $user->email ?: 'Belum ditambahkan' }}</dd>
                        </div>
                        <div class="profile-detail-row">
                            <dt>Nomor telepon</dt>
                            <dd>{{ $user->phone ?: 'Belum ditambahkan' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($user->isCourier())
                    <div class="profile-detail-section">
                        <h3>Kendaraan & ketersediaan</h3>
                        <dl class="profile-detail-list mb-0">
                            <div class="profile-detail-row">
                                <dt>Kendaraan</dt>
                                <dd>{{ $user->courierProfile?->vehicle_type ?: 'Belum diatur' }}</dd>
                            </div>
                            <div class="profile-detail-row">
                                <dt>Nomor polisi</dt>
                                <dd>{{ $user->courierProfile?->vehicle_plate ?: 'Belum diatur' }}</dd>
                            </div>
                            <div class="profile-detail-row">
                                <dt>Status kurir</dt>
                                <dd>
                                    @if($user->courierProfile?->status)
                                        <span class="badge {{ $user->courierProfile->status->badgeClass() }}">{{ $user->courierProfile->status->label() }}</span>
                                    @else
                                        Belum diatur
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                @else
                    <div class="profile-detail-section">
                        <h3>Informasi akun</h3>
                        <dl class="profile-detail-list mb-0">
                            <div class="profile-detail-row">
                                <dt>Bergabung</dt>
                                <dd>{{ $user->created_at?->locale('id')->translatedFormat('d F Y') ?? 'Tidak tersedia' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
