<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssignmentType;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AssignCourierRequest;
use App\Http\Requests\Order\UpdateStageRequest;
use App\Http\Requests\Order\UpdateWeightRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CourierAssignmentService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CourierAssignmentService $assignmentService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'items.service', 'activeAssignment.courier'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        $statuses = OrderStatus::cases();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'customer',
            'pickupAddress',
            'deliveryAddress',
            'items.service',
            'assignments.courier.courierProfile',
            'assignments.latestLocation',
            'statusHistories.user',
            'payments',
        ]);

        $couriers = User::where('role', UserRole::COURIER)
            ->where('is_active', true)
            ->with('courierProfile')
            ->get();

        $statuses = OrderStatus::cases();

        return view('admin.orders.show', compact('order', 'couriers', 'statuses'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        try {
            $this->orderService->confirmOrder($order, auth()->user());
            return back()->with('success', "Pesanan {$order->order_number} berhasil dikonfirmasi.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateWeight(UpdateWeightRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->orderService->recordWeight(
                $order,
                (float) $request->validated('actual_weight'),
                $request->validated('additional_fee') !== null ? (float) $request->validated('additional_fee') : null,
                auth()->user()
            );

            return back()->with('success', 'Berat aktual dan rincian harga berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function assignCourier(AssignCourierRequest $request, Order $order): RedirectResponse
    {
        try {
            $type = AssignmentType::from($request->validated('type'));
            $this->assignmentService->assignCourier(
                $order,
                (int) $request->validated('courier_id'),
                $type,
                auth()->user()
            );

            return back()->with('success', 'Kurir berhasil ditugaskan untuk pesanan ini.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receiveAtLaundry(Order $order): RedirectResponse
    {
        try {
            $this->assignmentService->receiveAtLaundry($order, auth()->user());
            return back()->with('success', 'Status pesanan diubah: Pakaian telah diterima di workshop laundry.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStage(UpdateStageRequest $request, Order $order): RedirectResponse
    {
        try {
            $newStage = OrderStatus::from($request->validated('status'));
            $this->orderService->updateLaundryStage(
                $order,
                $newStage,
                auth()->user(),
                $request->validated('note')
            );

            return back()->with('success', "Tahapan laundry berhasil diperbarui ke: {$newStage->label()}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmPayment(Payment $payment): RedirectResponse
    {
        try {
            $this->paymentService->confirmPayment($payment, auth()->user());
            return back()->with('success', 'Pembayaran sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' berhasil dikonfirmasi LUNAS.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
