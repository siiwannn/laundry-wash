@extends('layouts.app')

@section('title', 'Profil Saya - Laundry Wash')

@section('content')
<div class="workspace-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <p class="text-muted mb-0">Lihat informasi akun yang digunakan untuk pesanan laundry Anda.</p>
    </div>
    <a href="{{ route('customer.profile.edit') }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2" aria-hidden="true"></i>Edit profil
    </a>
</div>

<section class="card" aria-labelledby="profile-information-heading">
    <div class="card-header py-3 px-4">
        <h2 id="profile-information-heading" class="h6 fw-bold mb-0">Informasi akun</h2>
    </div>
    <div class="card-body p-4">
        <dl class="mb-0">
            <div class="row g-2 py-3 border-bottom">
                <dt class="col-sm-4 text-muted fw-normal">Nama lengkap</dt>
                <dd class="col-sm-8 fw-semibold mb-0">{{ $user->name }}</dd>
            </div>
            <div class="row g-2 py-3 border-bottom">
                <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                <dd class="col-sm-8 fw-semibold mb-0 text-break">{{ $user->email }}</dd>
            </div>
            <div class="row g-2 py-3">
                <dt class="col-sm-4 text-muted fw-normal">Nomor telepon</dt>
                <dd class="col-sm-8 fw-semibold mb-0">{{ $user->phone ?: 'Belum ditambahkan' }}</dd>
            </div>
        </dl>
    </div>
</section>
@endsection
