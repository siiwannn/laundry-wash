<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CourierAssignment;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveGpsTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'distance' => 4300.4,
                    'duration' => 480.2,
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => [[106.8456, -6.2088], [106.82, -6.2], [106.8, -6.2]],
                    ],
                ]],
            ]),
        ]);
    }

    public function test_courier_can_send_location_for_own_active_trip(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();

        $response = $this->actingAs($courier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'accuracy' => 12.5,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('courier_locations', [
            'courier_id' => $courier->id,
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
    }

    public function test_location_is_rejected_before_trip_is_started(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip(
            AssignmentStatus::ASSIGNED,
            OrderStatus::PICKUP_ASSIGNED
        );

        $response = $this->actingAs($courier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'accuracy' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'GPS hanya dapat dikirim setelah perjalanan dimulai.');
        $this->assertDatabaseCount('courier_locations', 0);
    }

    public function test_courier_cannot_send_location_for_another_couriers_assignment(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();
        $otherCourier = $this->createUser(UserRole::COURIER);

        $response = $this->actingAs($otherCourier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'accuracy' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Penugasan tidak ditemukan untuk kurir ini.');
        $this->assertDatabaseCount('courier_locations', 0);
    }

    public function test_customer_can_poll_tracking_for_own_order(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();

        $this->actingAs($courier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'accuracy' => 8,
        ])->assertOk();

        $response = $this->actingAs($customer)->getJson(route('api.orders.tracking', $order));

        $response->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.assignment_id', $assignment->id)
            ->assertJsonPath('data.courier_location.latitude', -6.2088)
            ->assertJsonPath('data.journey_status', 'Menuju lokasi pickup')
            ->assertJsonPath('data.route.distance_meters', 4300)
            ->assertJsonPath('data.route.duration_seconds', 480);
    }

    public function test_tracking_is_active_during_delivery(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();
        $assignment->update(['type' => AssignmentType::DELIVERY]);
        $order->update([
            'status' => OrderStatus::COURIER_TO_CUSTOMER,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $this->actingAs($courier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'heading' => 135,
        ])->assertOk();

        $this->actingAs($customer)->getJson(route('api.orders.tracking', $order))
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.type', 'delivery')
            ->assertJsonPath('data.journey_status', 'Menuju customer')
            ->assertJsonPath('data.courier_location.heading', 135);
    }

    public function test_customer_cannot_poll_another_customers_order(): void
    {
        [$customer, $courier, $order] = $this->createPickupTrip();
        $otherCustomer = $this->createUser(UserRole::CUSTOMER);

        $this->actingAs($otherCustomer)
            ->getJson(route('api.orders.tracking', $order))
            ->assertForbidden();
    }

    public function test_tracking_becomes_inactive_after_assignment_is_completed(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();
        $assignment->update([
            'status' => AssignmentStatus::COMPLETED,
            'completed_at' => now(),
        ]);
        $order->update(['status' => OrderStatus::PICKED_UP]);

        $response = $this->actingAs($customer)->getJson(route('api.orders.tracking', $order));

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    public function test_courier_cannot_send_location_after_assignment_is_completed(): void
    {
        [$customer, $courier, $order, $assignment] = $this->createPickupTrip();
        $assignment->update([
            'status' => AssignmentStatus::COMPLETED,
            'completed_at' => now(),
        ]);
        $order->update(['status' => OrderStatus::PICKED_UP]);

        $this->actingAs($courier)->postJson(route('api.courier.location'), [
            'assignment_id' => $assignment->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'GPS hanya dapat dikirim setelah perjalanan dimulai.');

        $this->assertDatabaseCount('courier_locations', 0);
    }

    private function createPickupTrip(
        AssignmentStatus $assignmentStatus = AssignmentStatus::ON_THE_WAY,
        OrderStatus $orderStatus = OrderStatus::COURIER_TO_PICKUP
    ): array {
        $customer = $this->createUser(UserRole::CUSTOMER);
        $courier = $this->createUser(UserRole::COURIER);
        $address = CustomerAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'address' => 'Jl. Pengujian GPS No. 1',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_default' => true,
        ]);
        $order = Order::create([
            'order_number' => 'ORD-GPS-'.str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'pickup_address_id' => $address->id,
            'delivery_address_id' => $address->id,
            'status' => $orderStatus,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => 40000,
            'delivery_fee' => 10000,
            'additional_fee' => 0,
            'total' => 50000,
        ]);
        $assignment = CourierAssignment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'type' => AssignmentType::PICKUP,
            'status' => $assignmentStatus,
            'assigned_at' => now()->subMinute(),
            'started_at' => $assignmentStatus === AssignmentStatus::ON_THE_WAY ? now() : null,
        ]);

        return [$customer, $courier, $order, $assignment];
    }

    private function createUser(UserRole $role): User
    {
        return User::factory()->create([
            'phone' => '081234567890',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
