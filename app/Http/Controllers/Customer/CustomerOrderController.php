<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Order;
use App\Models\Service;
use App\Services\CourierTrackingService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CourierTrackingService $trackingService,
        protected PaymentService $paymentService
    ) {}

    public function create(): View
    {
        $services = Service::where('is_active', true)->get();
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('customer.orders.create', compact('services', 'addresses'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->createOrder(auth()->user(), $request->validated());

            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Pesanan laundry berhasil dibuat! Menunggu verifikasi admin.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat pesanan: '.$e->getMessage());
        }
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load([
            'items.service',
            'pickupAddress',
            'deliveryAddress',
            'statusHistories.user',
            'activeAssignment.courier.courierProfile',
            'payments',
        ]);

        return view('customer.orders.show', compact('order'));
    }

    public function tracking(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['pickupAddress', 'deliveryAddress', 'activeAssignment.courier']);
        $trackingData = $this->trackingService->getLatestTrackingData($order);

        return view('customer.orders.tracking', compact('order', 'trackingData'));
    }

    public function history(): View
    {
        $orders = Order::forCustomer(auth()->id())
            ->with(['items.service', 'latestPayment'])
            ->latest()
            ->paginate(10);

        return view('customer.orders.history', compact('orders'));
    }

    public function pay(StorePaymentRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('pay', $order);

        try {
            $this->paymentService->submitPayment(
                $order,
                auth()->user(),
                $request->validated(),
                $request->file('proof_file')
            );

            return back()->with('success', 'Pembayaran berhasil dikirim! Menunggu konfirmasi verifikasi admin.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            $this->orderService->cancelOrder($order, auth()->user(), $request->input('reason'));

            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
