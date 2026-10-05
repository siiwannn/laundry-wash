<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\MidtransNotificationRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class MidtransWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function __invoke(MidtransNotificationRequest $request): JsonResponse
    {
        $this->payments->handleMidtransNotification($request->validated());

        return response()->json(['success' => true]);
    }
}
