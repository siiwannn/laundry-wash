<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_pricing_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'laundry_price_per_kg' => 9000,
            'pickup_fee' => 6000,
            'delivery_fee' => 7000,
        ])->assertRedirect();

        $setting = Setting::firstOrFail();
        $this->assertSame('9000.00', $setting->laundry_price_per_kg);
        $this->assertSame($admin->id, $setting->updated_by);
    }

    public function test_customer_cannot_access_admin_settings(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->actingAs($customer)->get(route('admin.settings.edit'))->assertForbidden();
    }
}
