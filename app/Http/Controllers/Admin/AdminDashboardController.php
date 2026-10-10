<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminDashboardRangeRequest;
use App\Models\Order;
use App\Services\ReportService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(AdminDashboardRangeRequest $request): View
    {
        $stats = $this->reportService->getSummaryStats();
        $revenueChart = $this->reportService->getDashboardRevenue();
        $periodLabel = match ($request->validated('range', 'today')) {
            'month' => '30 hari terakhir',
            'week' => '7 hari terakhir',
            default => 'Hari ini',
        };
        $dailyVolume = $this->reportService->getDailyOrderVolume($request->validated('range') === 'month' ? 30 : 7);

        // Recent orders
        $recentOrders = Order::with(['customer', 'serviceItem', 'items.service'])
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
            'readyForDelivery',
            'periodLabel',
            'dailyVolume',
            'revenueChart'
        ));
    }
}
