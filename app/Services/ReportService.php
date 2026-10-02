<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSummaryStats(): array
    {
        $totalRevenue = (float) Order::where('payment_status', PaymentStatus::PAID)->sum('total');
        $totalOrders = Order::count();
        $activeOrders = Order::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->count();
        $totalCustomers = User::where('role', UserRole::CUSTOMER)->count();
        $totalCouriers = User::where('role', UserRole::COURIER)->count();

        // Orders breakdown by status
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'active_orders' => $activeOrders,
            'total_customers' => $totalCustomers,
            'total_couriers' => $totalCouriers,
            'status_counts' => $statusCounts,
        ];
    }

    public function getRevenueReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $orders = Order::with(['customer', 'items.service'])
            ->where('payment_status', PaymentStatus::PAID)
            ->whereBetween('updated_at', [$start, $end])
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalFilteredRevenue = (float) $orders->sum('total');

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_revenue' => $totalFilteredRevenue,
            'total_orders' => $orders->count(),
            'orders' => $orders,
        ];
    }

    public function getCourierPerformance(): array
    {
        return User::where('role', UserRole::COURIER)
            ->with(['courierProfile'])
            ->withCount([
                'assignments as completed_pickups' => function ($q) {
                    $q->where('type', 'pickup')->where('status', AssignmentStatus::COMPLETED);
                },
                'assignments as completed_deliveries' => function ($q) {
                    $q->where('type', 'delivery')->where('status', AssignmentStatus::COMPLETED);
                },
                'assignments as active_tasks' => function ($q) {
                    $q->whereIn('status', [AssignmentStatus::ASSIGNED, AssignmentStatus::ON_THE_WAY]);
                },
            ])
            ->get()
            ->toArray();
    }
}
