<?php

namespace Tests\Feature;

use App\Enums\AssignmentType;
use App\Enums\CourierStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\CourierProfile;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\CourierAssignmentService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_create_order_with_another_customers_address(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $otherCustomer = $this->createUser(UserRole::CUSTOMER);
        $otherAddress = $this->createAddress($otherCustomer);
        $service = $this->createService();

        $response = $this->actingAs($customer)->post(route('customer.orders.store'), [
            'service_type' => ServiceType::PICKUP_AND_DELIVERY->value,
            'service_id' => $service->id,
            'estimated_weight' => 3,
            'pickup_address_id' => $otherAddress->id,
        ]);

        $response->assertSessionHasErrors('pickup_address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_laundry_stage_cannot_skip_the_required_sequence(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $order = $this->createOrder($customer, OrderStatus::RECEIVED_AT_LAUNDRY);

        $this->expectExceptionMessage(
            'Transisi status tidak valid. Tahap berikutnya dari Diterima di Laundry adalah Sedang Dicuci.'
        );

        app(OrderService::class)->updateLaundryStage($order, OrderStatus::READY, $admin);
    }

    public function test_payment_moves_order_from_ready_to_waiting_payment_then_paid(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $order = $this->createOrder($customer, OrderStatus::READY);
        $paymentService = app(PaymentService::class);

        $payment = $paymentService->submitPayment($order, $customer, [
            'method' => PaymentMethod::TRANSFER->value,
            'reference' => 'TEST-TRANSFER-001',
        ]);

        $this->assertSame(OrderStatus::WAITING_PAYMENT, $order->refresh()->status);
        $this->assertSame(PaymentStatus::PENDING, $payment->status);

        $paymentService->confirmPayment($payment, $admin);

        $this->assertSame(OrderStatus::PAID, $order->refresh()->status);
        $this->assertSame(PaymentStatus::PAID, $order->payment_status);
        $this->assertSame(PaymentStatus::PAID, $payment->refresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::PAID->value,
            'changed_by' => $admin->id,
        ]);
    }

    public function test_delivery_assignment_is_rejected_until_payment_is_paid(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $courier = $this->createUser(UserRole::COURIER);
        CourierProfile::create([
            'user_id' => $courier->id,
            'status' => CourierStatus::AVAILABLE,
        ]);
        $order = $this->createOrder($customer, OrderStatus::READY);

        $this->expectExceptionMessage(
            'Kurir delivery hanya dapat ditugaskan setelah pembayaran dikonfirmasi lunas.'
        );

        app(CourierAssignmentService::class)->assignCourier(
            $order,
            $courier->id,
            AssignmentType::DELIVERY,
            $admin
        );
    }

    public function test_admin_can_reject_payment_and_customer_can_resubmit(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $order = $this->createOrder($customer, OrderStatus::READY);
        $service = app(PaymentService::class);
        $payment = $service->submitPayment($order, $customer, ['method' => PaymentMethod::QRIS->value]);

        $this->actingAs($admin)->patch(route('admin.payments.verify', $payment), ['status' => 'failed'])->assertRedirect();

        $this->assertSame(PaymentStatus::FAILED, $payment->refresh()->status);
        $this->assertSame(OrderStatus::READY, $order->refresh()->status);
        $this->assertTrue($order->canAcceptPayment());
    }

    public function test_admin_can_complete_paid_self_pickup_order(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $order = $this->createOrder($customer, OrderStatus::PAID, PaymentStatus::PAID);
        $order->update(['delivery_method' => 'self_pickup']);

        app(OrderService::class)->completeSelfPickup($order, $admin);

        $this->assertSame(OrderStatus::COMPLETED, $order->refresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'completed']);
    }

    public function test_paid_order_can_receive_delivery_assignment(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $admin = $this->createUser(UserRole::ADMIN);
        $courier = $this->createUser(UserRole::COURIER);
        CourierProfile::create([
            'user_id' => $courier->id,
            'status' => CourierStatus::AVAILABLE,
        ]);
        $order = $this->createOrder($customer, OrderStatus::PAID, PaymentStatus::PAID);

        $assignment = app(CourierAssignmentService::class)->assignCourier(
            $order,
            $courier->id,
            AssignmentType::DELIVERY,
            $admin
        );

        $this->assertSame(AssignmentType::DELIVERY, $assignment->type);
        $this->assertSame(OrderStatus::DELIVERY_ASSIGNED, $order->refresh()->status);
    }

    private function createUser(UserRole $role): User
    {
        return User::factory()->create([
            'phone' => '081234567890',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createAddress(User $customer): CustomerAddress
    {
        return CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Jl. Pengujian No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Cuci Reguler',
            'price_per_kg' => 8000,
            'estimated_hours' => 48,
            'is_active' => true,
        ]);
    }

    private function createOrder(
        User $customer,
        OrderStatus $status,
        PaymentStatus $paymentStatus = PaymentStatus::PENDING
    ): Order {
        $address = $this->createAddress($customer);

        return Order::create([
            'order_number' => 'ORD-TEST-'.str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT).'-'.$status->value,
            'customer_id' => $customer->id,
            'pickup_address_id' => $address->id,
            'delivery_address_id' => $address->id,
            'service_type' => ServiceType::PICKUP_AND_DELIVERY,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal' => 40000,
            'delivery_fee' => 10000,
            'additional_fee' => 0,
            'total' => 50000,
        ]);
    }
}
