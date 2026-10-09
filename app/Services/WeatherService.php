<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function current(float $latitude, float $longitude): ?array
    {
        $cacheKey = 'weather.current.'.hash('sha256', sprintf('%.3f,%.3f', $latitude, $longitude));

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($latitude, $longitude): ?array {
            try {
                $request = Http::acceptJson()
                    ->connectTimeout(2)
                    ->timeout(4);

                $caBundle = trim((string) (
                    config('services.midtrans.ca_bundle')
                    ?: ini_get('curl.cainfo')
                    ?: ini_get('openssl.cafile')
                ));

                if ($caBundle === '' || ! is_file($caBundle)) {
                    $laragonCaBundle = 'C:\\laragon\\etc\\ssl\\cacert.pem';

                    if (is_file($laragonCaBundle)) {
                        $caBundle = $laragonCaBundle;
                    }
                }

                if ($caBundle !== '' && is_file($caBundle)) {
                    $request = $request->withOptions(['verify' => $caBundle]);
                }

                $response = $request->get(config('services.weather.url'), [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code',
                        'timezone' => 'auto',
                        'forecast_days' => 1,
                    ]);
            } catch (\Throwable) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $current = $response->json('current');
            $requiredValues = ['temperature_2m', 'relative_humidity_2m', 'apparent_temperature', 'weather_code'];

            if (! is_array($current)) {
                return null;
            }

            foreach ($requiredValues as $key) {
                if (! isset($current[$key]) || ! is_numeric($current[$key])) {
                    return null;
                }
            }

            return [
                'temperature' => (float) $current['temperature_2m'],
                'humidity' => (int) $current['relative_humidity_2m'],
                'feels_like' => (float) $current['apparent_temperature'],
                'weather_code' => (int) $current['weather_code'],
            ];
        });
    }
}
