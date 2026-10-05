<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class RoadRouteService
{
    public function route(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): ?array
    {
        $cacheKey = sprintf(
            'road-route:%0.4f:%0.4f:%0.4f:%0.4f',
            $fromLatitude,
            $fromLongitude,
            $toLatitude,
            $toLongitude,
        );

        return Cache::remember($cacheKey, now()->addSeconds(15), function () use ($fromLatitude, $fromLongitude, $toLatitude, $toLongitude) {
            try {
                $baseUrl = rtrim((string) config('services.tracking.routing_url'), '/');
                $coordinates = "{$fromLongitude},{$fromLatitude};{$toLongitude},{$toLatitude}";
                $response = Http::acceptJson()
                    ->timeout(4)
                    ->retry(1, 150, fn (Throwable $exception) => $exception instanceof ConnectionException)
                    ->get("{$baseUrl}/route/v1/driving/{$coordinates}", [
                        'overview' => 'full',
                        'geometries' => 'geojson',
                        'steps' => 'false',
                    ]);

                if (! $response->successful() || $response->json('code') !== 'Ok') {
                    return null;
                }

                $route = $response->json('routes.0');
                if (! is_array($route) || ! isset($route['geometry']['coordinates'], $route['distance'], $route['duration'])) {
                    return null;
                }

                return [
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $route['geometry']['coordinates'],
                    ],
                    'distance_meters' => (int) round((float) $route['distance']),
                    'duration_seconds' => (int) round((float) $route['duration']),
                ];
            } catch (Throwable) {
                return null;
            }
        });
    }
}
