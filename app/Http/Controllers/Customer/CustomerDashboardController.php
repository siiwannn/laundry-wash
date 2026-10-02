<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $customer = auth()->user();

        // Active orders that are in progress
        $activeOrders = Order::forCustomer($customer->id)
            ->with(['items.service', 'pickupAddress', 'activeAssignment.courier'])
            ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
            ->latest()
            ->get();

        // Completed history preview
        $recentCompleted = Order::forCustomer($customer->id)
            ->with(['items.service'])
            ->where('status', OrderStatus::COMPLETED)
            ->latest()
            ->take(5)
            ->get();

        $services = Service::where('is_active', true)->get();

        return view('customer.dashboard', compact('customer', 'activeOrders', 'recentCompleted', 'services'));
    }
}
