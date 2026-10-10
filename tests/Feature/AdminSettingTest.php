<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_settings_routes_are_disabled(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get('/admin/settings')->assertNotFound();
        $this->actingAs($admin)->put('/admin/settings', [
            'pickup_fee' => 6000,
            'delivery_fee' => 7000,
        ])->assertNotFound();
    }

    public function test_customer_cannot_access_admin_settings(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->actingAs($customer)->get('/admin/settings')->assertNotFound();
    }
}
