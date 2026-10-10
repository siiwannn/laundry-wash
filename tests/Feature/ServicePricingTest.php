<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServicePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_restored_catalog_contains_four_services_and_uses_piece_pricing_for_bed_cover(): void
    {
        $services = Service::query()->where('is_active', true)->orderBy('name')->get()->keyBy('name');

        $this->assertSame([
            'Bed Cover & Selimut Tebal' => ['pcs', '20000.00'],
            'Cuci Kilat Express' => ['kg', '15000.00'],
            'Cuci Komplit Reguler' => ['kg', '9000.00'],
            'Setrika Saja' => ['kg', '5000.00'],
        ], $services->map(fn (Service $service) => [$service->unit, $service->price_per_unit])->all());
        $this->assertCount(4, $services);
        $this->assertDatabaseMissing('services', ['name' => 'Setrika Saja (Lipat Rapi)', 'is_active' => true]);
        $this->assertDatabaseMissing('services', ['name' => 'Sepatu']);
        $this->assertDatabaseMissing('services', ['name' => 'Boneka']);
    }

    public function test_kg_service_estimate_and_final_amount_use_the_order_price_snapshot(): void
    {
        [$customer, $address] = $this->customerWithAddress();
        $service = $this->service('Cuci Kering', 'kg', 8000);
        $order = app(OrderService::class)->createOrder($customer, $this->orderData($service, $address, 2.5));
        $item = $order->serviceItem()->firstOrFail();

        $this->assertSame(1, $order->items()->count());
        $this->assertSame('kg', $item->unit);
        $this->assertSame('Cuci Kering', $item->service_name_snapshot);
        $this->assertSame('8000.00', $item->unit_price);
        $this->assertSame('20000.00', $item->subtotal);
        $this->assertSame('30000.00', $order->total);

        $service->update(['price_per_unit' => 12000]);
        $order->status = OrderStatus::RECEIVED_AT_LAUNDRY;
        $order->save();
        app(OrderService::class)->recordWeight($order, 3.25, null, User::factory()->create(['role' => UserRole::ADMIN]));

        $this->assertSame('8000.00', $item->fresh()->unit_price);
        $this->assertSame('26000.00', $item->fresh()->subtotal);
        $this->assertSame('36000.00', $order->fresh()->total);
        $this->assertSame('3.25', $order->fresh()->actual_weight);
    }

    public function test_piece_service_uses_integer_quantities_and_price_snapshot_for_midtrans_amount(): void
    {
        [$customer, $address] = $this->customerWithAddress();
        $service = $this->service('Bed Cover', 'pcs', 15000);
        $order = app(OrderService::class)->createOrder($customer, $this->orderData($service, $address, 2));
        $item = $order->serviceItem()->firstOrFail();

        $this->assertNull($order->estimated_weight);
        $this->assertSame('pcs', $item->unit);
        $this->assertSame('2.00', $item->estimated_quantity);
        $this->assertSame('40000.00', $order->total);

        $service->update(['price_per_unit' => 20000]);
        $order->update(['status' => OrderStatus::RECEIVED_AT_LAUNDRY]);
        app(OrderService::class)->recordWeight($order, 3, null, User::factory()->create(['role' => UserRole::ADMIN]));
        $order->update(['status' => OrderStatus::READY]);

        $payment = app(PaymentService::class)->simulateSuccessfulPayment($order->fresh(), $customer);

        $this->assertSame('55000.00', $order->fresh()->total);
        $this->assertSame('55000.00', $payment->amount);
        $this->assertSame(PaymentStatus::PAID, $payment->status);
        $this->assertSame('3.00', $item->fresh()->actual_quantity);
        $this->assertSame('15000.00', $item->fresh()->unit_price);
    }

    public function test_customer_cannot_submit_multiple_services_and_piece_quantity_must_be_integer(): void
    {
        [$customer, $address] = $this->customerWithAddress();
        $service = $this->service('Bed Cover', 'pcs', 12000);
        $data = $this->orderData($service, $address, 1);

        $this->actingAs($customer)->post(route('customer.orders.store'), [
            ...$data,
            'service_ids' => [$service->id],
        ])->assertSessionHasErrors('service_ids');

        $data['estimated_quantity'] = 1.5;
        $this->actingAs($customer)->post(route('customer.orders.store'), $data)
            ->assertSessionHasErrors('estimated_quantity');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_actual_quantity_endpoint_uses_the_order_unit(): void
    {
        [$customer, $address] = $this->customerWithAddress();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $service = $this->service('Bed Cover', 'pcs', 12000);
        $order = app(OrderService::class)->createOrder($customer, $this->orderData($service, $address, 2));
        $service->update(['price_per_unit' => 15000]);
        $order->update(['status' => OrderStatus::RECEIVED_AT_LAUNDRY]);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('name="actual_quantity"', false)
            ->assertSee('value="2"', false);

        $this->actingAs($admin)->patch(route('admin.orders.weight', $order), [
            'actual_quantity' => 2.5,
        ])->assertSessionHasErrors('actual_quantity');

        $this->actingAs($admin)->patch(route('admin.orders.weight', $order), [
            'actual_quantity' => '2.00',
        ])->assertRedirect();

        $this->actingAs($admin)->patch(route('admin.orders.weight', $order), [
            'actual_quantity' => 3,
        ])->assertRedirect();

        $this->assertSame('46000.00', $order->fresh()->total);
        $this->assertSame('3.00', $order->serviceItem()->firstOrFail()->actual_quantity);
    }

    public function test_order_shipping_is_one_flat_fee_independent_of_legacy_settings(): void
    {
        DB::table('settings')->insert([
            'laundry_price_per_kg' => 99000,
            'pickup_fee' => 500,
            'delivery_fee' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        [$customer, $address] = $this->customerWithAddress();
        $service = $this->service('Cuci Kering', 'kg', 6000);

        $order = app(OrderService::class)->createOrder($customer, $this->orderData($service, $address, 2));

        $this->assertSame('10000.00', $order->shipping_fee);
        $this->assertSame('0.00', $order->pickup_fee);
        $this->assertSame('0.00', $order->delivery_fee);
        $this->assertSame('22000.00', $order->total);
    }

    private function customerWithAddress(): array
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Alamat pengujian',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);

        return [$customer, $address];
    }

    private function service(string $name, string $unit, int $price): Service
    {
        return Service::create([
            'name' => $name,
            'unit' => $unit,
            'price_per_unit' => $price,
            'estimated_hours' => 48,
            'is_active' => true,
        ]);
    }

    private function orderData(Service $service, CustomerAddress $address, float|int $quantity): array
    {
        return [
            'service_id' => $service->id,
            'estimated_quantity' => $quantity,
            'pickup_address_id' => $address->id,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '10:00',
        ];
    }
}
