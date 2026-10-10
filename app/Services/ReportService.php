<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    public function getDashboardRevenue(): array
    {
        $start = today()->subDays(6);
        $payments = Payment::where('status', PaymentStatus::PAID)
            ->whereBetween('paid_at', [$start, now()])->get(['paid_at', 'amount']);
        $grouped = $payments->groupBy(fn ($payment) => Carbon::parse($payment->paid_at)->toDateString());
        $days = collect(range(0, 6))->map(function ($offset) use ($start, $grouped) {
            $date = $start->copy()->addDays($offset);

            return ['label' => $date->format('j M'), 'amount' => (float) ($grouped->get($date->toDateString())?->sum('amount') ?? 0)];
        });

        return ['days' => $days->all(), 'max' => max(1, $days->max('amount')), 'total' => $days->sum('amount')];
    }

    public function getDailyOrderVolume(int $days = 7): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $counts = Order::where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->toDateString())
            ->map->count();

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $counts): array {
            $date = $start->copy()->addDays($offset);
            $count = (int) ($counts[$date->toDateString()] ?? 0);

            return ['label' => $date->format('d M'), 'count' => $count];
        })->all();
    }

    public function getSummaryStats(): array
    {
        $totalRevenue = (float) Order::where('payment_status', PaymentStatus::PAID)->sum('total');
        $totalOrders = Order::count();
        $activeOrders = Order::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->count();
        $totalCustomers = User::where('role', UserRole::CUSTOMER)->count();
        $totalCouriers = User::where('role', UserRole::COURIER)->count();

        // Orders breakdown by status
        $statusCounts = Order::pluck('status')->map(fn (OrderStatus $status) => $status->value)->countBy()->all();

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

        $orders = Order::with(['customer', 'serviceItem.service'])
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
