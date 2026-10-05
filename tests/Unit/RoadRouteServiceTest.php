<?php

namespace Tests\Unit;

use App\Services\RoadRouteService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoadRouteServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_normalizes_a_road_route_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'distance' => 4321.6,
                    'duration' => 487.4,
                    'geometry' => ['coordinates' => [[106.8, -6.2], [106.9, -6.3]]],
                ]],
            ]),
        ]);

        $route = app(RoadRouteService::class)->route(-6.2, 106.8, -6.3, 106.9);

        $this->assertSame(4322, $route['distance_meters']);
        $this->assertSame(487, $route['duration_seconds']);
        $this->assertSame('LineString', $route['geometry']['type']);
    }

    public function test_it_returns_null_when_the_routing_service_fails(): void
    {
        Http::fake(['*' => Http::response(['code' => 'NoRoute'], 400)]);

        $this->assertNull(app(RoadRouteService::class)->route(-6.2, 106.8, -6.3, 106.9));
    }
}
