<?php

namespace Tests\Feature;

use App\Enums\CourierStatus;
use App\Enums\UserRole;
use App\Models\CourierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_only_their_own_profile_and_password(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'name' => 'Admin Laundry',
            'email' => 'admin@example.test',
            'phone' => '081200000001',
        ]);
        $otherAdmin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get(route('admin.profile.show'))
            ->assertOk()
            ->assertViewIs('account.profile.show')
            ->assertSee('Admin Laundry')
            ->assertSee('AL', false)
            ->assertSee('admin@example.test')
            ->assertSee('081200000001')
            ->assertSee(route('admin.profile.edit'))
            ->assertDontSee('name="current_password"', false);

        $this->actingAs($admin)->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertViewIs('account.profile.edit')
            ->assertSee('name="email"', false)
            ->assertSee('name="current_password"', false)
            ->assertDontSee('name="vehicle_plate"', false);

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.test',
            'phone' => '081299999991',
        ])->assertRedirect(route('admin.profile.show'));

        $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('admin.profile.show'));

        $this->assertSame('Admin Baru', $admin->fresh()->name);
        $this->assertSame('admin-baru@example.test', $admin->fresh()->email);
        $this->assertTrue(Hash::check('password-baru', $admin->fresh()->password));
        $this->assertSame($otherAdmin->name, $otherAdmin->fresh()->name);
    }

    public function test_courier_can_view_and_update_their_account_and_vehicle_without_changing_availability(): void
    {
        $courier = User::factory()->create([
            'role' => UserRole::COURIER,
            'name' => 'Kurir Laundry',
            'email' => 'kurir@example.test',
            'phone' => '081200000002',
        ]);
        $profile = CourierProfile::create([
            'user_id' => $courier->id,
            'vehicle_type' => 'Honda Vario',
            'vehicle_plate' => 'B 1234 ABC',
            'status' => CourierStatus::BUSY,
        ]);

        $this->actingAs($courier)->get(route('courier.profile.show'))
            ->assertOk()
            ->assertViewIs('account.profile.show')
            ->assertSee('Kurir Laundry')
            ->assertSee('KL', false)
            ->assertSee('Honda Vario')
            ->assertSee('B 1234 ABC')
            ->assertSee('Sibuk')
            ->assertSee(route('courier.profile.edit'));

        $this->actingAs($courier)->get(route('courier.profile.edit'))
            ->assertOk()
            ->assertViewIs('account.profile.edit')
            ->assertSee('name="vehicle_type"', false)
            ->assertSee('name="vehicle_plate"', false)
            ->assertSee('name="current_password"', false);

        $this->actingAs($courier)->put(route('courier.profile.update'), [
            'name' => 'Kurir Diperbarui',
            'email' => 'kurir-baru@example.test',
            'phone' => '081299999992',
            'vehicle_type' => 'Yamaha NMAX',
            'vehicle_plate' => 'B 5678 DEF',
        ])->assertRedirect(route('courier.profile.show'));

        $this->assertSame('Kurir Diperbarui', $courier->fresh()->name);
        $this->assertSame('Yamaha NMAX', $profile->fresh()->vehicle_type);
        $this->assertSame('B 5678 DEF', $profile->fresh()->vehicle_plate);
        $this->assertSame(CourierStatus::BUSY, $profile->fresh()->status);
    }

    public function test_profile_routes_are_limited_to_the_matching_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $courier = User::factory()->create(['role' => UserRole::COURIER]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($admin)->get(route('courier.profile.show'))->assertForbidden();
        $this->actingAs($courier)->get(route('admin.profile.show'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.profile.show'))->assertForbidden();
        $this->actingAs($customer)->get(route('courier.profile.show'))->assertForbidden();
    }

    public function test_editing_a_courier_without_a_profile_does_not_make_them_available_for_assignment(): void
    {
        $courier = User::factory()->create(['role' => UserRole::COURIER]);

        $this->actingAs($courier)->put(route('courier.profile.update'), [
            'name' => $courier->name,
            'email' => $courier->email,
            'phone' => '081200000005',
            'vehicle_type' => 'Motor',
            'vehicle_plate' => 'B 1234 ABC',
        ])->assertRedirect(route('courier.profile.show'));

        $this->assertDatabaseHas('courier_profiles', [
            'user_id' => $courier->id,
            'status' => CourierStatus::OFFLINE->value,
        ]);
    }

    public function test_courier_profile_requires_vehicle_details_and_does_not_allow_admin_vehicle_fields(): void
    {
        $courier = User::factory()->create(['role' => UserRole::COURIER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($courier)->put(route('courier.profile.update'), [
            'name' => $courier->name,
            'email' => $courier->email,
            'phone' => '081200000003',
        ])->assertSessionHasErrors(['vehicle_type', 'vehicle_plate']);

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => '081200000004',
            'vehicle_type' => 'Motor',
            'vehicle_plate' => 'B 0000 XX',
        ])->assertSessionHasErrors(['vehicle_type', 'vehicle_plate']);
    }
}
