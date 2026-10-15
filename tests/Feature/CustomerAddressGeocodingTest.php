<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CustomerAddressGeocodingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_address_search_uses_photon_and_returns_a_feature(): void
    {
        Cache::forget('geocoding.search.'.hash('sha256', 'jl. mawar no. 5, jakarta|-6.2|106.8'));
        Http::fake([
            'photon.komoot.io/api/*' => Http::response(['features' => [[
                'geometry' => ['coordinates' => [106.8, -6.2]],
                'properties' => ['street' => 'Jl. Mawar'],
            ]]]),
        ]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)
            ->getJson(route('customer.addresses.geocode', [
                'address' => 'Jl. Mawar No. 5, Jakarta',
                'latitude' => '-6.2',
                'longitude' => '106.8',
            ]))
            ->assertOk()
            ->assertJsonPath('features.0.geometry.coordinates.0', 106.8);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'photon.komoot.io/api/')
                && data_get($request->data(), 'q') === 'Jl. Mawar No. 5, Jakarta'
                && data_get($request->data(), 'countrycode') === 'id'
                && data_get($request->data(), 'lang') === 'id'
                && str_contains($request->header('User-Agent')[0] ?? '', 'Laundry Wash/1.0');
        });
    }

    public function test_customer_pin_reverse_lookup_uses_nominatim_and_returns_a_geocoding_feature(): void
    {
        Cache::forget('geocoding.reverse.-6.200000.106.800000');
        RateLimiter::clear('geocoding.nominatim');
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'lon' => '106.8',
                'lat' => '-6.2',
                'address' => ['road' => 'Jl. Mawar', 'city' => 'Jakarta'],
            ]),
        ]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)
            ->getJson(route('customer.addresses.reverse-geocode', ['latitude' => '-6.2', 'longitude' => '106.8']))
            ->assertOk()
            ->assertJsonPath('features.0.properties.street', 'Jl. Mawar')
            ->assertJsonPath('features.0.geometry.coordinates.0', 106.8);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'nominatim.openstreetmap.org/reverse')
                && data_get($request->data(), 'lat') == -6.2
                && data_get($request->data(), 'lon') == 106.8
                && data_get($request->data(), 'format') === 'jsonv2'
                && data_get($request->data(), 'addressdetails') == 1
                && str_contains($request->header('User-Agent')[0] ?? '', 'Laundry Wash/1.0');
        });
    }

    public function test_geocoding_proxy_requires_a_customer_session_and_valid_coordinates(): void
    {
        $this->getJson(route('customer.addresses.geocode', ['address' => 'Jl. Mawar, Jakarta']))
            ->assertUnauthorized();

        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->actingAs($customer)
            ->getJson(route('customer.addresses.reverse-geocode', ['latitude' => '200', 'longitude' => '106.8']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('latitude');
    }

    public function test_geocoding_proxy_returns_service_unavailable_when_photon_fails(): void
    {
        Cache::forget('geocoding.search.'.hash('sha256', 'jl. mawar, jakarta||'));
        Http::fake(['photon.komoot.io/api/*' => Http::response([], 503)]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)
            ->getJson(route('customer.addresses.geocode', ['address' => 'Jl. Mawar, Jakarta']))
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Layanan pencarian alamat sedang tidak tersedia.');
    }
}
