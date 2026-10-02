<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CustomerAddress;
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
        ])->assertRedirect();

        $this->actingAs($customer)->put(route('customer.profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect();

        $this->assertSame('Nama Diperbarui', $customer->fresh()->name);
        $this->assertTrue(Hash::check('password-baru', $customer->fresh()->password));
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
}
