<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_update_and_disable_customer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($admin)->get(route('admin.customers.index'))->assertOk()->assertSee($customer->email);
        $this->actingAs($admin)->put(route('admin.customers.update', $customer), [
            'name' => 'Customer Presentasi', 'email' => $customer->email, 'phone' => '081234567800',
        ])->assertRedirect(route('admin.customers.show', $customer));
        $this->actingAs($admin)->patch(route('admin.customers.toggle', $customer))->assertRedirect();

        $this->assertFalse($customer->fresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id]);
    }

    public function test_customer_cannot_access_customer_management(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->actingAs($customer)->get(route('admin.customers.index'))->assertForbidden();
    }
}
