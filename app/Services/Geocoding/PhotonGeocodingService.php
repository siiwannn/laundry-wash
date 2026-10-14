<?php

namespace App\Services\Geocoding;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PhotonGeocodingService
{
    public function searchAddress(string $address, ?float $latitude = null, ?float $longitude = null): ?array
    {
        $query = [
            'q' => $address,
            'limit' => 1,
            'lang' => 'id',
            'countrycode' => 'id',
        ];

        if ($latitude !== null && $longitude !== null) {
            $query += [
                'lat' => $latitude,
                'lon' => $longitude,
                'zoom' => 16,
            ];
        }

        return $this->request(config('services.geocoding.forward_url'), $query);
    }

    public function reverseCoordinates(float $latitude, float $longitude): ?array
    {
        $cacheKey = sprintf('geocoding.reverse.%0.6f.%0.6f', $latitude, $longitude);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude): ?array {
            try {
                $response = Http::acceptJson()
                    ->withUserAgent(sprintf('%s/1.0 (%s)', config('app.name'), config('app.url')))
                    ->timeout(4)
                    ->get(config('services.geocoding.reverse_url'), [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'jsonv2',
                        'accept-language' => 'id',
                        'addressdetails' => 1,
                    ]);
            } catch (ConnectionException) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data) || ! isset($data['lon'], $data['lat'])) {
                return null;
            }

            $address = $data['address'] ?? [];

            return [
                'features' => [[
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float) $data['lon'], (float) $data['lat']],
                    ],
                    'properties' => [
                        'housenumber' => $address['house_number'] ?? null,
                        'street' => $address['road'] ?? null,
                        'suburb' => $address['suburb'] ?? $address['neighbourhood'] ?? null,
                        'district' => $address['city_district'] ?? $address['county'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                        'state' => $address['state'] ?? null,
                        'postcode' => $address['postcode'] ?? null,
                        'country' => $address['country'] ?? null,
                    ],
                ]],
            ];
        });
    }

    private function request(string $url, array $query): ?array
    {
        try {
            $response = Http::acceptJson()
                ->withUserAgent(sprintf('%s/1.0 (%s)', config('app.name'), config('app.url')))
                ->timeout(8)
                ->get($url, $query);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) && isset($data['features']) && is_array($data['features'])
            ? $data
            : null;
    }
}
