<?php

namespace App\Services\Geocoding;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class AddressGeocodingService
{
    public function searchAddress(string $address, ?float $latitude = null, ?float $longitude = null): ?array
    {
        $cacheKey = 'geocoding.search.'.hash('sha256', implode('|', [
            mb_strtolower(trim($address)),
            $latitude === null ? '' : round($latitude, 3),
            $longitude === null ? '' : round($longitude, 3),
        ]));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($address, $latitude, $longitude): ?array {
            $query = [
                'q' => $address,
                'limit' => 1,
                'lang' => 'id',
                'countrycode' => 'id',
            ];

            if ($latitude !== null && $longitude !== null) {
                $query += ['lat' => $latitude, 'lon' => $longitude, 'zoom' => 16];
            }

            return $this->request(config('services.geocoding.forward_url'), $query, false);
        });
    }

    public function reverseCoordinates(float $latitude, float $longitude): ?array
    {
        $cacheKey = sprintf('geocoding.reverse.%0.6f.%0.6f', $latitude, $longitude);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude): ?array {
            $result = $this->request(config('services.geocoding.reverse_url'), [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'jsonv2',
                'accept-language' => 'id',
                'addressdetails' => 1,
            ], true);

            if (! is_array($result)) {
                return null;
            }

            $feature = $this->normalizeNominatimResult($result);

            return ['features' => $feature ? [$feature] : []];
        });
    }

    private function request(string $url, array $query, bool $nominatim): array|null
    {
        $request = function () use ($url, $query) {
            try {
                return Http::acceptJson()
                    ->withUserAgent(sprintf('%s/1.0 (%s)', config('app.name'), config('app.url')))
                    ->timeout(4)
                    ->get($url, $query);
            } catch (ConnectionException) {
                return null;
            }
        };

        $response = $nominatim
            ? RateLimiter::attempt('geocoding.nominatim', 1, $request, 1)
            : $request();

        if (! $response || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    private function normalizeNominatimResult(array $result): ?array
    {
        if (! isset($result['lon'], $result['lat'])) {
            return null;
        }

        $address = $result['address'] ?? [];

        return [
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $result['lon'], (float) $result['lat']],
            ],
            'properties' => [
                'housenumber' => $address['house_number'] ?? null,
                'street' => $address['road'] ?? null,
                'name' => $result['name'] ?? null,
                'suburb' => $address['suburb'] ?? $address['neighbourhood'] ?? null,
                'district' => $address['city_district'] ?? $address['county'] ?? null,
                'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                'state' => $address['state'] ?? null,
                'postcode' => $address['postcode'] ?? null,
                'country' => $address['country'] ?? null,
            ],
        ];
    }
}
