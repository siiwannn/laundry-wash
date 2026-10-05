<?php

namespace Tests\Feature;

use App\Enums\AssignmentType;
use App\Enums\CourierStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CourierProfile;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\CourierAssignmentService;
use App\Services\OrderService;
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
            'service_type' => 'self_drop_off',
            'service_id' => $service->id,
            'estimated_weight' => 3,
            'pickup_address_id' => $otherAddress->id,
        ]);

        $response->assertSessionHasErrors('pickup_address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_cannot_create_drop_off_or_self_pickup_order(): void
    {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $address = $this->createAddress($customer);
        $service = $this->createService();

        $response = $this->actingAs($customer)->post(route('customer.orders.store'), [
            'service_type' => 'self_drop_off',
            'delivery_method' => 'self_pickup',
            'service_id' => $service->id,
            'estimated_weight' => 3,
            'pickup_address_id' => $address->id,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['service_type', 'delivery_method']);
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
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal' => 40000,
            'delivery_fee' => 10000,
            'additional_fee' => 0,
            'total' => 50000,
        ]);
    }
}
