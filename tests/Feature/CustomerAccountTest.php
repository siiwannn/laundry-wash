<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_own_profile_and_password(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)->put(route('customer.profile.update'), [
            'name' => 'Nama Diperbarui',
            'email' => 'updated@example.test',
            'phone' => '081299999999',
        ])->assertRedirect(route('customer.profile.show'));

        $this->actingAs($customer)->put(route('customer.profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('customer.profile.show'));

        $this->assertSame('Nama Diperbarui', $customer->fresh()->name);
        $this->assertTrue(Hash::check('password-baru', $customer->fresh()->password));
    }

    public function test_customer_profile_page_displays_real_account_details_and_links_to_edit_form(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
            'phone' => '081234567890',
        ]);

        $this->actingAs($customer)->get(route('customer.profile.show'))
            ->assertOk()
            ->assertViewIs('customer.profile.show')
            ->assertSee('Siti Rahmawati')
            ->assertSee('siti@example.test')
            ->assertSee('081234567890')
            ->assertSee(route('customer.profile.edit'))
            ->assertDontSee('name="current_password"', false)
            ->assertDontSee('name="email"', false);
    }

    public function test_customer_can_open_profile_edit_form_separately(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertViewIs('customer.profile.edit')
            ->assertSee('name="email"', false)
            ->assertSee('name="current_password"', false);
    }

    public function test_customer_can_store_recipient_details_on_address(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)->post(route('customer.addresses.store'), [
            'label' => 'Rumah',
            'recipient_name' => 'Penerima Demo',
            'recipient_phone' => '081200000000',
            'address' => 'Jalan Presentasi Nomor 10',
        ])->assertRedirect(route('customer.addresses.index'));

        $address = CustomerAddress::firstOrFail();
        $this->assertSame('Penerima Demo', $address->recipient_name);
        $this->assertTrue($address->is_default);
    }

    public function test_customer_cannot_edit_another_customers_address(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $otherCustomer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $otherCustomer->id,
            'label' => 'Rumah',
            'address' => 'Alamat orang lain',
        ]);

        $this->actingAs($customer)->get(route('customer.addresses.edit', $address))->assertForbidden();
    }

    public function test_address_and_pin_changes_sync_to_orders_that_reference_the_address(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Nama Lama',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Lama No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);
        $order = $this->createOrderUsingAddress($customer, $address);

        $this->actingAs($customer)->put(route('customer.addresses.update', $address), [
            'label' => 'Rumah',
            'recipient_name' => 'Nama Baru',
            'recipient_phone' => '081299999999',
            'address' => 'Alamat Baru No. 2',
            'latitude' => '-6.3',
            'longitude' => '106.9',
            'location_confirmed' => '1',
            'is_default' => '1',
        ])->assertRedirect(route('customer.addresses.index'));

        $updatedOrderAddress = $order->fresh()->pickupAddress;
        $this->assertSame('Alamat Baru No. 2', $updatedOrderAddress->address);
        $this->assertSame('Nama Baru', $updatedOrderAddress->recipient_name);
        $this->assertSame('081299999999', $updatedOrderAddress->recipient_phone);
        $this->assertSame(-6.3, $updatedOrderAddress->latitude);
        $this->assertSame(106.9, $updatedOrderAddress->longitude);
    }

    public function test_address_text_cannot_change_without_confirming_its_map_pin(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Lama No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);

        $this->actingAs($customer)->put(route('customer.addresses.update', $address), [
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Baru No. 2',
            'latitude' => '-6.2',
            'longitude' => '106.8',
            'location_confirmed' => '0',
            'is_default' => '1',
        ])->assertSessionHasErrors('address');

        $this->assertSame('Alamat Lama No. 1', $address->fresh()->address);
    }

    public function test_address_pin_cannot_change_without_a_synced_address(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Lama No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);

        $this->actingAs($customer)->put(route('customer.addresses.update', $address), [
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Lama No. 1',
            'latitude' => '-6.3',
            'longitude' => '106.9',
            'location_confirmed' => '0',
            'is_default' => '1',
        ])->assertSessionHasErrors('address');

        $this->assertSame(-6.2, $address->fresh()->latitude);
        $this->assertSame(106.8, $address->fresh()->longitude);
    }

    public function test_edit_address_page_loads_the_configured_photon_picker(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Jl. Mawar No. 5, Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.addresses.edit', $address))
            ->assertOk()
            ->assertSee(route('customer.addresses.geocode', [], false), false)
            ->assertSee(route('customer.addresses.reverse-geocode', [], false), false)
            ->assertSee('customer-address-picker.js', false)
            ->assertSee('Alamat dan koordinat dikirim ke layanan publik tersebut.', false);
    }

    public function test_customer_address_list_uses_clear_location_action_label(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Jl. Mawar No. 5, Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.addresses.index'))
            ->assertOk()
            ->assertSee('Ubah Lokasi')
            ->assertDontSee('Ubah Pin');
    }

    public function test_address_used_by_an_order_cannot_be_deleted(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Kantor',
            'recipient_name' => 'Nama Penerima',
            'recipient_phone' => '081200000000',
            'address' => 'Alamat Kantor No. 3',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => false,
        ]);
        $order = $this->createOrderUsingAddress($customer, $address);

        $this->actingAs($customer)->delete(route('customer.addresses.destroy', $address))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customer_addresses', ['id' => $address->id]);
        $this->assertSame($address->id, $order->fresh()->pickup_address_id);
    }

    private function createOrderUsingAddress(User $customer, CustomerAddress $address): Order
    {
        return Order::create([
            'order_number' => 'ORD-ADDRESS-'.$customer->id.'-'.$address->id,
            'customer_id' => $customer->id,
            'pickup_address_id' => $address->id,
            'delivery_address_id' => $address->id,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => 0,
            'total' => 0,
        ]);
    }
}
