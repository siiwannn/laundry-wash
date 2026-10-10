<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CustomerAddress;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_custom_service_from_the_services_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Tambah Layanan');

        $this->actingAs($admin)->post(route('admin.services.store'), [
            'name' => 'Laundry Khusus',
            'description' => 'Layanan sesuai kebutuhan pelanggan.',
            'unit' => 'pcs',
            'price_per_unit' => 18000,
            'estimated_hours' => 24,
        ])->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'name' => 'Laundry Khusus',
            'unit' => 'pcs',
            'price_per_unit' => 18000,
            'is_active' => true,
            'deleted_at' => null,
        ]);
    }

    public function test_delete_hides_service_but_preserves_existing_order_snapshot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Alamat uji layanan',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);
        $service = Service::create([
            'name' => 'Laundry Khusus',
            'unit' => 'pcs',
            'price_per_unit' => 18000,
            'estimated_hours' => 24,
            'is_active' => true,
        ]);
        $order = app(OrderService::class)->createOrder($customer, [
            'service_id' => $service->id,
            'estimated_quantity' => 2,
            'pickup_address_id' => $address->id,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '10:00',
        ]);

        $this->actingAs($admin)->delete(route('admin.services.destroy', $service))
            ->assertRedirect();

        $this->assertNotNull($service->fresh()->deleted_at);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'service_id' => $service->id,
            'service_name_snapshot' => 'Laundry Khusus',
            'unit_price' => 18000,
        ]);
        $this->assertNull(OrderItem::query()->where('order_id', $order->id)->firstOrFail()->service);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Laundry Khusus');

        $this->actingAs($admin)->get(route('admin.services.index'))
            ->assertOk()
            ->assertDontSee('Laundry Khusus')
            ->assertDontSee('Nonaktifkan');
    }
}
