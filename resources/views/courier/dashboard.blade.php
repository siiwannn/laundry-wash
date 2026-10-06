@extends('layouts.app')

@section('title', 'Dashboard Kurir - Laundry Wash')

@section('content')
<div class="dashboard-heading">
    <div>
        <p class="mb-0">{{ $courier->name }} &middot; {{ $profile->vehicle_type ?? 'Motor' }} &middot; {{ $profile->vehicle_plate ?? 'Kendaraan belum diatur' }}</p>
    </div>
    <form id="courier-availability" action="{{ route('courier.profile.status') }}" method="POST" class="d-inline-flex align-items-center gap-2">
        @csrf
        @method('PATCH')
        <span class="small fw-semibold text-muted">Status kerja</span>
        <select aria-label="Status kerja" name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
            <option value="available" {{ $profile && $profile->Tersedia</option>
            <option value="offline" {{ $profile && $profile->Istirahat</option>
        </select>
    </form>
</div>

<section class="dashboard-summary" aria-label="Ringkasan kurir">
    <div class="role-summary-grid">
        <x-dashboard-metric label="Tugas aktif" :value="$activeTasks->count()" icon="list-task" tone="orange" note="Menunggu atau dalam perjalanan" />
        <x-dashboard-metric label="Pickup hari ini" :value="$pickupTodayCount" icon="box-arrow-in-down" tone="blue" note="Penjemputan" />
        <x-dashboard-metric label="Delivery hari ini" :value="$deliveryTodayCount" icon="truck" tone="purple" note="Pengantaran" />
        <x-dashboard-metric label="Selesai hari ini" :value="$todayCompletedCount" icon="check2-circle" tone="green" note="Tugas diselesaikan" />
    </div>
</section>

<!-- Active Task List Section -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="dashboard-section-title mb-0">Tugas aktif</h2><a href="{{ route('courier.history') }}" class="dashboard-text-link">Riwayat</a></div>

    @if($activeTasks->count() > 0)
        <div class="row g-3">
            @foreach($activeTasks as $task)
                <div class="col-lg-6">
                    <div class="card h-100 courier-assignment-card">
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
                                    Lihat tugas
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
                <p class="text-muted small mb-0">Penjemputan atau pengantaran akan tampil setelah admin menugaskan Anda.</p>
            </div>
        </div>
    @endif
</div>
@endsection
