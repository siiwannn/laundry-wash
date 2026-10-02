<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courier\UpdateLocationRequest;
use App\Models\Order;
use App\Services\CourierTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TrackingApiController extends Controller
{
    public function __construct(
        protected CourierTrackingService $trackingService
    ) {}

    /**
     * Endpoint for courier browser watchPosition to send updates every 10 seconds.
     */
    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        try {
            $location = $this->trackingService->recordLocation(
                $request->user(),
                (int) $request->validated('assignment_id'),
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
                $request->validated('accuracy') !== null
                    ? (float) $request->validated('accuracy')
                    : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Koordinat lokasi berhasil diperbarui.',
                'recorded_at' => $location->recorded_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Polling endpoint called by Customer / Admin every 10 seconds to retrieve latest courier position.
     */
    public function getOrderTracking(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $data = $this->trackingService->getLatestTrackingData($order);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
