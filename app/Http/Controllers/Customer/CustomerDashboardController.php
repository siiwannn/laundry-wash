<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $customer = auth()->user();

        // Active orders that are in progress
        $activeOrders = Order::forCustomer($customer->id)
            ->with(['serviceItem.service', 'items.service', 'pickupAddress', 'activeAssignment.courier'])
            ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
            ->latest()
            ->get();

        // Completed history preview
        $recentCompleted = Order::forCustomer($customer->id)
            ->with(['serviceItem.service', 'items.service'])
            ->where('status', OrderStatus::COMPLETED)
            ->latest()
            ->take(5)
            ->get();

        $services = Service::where('is_active', true)->get();
        $weatherLocation = $customer->defaultAddress;

        return view('customer.dashboard', compact('customer', 'activeOrders', 'recentCompleted', 'services', 'weatherLocation'));
    }

    public function weather(WeatherService $weatherService): JsonResponse
    {
        $address = auth()->user()->defaultAddress;

        if (! $address || $address->latitude === null || $address->longitude === null) {
            return response()->json(['message' => 'Tambahkan titik lokasi pada alamat utama untuk melihat cuaca.'], 422);
        }

        $weather = $weatherService->current($address->latitude, $address->longitude);

        if ($weather === null) {
            return response()->json(['message' => 'Cuaca belum dapat dimuat. Coba lagi sebentar.'], 503);
        }

        return response()->json($weather);
    }
}
