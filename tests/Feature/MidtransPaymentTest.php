<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\MidtransPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
    }

    public function test_customer_can_create_snap_payment_for_own_ready_order(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $order = $this->createOrder($customer, OrderStatus::READY);
        $gateway = Mockery::mock(MidtransPaymentService::class);
        $gateway->shouldReceive('createSnapToken')->once()->andReturn('snap-test-token');
        $this->app->instance(MidtransPaymentService::class, $gateway);

        $this->actingAs($customer)->postJson(route('customer.orders.pay', $order))
            ->assertOk()
            ->assertJsonPath('snap_token', 'snap-test-token');

        $payment = Payment::firstOrFail();
        $this->assertSame(PaymentStatus::PENDING, $payment->status);
        $this->assertSame(OrderStatus::WAITING_PAYMENT, $order->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['activity' => "Payment Created: {$payment->gateway_order_id}"]);
        $this->assertDatabaseHas('activity_logs', ['activity' => "Payment Pending: {$payment->gateway_order_id}"]);
    }

    public function test_customer_can_reopen_existing_pending_snap_payment(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $order = $this->createOrder($customer, OrderStatus::READY);
        $gateway = Mockery::mock(MidtransPaymentService::class);
        $gateway->shouldReceive('createSnapToken')->once()->andReturn('snap-reusable-token');
        $this->app->instance(MidtransPaymentService::class, $gateway);

        $firstResponse = $this->actingAs($customer)->postJson(route('customer.orders.pay', $order));
        $secondResponse = $this->actingAs($customer)->postJson(route('customer.orders.pay', $order));

        $firstResponse->assertOk()->assertJsonPath('snap_token', 'snap-reusable-token');
        $secondResponse->assertOk()->assertJsonPath('snap_token', 'snap-reusable-token');
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(OrderStatus::WAITING_PAYMENT, $order->fresh()->status);
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
    }

    public function test_customer_can_refresh_snap_token_after_closing_checkout(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $order = $this->createOrder($customer, OrderStatus::READY);
        $gateway = Mockery::mock(MidtransPaymentService::class);
        $gateway->shouldReceive('createSnapToken')->twice()->andReturn('snap-first-token', 'snap-refreshed-token');
        $this->app->instance(MidtransPaymentService::class, $gateway);

        $this->actingAs($customer)->postJson(route('customer.orders.pay', $order))
            ->assertOk()
            ->assertJsonPath('snap_token', 'snap-first-token');

        $this->actingAs($customer)->postJson(route('customer.orders.pay', $order), ['refresh_token' => true])
            ->assertOk()
            ->assertJsonPath('snap_token', 'snap-refreshed-token');

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('snap-refreshed-token', Payment::firstOrFail()->snap_token);
        $this->assertSame(PaymentStatus::PENDING, Payment::firstOrFail()->status);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payment = $this->createPendingPayment();

        $this->postJson(route('midtrans.notification'), $this->notification($payment, 'settlement', 'invalid'))
            ->assertForbidden();

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
    }

    public function test_settlement_webhook_marks_payment_and_order_paid_idempotently(): void
    {
        $payment = $this->createPendingPayment();
        $payload = $this->signedNotification($payment, 'settlement', 'qris');

        $this->postJson(route('midtrans.notification'), $payload)->assertOk();
        $this->postJson(route('midtrans.notification'), $payload)->assertOk();

        $this->assertSame(PaymentStatus::PAID, $payment->fresh()->status);
        $this->assertSame(OrderStatus::PAID, $payment->order->fresh()->status);
        $this->assertDatabaseCount('order_status_histories', 1);
        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertDatabaseHas('activity_logs', ['activity' => "Payment Paid: {$payment->gateway_order_id}"]);
    }

    public function test_expired_webhook_returns_order_to_ready_and_logs_expiration(): void
    {
        $payment = $this->createPendingPayment();

        $this->postJson(route('midtrans.notification'), $this->signedNotification($payment, 'expire', 'bank_transfer'))->assertOk();

        $this->assertSame(PaymentStatus::FAILED, $payment->fresh()->status);
        $this->assertSame(OrderStatus::READY, $payment->order->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['activity' => "Payment Expired: {$payment->gateway_order_id}"]);
    }

    public function test_failed_webhook_returns_order_to_ready_and_logs_failure(): void
    {
        $payment = $this->createPendingPayment();

        $this->postJson(route('midtrans.notification'), $this->signedNotification($payment, 'failure', 'bank_transfer'))
            ->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatus::FAILED, $payment->status);
        $this->assertSame(PaymentMethod::VIRTUAL_ACCOUNT, $payment->method);
        $this->assertSame(OrderStatus::READY, $payment->order->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['activity' => "Payment Failed: {$payment->gateway_order_id}"]);
    }

    public function test_manual_admin_verification_route_no_longer_exists(): void
    {
        $this->assertFalse(Route::has('admin.payments.verify'));
        $this->assertFalse(Route::has('admin.payments.confirm'));
    }

    private function createPendingPayment(): Payment
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $order = $this->createOrder($customer, OrderStatus::WAITING_PAYMENT);

        return Payment::create([
            'order_id' => $order->id,
            'gateway_order_id' => 'LW-TEST-'.$order->id,
            'method' => PaymentMethod::QRIS,
            'amount' => $order->total,
            'status' => PaymentStatus::PENDING,
            'gateway_status' => 'pending',
            'expires_at' => now()->addDay(),
        ]);
    }

    private function createOrder(User $customer, OrderStatus $status): Order
    {
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Jalan Midtrans Sandbox',
            'is_default' => true,
        ]);

        return Order::create([
            'order_number' => 'ORD-MID-'.$customer->id,
            'customer_id' => $customer->id,
            'pickup_address_id' => $address->id,
            'delivery_address_id' => $address->id,
            'status' => $status,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => 40000,
            'pickup_fee' => 5000,
            'delivery_fee' => 5000,
            'total' => 50000,
        ]);
    }

    private function signedNotification(Payment $payment, string $status, string $type): array
    {
        $payload = $this->notification($payment, $status, '');
        $payload['payment_type'] = $type;
        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'sandbox-server-key'
        );

        return $payload;
    }

    private function notification(Payment $payment, string $status, string $signature): array
    {
        return [
            'order_id' => $payment->gateway_order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => $signature,
            'transaction_status' => $status,
            'transaction_id' => 'midtrans-transaction-'.$payment->id,
            'payment_type' => 'qris',
        ];
    }
}
