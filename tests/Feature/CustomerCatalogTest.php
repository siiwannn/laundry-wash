<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_active_services_with_prices_and_duration(): void
    {
        Service::create(['name' => 'Cuci Reguler', 'description' => 'Cuci dan setrika.', 'price_per_kg' => 8000, 'estimated_hours' => 48, 'is_active' => true]);
        Service::create(['name' => 'Layanan Nonaktif', 'price_per_kg' => 5000, 'estimated_hours' => 24, 'is_active' => false]);

        $this->actingAs(User::factory()->create(['role' => UserRole::CUSTOMER]))
            ->get(route('catalog'))->assertOk()
            ->assertSee('Cuci Reguler')->assertSee('Cuci dan setrika.')
            ->assertSee('Rp 8.000')->assertSee('Estimasi 48 jam')
            ->assertDontSee('Layanan Nonaktif')
            ->assertSee(route('customer.orders.create'));
    }

    public function test_empty_catalog_has_an_honest_empty_state(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::CUSTOMER]))
            ->get(route('catalog'))->assertOk()->assertSee('Layanan belum tersedia');
    }

    public function test_catalog_is_public_but_ordering_requires_login(): void
    {
        $this->get(route('catalog'))->assertOk()->assertSee('Masuk')->assertSee('Daftar Pelanggan');
        $this->get(route('customer.orders.create'))->assertRedirect(route('login'));

        foreach ([UserRole::ADMIN, UserRole::COURIER] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('catalog'))->assertOk();
        }
    }

    public function test_home_page_is_the_public_catalog_but_keeps_dashboard_redirects_for_signed_in_users(): void
    {
        Service::create(['name' => 'Cuci Reguler', 'price_per_kg' => 8000, 'estimated_hours' => 48, 'is_active' => true]);

        $this->get(route('home'))->assertOk()
            ->assertSee('Cucian bersih')
            ->assertSee('Cuci Reguler')
            ->assertSee('Cara kerja')
            ->assertSee('Layanan');

        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))
            ->get(route('home'))->assertRedirect(route('admin.dashboard'));
    }
}
