<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        $stats = $this->reportService->getSummaryStats();

        // Recent orders
        $recentOrders = Order::with(['customer', 'items.service'])
            ->latest()
            ->take(8)
            ->get();

        // Orders needing action: pending verification or ready for courier assignment
        $pendingOrders = Order::where('status', OrderStatus::PENDING)->latest()->get();
        $readyForPickup = Order::where('status', OrderStatus::CONFIRMED)->latest()->get();
        $readyForDelivery = Order::where('status', OrderStatus::PAID)->latest()->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'pendingOrders',
            'readyForPickup',
            'readyForDelivery'
        ));
    }
}
