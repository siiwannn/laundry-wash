<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\OrderStatus;
use App\Models\CourierAssignment;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;

class CourierTrackingService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly RoadRouteService $roadRoute,
    ) {}

    /**
     * Ingest courier GPS location update.
     */
    public function recordLocation(User $courier, int $assignmentId, float $latitude, float $longitude, ?float $accuracy, ?float $speed = null, ?float $heading = null): CourierLocation
    {
        $assignment = CourierAssignment::where('id', $assignmentId)
            ->where('courier_id', $courier->id)
            ->first();

        if (! $assignment) {
            throw new Exception('Penugasan tidak ditemukan untuk kurir ini.');
        }

        if ($assignment->status !== AssignmentStatus::ON_THE_WAY) {
            throw new Exception('GPS hanya dapat dikirim setelah perjalanan dimulai.');
        }

        $expectedOrderStatus = $assignment->type === AssignmentType::PICKUP
            ? OrderStatus::COURIER_TO_PICKUP
            : OrderStatus::COURIER_TO_CUSTOMER;

        if ($assignment->order->status !== $expectedOrderStatus) {
            throw new Exception('Status order tidak sesuai dengan perjalanan kurir yang aktif.');
        }

        $location = CourierLocation::create([
            'courier_id' => $courier->id,
            'assignment_id' => $assignment->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'speed' => $speed,
            'heading' => $heading,
            'recorded_at' => now(),
        ]);
        $courier->courierProfile?->update(['current_latitude' => $latitude, 'current_longitude' => $longitude]);
        $this->activityLog->record($courier, "Update GPS assignment {$assignment->id}");

        return $location;
    }

    /**
     * Get latest location payload for an order (polled by customer or admin).
     */
    public function getLatestTrackingData(Order $order): array
    {
        $activeAssignment = $order->assignments()
            ->where('status', AssignmentStatus::ON_THE_WAY)
            ->with(['courier.courierProfile', 'latestLocation'])
            ->latest('started_at')
            ->first();

        if (! $activeAssignment) {
            $waitingAssignment = $order->assignments()
                ->where('status', AssignmentStatus::ASSIGNED)
                ->with(['courier.courierProfile'])
                ->latest('assigned_at')
                ->first();

            if ($waitingAssignment) {
                $journey = $waitingAssignment->type === AssignmentType::DELIVERY
                    ? 'Pengantaran'
                    : 'Penjemputan';

                return [
                    'is_active' => false,
                    'is_waiting_for_trip' => true,
                    'journey_status' => "Kurir {$journey} sudah ditugaskan, menunggu perjalanan dimulai.",
                    'message' => 'Halaman ini akan diperbarui otomatis saat kurir memulai perjalanan.',
                ];
            }

            return [
                'is_active' => false,
                'is_waiting_for_trip' => false,
                'journey_status' => $this->inactiveJourneyStatus($order),
                'message' => 'Tidak ada kurir yang sedang aktif melakukan perjalanan untuk pesanan ini.',
            ];
        }

        $courier = $activeAssignment->courier;
        $profile = $courier->courierProfile;
        $latestLocation = $activeAssignment->latestLocation;

        // Destination coords: pickup address or delivery address
        $targetAddress = $activeAssignment->type->value === 'pickup'
            ? $order->pickupAddress
            : ($order->deliveryAddress ?? $order->pickupAddress);

        $route = null;
        $movementRoute = null;
        $routeCacheKey = "courier-assignment-route:{$activeAssignment->id}";
        if ($latestLocation && $targetAddress?->latitude !== null && $targetAddress?->longitude !== null) {
            $route = $this->roadRoute->route(
                (float) $latestLocation->latitude,
                (float) $latestLocation->longitude,
                (float) $targetAddress->latitude,
                (float) $targetAddress->longitude,
            );

            if ($route !== null) {
                Cache::put($routeCacheKey, $route, now()->addDay());
            }

            $previousLocation = $activeAssignment->locations()
                ->whereKeyNot($latestLocation->id)
                ->latest('recorded_at')
                ->first();

            if ($previousLocation) {
                $movementRoute = $this->roadRoute->route(
                    (float) $previousLocation->latitude,
                    (float) $previousLocation->longitude,
                    (float) $latestLocation->latitude,
                    (float) $latestLocation->longitude,
                );
            }
        }

        if ($route === null) {
            $cachedRoute = Cache::get($routeCacheKey);
            $route = is_array($cachedRoute) ? $cachedRoute : null;
        }

        $journeyStatus = $this->activeJourneyStatus($activeAssignment->type, $route['distance_meters'] ?? null);

        return [
            'is_active' => true,
            'is_waiting_for_trip' => false,
            'assignment_id' => $activeAssignment->id,
            'type' => $activeAssignment->type->value,
            'type_label' => $activeAssignment->type->label(),
            'assignment_status' => $activeAssignment->status->value,
            'journey_status' => $journeyStatus,
            'order_status' => $order->status->value,
            'order_status_label' => $order->status->label(),
            'courier' => [
                'name' => $courier->name,
                'phone' => $courier->phone,
                'vehicle_type' => $profile ? $profile->vehicle_type : 'Motor',
                'vehicle_plate' => $profile ? $profile->vehicle_plate : '-',
            ],
            'courier_location' => $latestLocation ? [
                'latitude' => (float) $latestLocation->latitude,
                'longitude' => (float) $latestLocation->longitude,
                'accuracy' => (float) $latestLocation->accuracy,
                'speed' => $latestLocation->speed !== null ? (float) $latestLocation->speed : null,
                'heading' => $latestLocation->heading !== null ? (float) $latestLocation->heading : null,
                'recorded_at' => $latestLocation->recorded_at->format('H:i:s'),
                'recorded_at_iso' => $latestLocation->recorded_at->toIso8601String(),
                'recorded_human' => $latestLocation->recorded_at->diffForHumans(),
            ] : null,
            'route' => $route,
            'movement_route' => $movementRoute,
            'destination' => $targetAddress ? [
                'label' => $targetAddress->label,
                'address' => $targetAddress->address,
                'latitude' => (float) $targetAddress->latitude,
                'longitude' => (float) $targetAddress->longitude,
            ] : null,
        ];
    }

    private function activeJourneyStatus(AssignmentType $type, ?int $distanceMeters): string
    {
        if ($distanceMeters !== null && $distanceMeters <= 500) {
            return 'Hampir tiba';
        }

        return $type === AssignmentType::PICKUP
            ? 'Menuju lokasi pickup'
            : 'Menuju customer';
    }

    private function inactiveJourneyStatus(Order $order): string
    {
        return in_array($order->status, [OrderStatus::PICKED_UP, OrderStatus::RECEIVED_AT_LAUNDRY], true)
            ? 'Sudah mengambil laundry'
            : $order->status->label();
    }
}
