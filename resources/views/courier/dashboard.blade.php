@extends('layouts.app')

@section('title', 'Dashboard Kurir - Laundry Wash')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1">Dashboard Kurir Lapangan</h3>
        <p class="text-muted mb-0">Selamat bertugas, <strong>{{ $courier->name }}</strong> &bull; Kendaraan: {{ $profile->vehicle_type ?? 'Motor' }} ({{ $profile->vehicle_plate ?? '-' }})</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <!-- Work Status Switcher -->
        <form action="{{ route('courier.profile.status') }}" method="POST" class="d-inline-flex align-items-center gap-2">
            @csrf
            @method('PATCH')
            <span class="small fw-semibold text-muted">Status Ketersediaan:</span>
            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                <option value="available" {{ $profile && $profile->status->value === 'available' ? 'selected' : '' }}>🟢 Tersedia (Online)</option>
                <option value="offline" {{ $profile && $profile->status->value === 'offline' ? 'selected' : '' }}>⚪ Istirahat (Offline)</option>
            </select>
        </form>
    </div>
</div>

<!-- Courier Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card p-3 border-start border-warning border-4 shadow-sm">
            <span class="text-muted small text-uppercase fw-semibold">Tugas Aktif Saat Ini</span>
            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $activeTasks->count() }} Tugas</h3>
            <small class="text-muted mt-1 d-block">Segera selesaikan penjemputan / pengantaran</small>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-3 border-start border-success border-4 shadow-sm">
            <span class="text-muted small text-uppercase fw-semibold">Tugas Selesai Hari Ini</span>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $todayCompletedCount }} Selesai</h3>
            <small class="text-muted mt-1 d-block">Rekap performa operasional kurir harian</small>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-3 border-start border-primary border-4 shadow-sm">
            <span class="text-muted small text-uppercase fw-semibold">Pickup Hari Ini</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $pickupTodayCount }}</h3>
            <small class="text-muted mt-1 d-block">Tugas penjemputan terjadwal</small>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-3 border-start border-info border-4 shadow-sm">
            <span class="text-muted small text-uppercase fw-semibold">Delivery Hari Ini</span>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $deliveryTodayCount }}</h3>
            <small class="text-muted mt-1 d-block">Tugas pengantaran terjadwal</small>
        </div>
    </div>
    <div class="col-12">
        <div class="card p-3 border-start border-primary border-4 shadow-sm">
            <span class="text-muted small text-uppercase fw-semibold">Armada & Pelat Nomor</span>
            <h5 class="fw-bold text-primary mb-0 mt-1">{{ $profile->vehicle_plate ?? 'B 1234 ABC' }}</h5>
            <small class="text-muted mt-1 d-block">{{ $profile->vehicle_type ?? 'Motor' }} &bull; Pastikan GPS HP aktif</small>
        </div>
    </div>
</div>

<!-- Active Task List Section -->
<div class="mb-4">
    <h4 class="fw-bold mb-3"><i class="bi bi-list-task me-2 text-primary"></i> Daftar Tugas Aktif Penjemputan / Pengantaran</h4>

    @if($activeTasks->count() > 0)
        <div class="row g-3">
            @foreach($activeTasks as $task)
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-start {{ $task->type->value === 'pickup' ? 'border-warning' : 'border-info' }} border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $task->type->value === 'pickup' ? 'bg-warning text-dark' : 'bg-info text-white' }} px-3 py-1">
                                    <i class="bi bi-bicycle me-1"></i> {{ $task->type->label() }}
                                </span>
                                <span class="badge badge-status {{ $task->status->badgeClass() }}">
                                    {{ $task->status->label() }}
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mt-2 mb-1">Pesanan: {{ $task->order->order_number }}</h5>
                            <div class="text-muted small mb-3">Ditugaskan sejak: {{ $task->assigned_at->format('d/m/Y H:i') }} ({{ $task->assigned_at->diffForHumans() }})</div>

                            <div class="p-3 bg-light rounded border mb-3">
                                <div class="fw-semibold text-dark mb-1"><i class="bi bi-person me-1 text-primary"></i> {{ $task->order->customer->name }}</div>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-whatsapp text-success me-1"></i> {{ $task->order->customer->phone }}
                                </div>
                                <div class="small text-dark">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                    {{ $task->type->value === 'pickup' ? ($task->order->pickupAddress->address ?? '-') : ($task->order->deliveryAddress->address ?? $task->order->pickupAddress->address ?? '-') }}
                                </div>
                            </div>

                            @if($task->status->value === 'on_the_way')
                                <div class="alert alert-primary py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                                    <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                                    <span><strong>Perjalanan Aktif!</strong> Buka detail untuk menyalakan pemancar GPS live.</span>
                                </div>
                            @endif

                            <div class="d-grid">
                                <a href="{{ route('courier.tasks.show', $task) }}" class="btn btn-primary fw-bold py-2">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Buka Tugas & Operasikan &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card text-center py-5 shadow-sm">
            <div class="card-body">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3 mb-1">Tidak Ada Tugas Tertunda</h5>
                <p class="text-muted small mb-0">Semua tugas penjemputan dan pengantaran telah selesai. Bersiaplah untuk tugas berikutnya!</p>
            </div>
        </div>
    @endif
</div>
@endsection
