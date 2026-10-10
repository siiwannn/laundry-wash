<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Http\Controllers\Controller;
use App\Models\CourierAssignment;
use App\Services\CourierAssignmentService;
use App\Services\RoadRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CourierTaskController extends Controller
{
    public function __construct(
        protected CourierAssignmentService $assignmentService,
        protected RoadRouteService $roadRoute
    ) {}

    public function show(CourierAssignment $assignment): View
    {
        $this->authorizeCourier($assignment);

        $assignment->load([
            'order.customer',
            'order.pickupAddress',
            'order.deliveryAddress',
            'order.items.service',
            'latestLocation',
        ]);

        $targetAddress = $assignment->type === AssignmentType::PICKUP
            ? $assignment->order->pickupAddress
            : ($assignment->order->deliveryAddress ?? $assignment->order->pickupAddress);
        $location = $assignment->latestLocation;
        $profile = $assignment->courier?->courierProfile;
        $latitude = $location?->latitude ?? $profile?->current_latitude;
        $longitude = $location?->longitude ?? $profile?->current_longitude;
        $courierStartCoordinates = $latitude !== null && $longitude !== null
            ? [(float) $longitude, (float) $latitude]
            : null;
        $trackingRoute = $this->resolveTrackingRoute($assignment, $latitude, $longitude, $targetAddress?->latitude, $targetAddress?->longitude);

        return view('courier.tasks.show', compact('assignment', 'targetAddress', 'trackingRoute', 'courierStartCoordinates'));
    }

    public function route(CourierAssignment $assignment): JsonResponse
    {
        $this->authorizeCourier($assignment);
        $assignment->load(['order.pickupAddress', 'order.deliveryAddress', 'latestLocation', 'courier.courierProfile']);

        $targetAddress = $assignment->type === AssignmentType::PICKUP
            ? $assignment->order->pickupAddress
            : ($assignment->order->deliveryAddress ?? $assignment->order->pickupAddress);
        $location = $assignment->latestLocation;
        $profile = $assignment->courier?->courierProfile;
        $latitude = $location?->latitude ?? $profile?->current_latitude;
        $longitude = $location?->longitude ?? $profile?->current_longitude;
        $route = $this->resolveTrackingRoute($assignment, $latitude, $longitude, $targetAddress?->latitude, $targetAddress?->longitude);

        return response()->json(['success' => true, 'route' => $route]);
    }

    public function start(CourierAssignment $assignment): RedirectResponse
    {
        $this->authorizeCourier($assignment);

        try {
            $this->assignmentService->startTrip($assignment, auth()->user());

            return back()->with('success', 'Perjalanan dimulai! Transmisi GPS otomatis aktif.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(CourierAssignment $assignment): RedirectResponse
    {
        $this->authorizeCourier($assignment);

        try {
            if ($assignment->type === AssignmentType::PICKUP) {
                $this->assignmentService->completePickup($assignment, auth()->user());
                $msg = 'Penjemputan pakaian berhasil diselesaikan! Silakan bawa cucian ke workshop laundry.';
            } else {
                $this->assignmentService->completeDelivery($assignment, auth()->user());
                $msg = 'Pengantaran pakaian ke pelanggan berhasil diselesaikan!';
            }

            return redirect()->route('courier.dashboard')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function history(): View
    {
        $tasks = CourierAssignment::where('courier_id', auth()->id())
            ->where('status', AssignmentStatus::COMPLETED)
            ->with(['order.customer', 'order.pickupAddress', 'order.deliveryAddress'])
            ->latest('completed_at')
            ->paginate(15);

        return view('courier.tasks.history', compact('tasks'));
    }

    private function authorizeCourier(CourierAssignment $assignment): void
    {
        if ($assignment->courier_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk tugas ini.');
        }
    }

    private function resolveTrackingRoute(
        CourierAssignment $assignment,
        mixed $latitude,
        mixed $longitude,
        mixed $targetLatitude,
        mixed $targetLongitude
    ): ?array {
        $cacheKey = "courier-assignment-route:{$assignment->id}";

        if ($latitude === null || $longitude === null || $targetLatitude === null || $targetLongitude === null) {
            $cachedRoute = Cache::get($cacheKey);

            return is_array($cachedRoute) ? $cachedRoute : null;
        }

        $route = $this->roadRoute->route(
            (float) $latitude,
            (float) $longitude,
            (float) $targetLatitude,
            (float) $targetLongitude
        );

        if ($route !== null) {
            Cache::put($cacheKey, $route, now()->addDay());

            return $route;
        }

        $cachedRoute = Cache::get($cacheKey);

        return is_array($cachedRoute) ? $cachedRoute : null;
    }
}
