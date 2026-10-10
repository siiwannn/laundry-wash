<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CourierAssignment;
use App\Models\CourierProfile;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_has_the_shared_workspace_and_its_own_dashboard_actions(): void
    {
        foreach ([
            [UserRole::ADMIN, 'admin.dashboard', 'admin.orders.index', 'Volume order harian'],
            [UserRole::COURIER, 'courier.dashboard', 'courier.profile.status', 'Pickup hari ini'],
            [UserRole::CUSTOMER, 'customer.dashboard', 'customer.orders.create', 'Pesanan saya'],
        ] as [$role, $dashboard, $action, $label]) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route($dashboard))
                ->assertOk()
                ->assertSee('laundry-workspace.css')
                ->assertSee('dashboard-summary')
                ->assertSee($label)
                ->assertSee(route($action));
        }
    }

    public function test_public_catalog_still_works_without_a_workspace_sidebar(): void
    {
        $this->get(route('catalog'))->assertOk()
            ->assertSee('workspace-public')->assertDontSee('aria-label="Navigasi utama"', false);
    }

    public function test_customer_dashboard_uses_compact_metrics_without_the_old_hero(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::CUSTOMER]))
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('workspace-consistency.css')
            ->assertSee('customer-welcome-banner')
            ->assertSee('customer-greeting-emoji')
            ->assertSee('Pantau cucian yang sedang diproses atau mulai pesanan baru.')
            ->assertSee('customer-weather')
            ->assertSee('customer-weather-meta')
            ->assertSee('bi-plus-lg')
            ->assertDontSee('operasional antar-jemput')
            ->assertSee('role-summary-grid')
            ->assertSee('metric-icon-orange')
            ->assertDontSee('customer-hero-mark')
            ->assertDontSee('Drop-off langsung');
    }

    public function test_customer_weather_uses_the_default_address_and_returns_current_conditions(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $latitude = -6.12345;
        $longitude = 106.12345;
        $cacheKey = 'weather.current.'.hash('sha256', sprintf('%.3f,%.3f', $latitude, $longitude));
        Cache::forget($cacheKey);
        CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Jl. Contoh No. 10',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('data-weather-location="Rumah"', false)
            ->assertSee(route('customer.weather'));

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 31.2,
                    'relative_humidity_2m' => 70,
                    'apparent_temperature' => 35.1,
                    'weather_code' => 3,
                ],
            ]),
        ]);

        $this->actingAs($customer)
            ->getJson(route('customer.weather'))
            ->assertOk()
            ->assertJson([
                'temperature' => 31.2,
                'humidity' => 70,
                'feels_like' => 35.1,
                'weather_code' => 3,
            ]);

        Http::assertSent(function ($request) use ($latitude, $longitude): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_starts_with($request->url(), 'https://api.open-meteo.com/v1/forecast')
                && (float) ($query['latitude'] ?? 0) === $latitude
                && (float) ($query['longitude'] ?? 0) === $longitude;
        });
    }

    public function test_customer_weather_requires_a_default_address_with_coordinates(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)
            ->getJson(route('customer.weather'))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Tambahkan titik lokasi pada alamat utama untuk melihat cuaca.');
    }

    public function test_customer_dashboard_shows_friendly_order_summary_without_order_code(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Jl. Contoh No. 10',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);
        $service = Service::create([
            'name' => 'Cuci Reguler',
            'description' => 'Cuci dan lipat',
            'unit' => 'kg',
            'price_per_unit' => 8000,
            'estimated_hours' => 24,
            'is_active' => true,
        ]);
        $order = Order::create([
            'order_number' => 'ORD-DASH-PRIVATE',
            'customer_id' => $customer->id,
            'pickup_address_id' => $address->id,
            'delivery_address_id' => $address->id,
            'pickup_date' => now()->addDay(),
            'pickup_time' => '09:00',
            'status' => OrderStatus::READY,
            'payment_status' => PaymentStatus::PENDING,
            'estimated_weight' => 2,
            'price_per_kg' => 8000,
            'subtotal' => 16000,
            'total' => 16000,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'service_name_snapshot' => $service->name,
            'unit' => 'kg',
            'quantity' => 2,
            'estimated_quantity' => 2,
            'unit_price' => 8000,
            'subtotal' => 16000,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Cuci Reguler')
            ->assertSee('Alamat penjemputan')
            ->assertSee('Pesanan saya')
            ->assertDontSee('ORD-DASH-PRIVATE');
    }

    public function test_courier_dashboard_has_all_four_metrics_without_example_vehicle_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::COURIER]))
            ->get(route('courier.dashboard'))
            ->assertOk()
            ->assertSee('role-summary-grid')
            ->assertSee('Tugas aktif')
            ->assertSee('Pickup hari ini')
            ->assertSee('Delivery hari ini')
            ->assertSee('Selesai hari ini')
            ->assertDontSee('B 1234 ABC');
    }

    public function test_admin_order_page_does_not_display_customer_dashboard_title(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('workspace-consistency.css')
            ->assertDontSee('Dashboard Pelanggan');
    }

    public function test_admin_can_change_dashboard_chart_period(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 'month']))
            ->assertOk()
            ->assertSee('30 hari terakhir');

        $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 'invalid']))
            ->assertSessionHasErrors('range');
    }

    public function test_internal_pages_render_the_shared_navigation_for_each_role(): void
    {
        foreach ([
            [UserRole::ADMIN, ['admin.orders.index', 'admin.payments.index', 'admin.customers.index', 'admin.couriers.index', 'admin.couriers.create', 'admin.services.index', 'admin.services.create', 'admin.reports.index']],
            [UserRole::CUSTOMER, ['customer.orders.history', 'customer.orders.create', 'customer.addresses.index', 'customer.addresses.create', 'customer.profile.edit']],
            [UserRole::COURIER, ['courier.history']],
        ] as [$role, $pages]) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach ($pages as $page) {
                $response = $this->get(route($page));
                $response->assertOk()
                    ->assertSee('workspace-detail-page')
                    ->assertSee('workspace-consistency.css')
                    ->assertSee('workspace-navigation.js')
                    ->assertSee('id="mobile-sidebar-toggle"', false)
                    ->assertSee('id="sidebar-backdrop"', false);
                if ($role !== UserRole::ADMIN) {
                    $response->assertSee('workspace-page-search')
                        ->assertDontSee('/admin/settings')
                        ->assertDontSee(route('admin.orders.index'));
                }
            }
        }
    }

    public function test_catalog_and_login_do_not_load_internal_page_overrides(): void
    {
        $this->get(route('login'))->assertOk()->assertDontSee('workspace-consistency.css');
        $this->get(route('catalog'))->assertOk()->assertDontSee('workspace-consistency.css');
        $this->actingAs(User::factory()->create(['role' => UserRole::CUSTOMER]))
            ->get(route('catalog'))->assertOk()->assertDontSee('workspace-consistency.css');
    }

    public function test_order_and_courier_details_keep_the_existing_actions_with_shared_headings(): void
    {
        Http::fake();
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $courier = User::factory()->create(['role' => UserRole::COURIER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $address = CustomerAddress::create([
            'user_id' => $customer->id, 'label' => 'Rumah', 'address' => 'Alamat pengujian',
            'latitude' => -6.2, 'longitude' => 106.8, 'is_default' => true,
        ]);
        $order = Order::create([
            'order_number' => 'ORD-UI-TEST', 'customer_id' => $customer->id,
            'pickup_address_id' => $address->id, 'delivery_address_id' => $address->id,
            'pickup_date' => now()->addDay(), 'pickup_time' => '09:00',
            'status' => OrderStatus::PICKUP_ASSIGNED, 'payment_status' => PaymentStatus::PENDING,
            'estimated_weight' => 3, 'price_per_kg' => 8000, 'subtotal' => 24000, 'total' => 24000,
        ]);
        $assignment = CourierAssignment::create([
            'order_id' => $order->id, 'courier_id' => $courier->id,
            'type' => AssignmentType::PICKUP, 'status' => AssignmentStatus::ASSIGNED,
            'assigned_at' => now(),
        ]);
        CourierProfile::create([
            'user_id' => $courier->id,
            'status' => 'available',
            'current_latitude' => -6.2,
            'current_longitude' => 106.8,
        ]);

        $this->actingAs($customer)->get(route('customer.orders.show', $order))
            ->assertOk()->assertSee('workspace-page-heading')->assertSee(route('customer.orders.tracking', $order));
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()->assertSee('workspace-page-heading')->assertSee($order->order_number);
        $this->actingAs($courier)->get(route('courier.tasks.show', $assignment))
            ->assertOk()->assertSee('workspace-page-heading')->assertSee('courierMap')
            ->assertSee('courierEta')->assertSee('courierDistance')
            ->assertViewHas('courierStartCoordinates', [106.8, -6.2])
            ->assertSee("const isPickupAssignment = true", false)
            ->assertSee('function bearingBetween(from, to)', false)
            ->assertSee('function markerRotationForBearing(bearing)', false)
            ->assertSee('courierMarker.setLngLat(markerCoordinates)', false)
            ->assertSee('ensureCourierMarker(currentRoute)', false)
            ->assertSee(route('courier.tasks.start', $assignment));
    }
}
