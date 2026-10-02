<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\CourierStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\CourierAssignment;
use App\Models\CourierLocation;
use App\Models\CourierProfile;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@laundrywash.com'],
            [
                'name' => 'Administrator Laundry',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );

        // 2. Create Couriers
        $courier1 = User::firstOrCreate(
            ['email' => 'courier@laundrywash.com'],
            [
                'name' => 'Budi Santoso (Kurir)',
                'phone' => '081234567891',
                'password' => Hash::make('password'),
                'role' => UserRole::COURIER,
                'is_active' => true,
            ]
        );
        CourierProfile::updateOrCreate(
            ['user_id' => $courier1->id],
            [
                'vehicle_type' => 'Honda Vario 160',
                'vehicle_plate' => 'B 1234 ABC',
                'status' => CourierStatus::AVAILABLE,
            ]
        );

        $courier2 = User::firstOrCreate(
            ['email' => 'joko@laundrywash.com'],
            [
                'name' => 'Joko Widodo (Kurir)',
                'phone' => '081234567892',
                'password' => Hash::make('password'),
                'role' => UserRole::COURIER,
                'is_active' => true,
            ]
        );
        CourierProfile::updateOrCreate(
            ['user_id' => $courier2->id],
            [
                'vehicle_type' => 'Yamaha NMAX',
                'vehicle_plate' => 'B 5678 DEF',
                'status' => CourierStatus::AVAILABLE,
            ]
        );

        // 3. Create Customers
        $customer1 = User::firstOrCreate(
            ['email' => 'customer@laundrywash.com'],
            [
                'name' => 'Siti Rahmawati',
                'phone' => '081298765432',
                'password' => Hash::make('password'),
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ]
        );
        $cust1Address = CustomerAddress::firstOrCreate(
            ['user_id' => $customer1->id, 'label' => 'Rumah'],
            [
                'address' => 'Jl. Tebet Timur Dalam Raya No. 45, Jakarta Selatan',
                'latitude' => -6.2383,
                'longitude' => 106.8524,
                'is_default' => true,
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'rian@laundrywash.com'],
            [
                'name' => 'Rian Hidayat',
                'phone' => '081311223344',
                'password' => Hash::make('password'),
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ]
        );
        $cust2Address = CustomerAddress::firstOrCreate(
            ['user_id' => $customer2->id, 'label' => 'Apartemen'],
            [
                'address' => 'Apartemen Sudirman Tower A Lt. 12 No. 05, Jakarta Pusat',
                'latitude' => -6.2146,
                'longitude' => 106.8202,
                'is_default' => true,
            ]
        );

        // 4. Create Services
        $svcKomplit = Service::firstOrCreate(
            ['name' => 'Cuci Komplit Reguler'],
            [
                'description' => 'Cuci bersih, pengeringan higienis, setrika rapi, dan kemasan wangi.',
                'price_per_kg' => 8000,
                'estimated_hours' => 48,
                'is_active' => true,
            ]
        );

        $svcExpress = Service::firstOrCreate(
            ['name' => 'Cuci Kilat Express (1 Hari)'],
            [
                'description' => 'Layanan kilat selesai dalam 12–24 jam. Prioritas mesin cuci dan setrika uap.',
                'price_per_kg' => 15000,
                'estimated_hours' => 24,
                'is_active' => true,
            ]
        );

        $svcSetrika = Service::firstOrCreate(
            ['name' => 'Setrika Saja (Lipat Rapi)'],
            [
                'description' => 'Setrika uap profesional dan packing rapi.',
                'price_per_kg' => 5000,
                'estimated_hours' => 24,
                'is_active' => true,
            ]
        );

        $svcBedcover = Service::firstOrCreate(
            ['name' => 'Bed Cover & Selimut Tebal'],
            [
                'description' => 'Pencucian khusus selimut besar dan bed cover menggunakan deterjen antibakteri.',
                'price_per_kg' => 20000,
                'estimated_hours' => 48,
                'is_active' => true,
            ]
        );

        // 5. Seed Demonstration Orders across key lifecycle states
        // Order 1: Pending Order
        $orderPending = Order::firstOrCreate(
            ['order_number' => 'ORD-20261002-0001'],
            [
                'customer_id' => $customer1->id,
                'pickup_address_id' => $cust1Address->id,
                'delivery_address_id' => $cust1Address->id,
                'service_type' => ServiceType::PICKUP_AND_DELIVERY,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'estimated_weight' => 5.0,
                'actual_weight' => null,
                'subtotal' => 0,
                'delivery_fee' => 10000,
                'additional_fee' => 0,
                'total' => 10000,
                'notes' => 'Tolong pisahkan pakaian putih dan berwarna.',
            ]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $orderPending->id, 'service_id' => $svcKomplit->id],
            [
                'quantity' => 5.0,
                'unit_price' => $svcKomplit->price_per_kg,
                'subtotal' => 40000,
            ]
        );
        OrderStatusHistory::firstOrCreate(
            ['order_id' => $orderPending->id, 'status' => OrderStatus::PENDING],
            [
                'note' => 'Pesanan baru dibuat oleh customer Siti Rahmawati.',
                'changed_by' => $customer1->id,
                'created_at' => now()->subHours(2),
            ]
        );

        // Order 2: Active Pickup Assignment with GPS Beacon simulation
        $orderPickup = Order::firstOrCreate(
            ['order_number' => 'ORD-20261002-0002'],
            [
                'customer_id' => $customer2->id,
                'pickup_address_id' => $cust2Address->id,
                'delivery_address_id' => $cust2Address->id,
                'service_type' => ServiceType::PICKUP_AND_DELIVERY,
                'status' => OrderStatus::COURIER_TO_PICKUP,
                'payment_status' => PaymentStatus::PENDING,
                'estimated_weight' => 4.0,
                'actual_weight' => null,
                'subtotal' => 0,
                'delivery_fee' => 10000,
                'additional_fee' => 0,
                'total' => 10000,
                'notes' => 'Diantar sebelum jam 5 sore.',
            ]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $orderPickup->id, 'service_id' => $svcExpress->id],
            [
                'quantity' => 4.0,
                'unit_price' => $svcExpress->price_per_kg,
                'subtotal' => 60000,
            ]
        );
        OrderStatusHistory::firstOrCreate(
            ['order_id' => $orderPickup->id, 'status' => OrderStatus::CONFIRMED],
            [
                'note' => 'Pesanan dikonfirmasi oleh Admin.',
                'changed_by' => $admin->id,
                'created_at' => now()->subMinutes(50),
            ]
        );
        OrderStatusHistory::firstOrCreate(
            ['order_id' => $orderPickup->id, 'status' => OrderStatus::COURIER_TO_PICKUP],
            [
                'note' => 'Kurir Budi Santoso sedang dalam perjalanan menuju alamat penjemputan.',
                'changed_by' => $courier1->id,
                'created_at' => now()->subMinutes(20),
            ]
        );
        $pickupAssign = CourierAssignment::firstOrCreate(
            ['order_id' => $orderPickup->id, 'type' => AssignmentType::PICKUP],
            [
                'courier_id' => $courier1->id,
                'status' => AssignmentStatus::ON_THE_WAY,
                'assigned_at' => now()->subMinutes(30),
                'started_at' => now()->subMinutes(20),
            ]
        );
        // Seed courier initial GPS position close to customer
        CourierLocation::create([
            'courier_id' => $courier1->id,
            'assignment_id' => $pickupAssign->id,
            'latitude' => -6.2200,
            'longitude' => 106.8250,
            'accuracy' => 12.5,
            'recorded_at' => now()->subMinutes(2),
        ]);

        // Order 3: Ready for Payment & Delivery
        $orderReady = Order::firstOrCreate(
            ['order_number' => 'ORD-20261002-0003'],
            [
                'customer_id' => $customer1->id,
                'pickup_address_id' => $cust1Address->id,
                'delivery_address_id' => $cust1Address->id,
                'service_type' => ServiceType::PICKUP_AND_DELIVERY,
                'status' => OrderStatus::READY,
                'payment_status' => PaymentStatus::PENDING,
                'estimated_weight' => 6.0,
                'actual_weight' => 5.5,
                'subtotal' => 44000,
                'delivery_fee' => 10000,
                'additional_fee' => 0,
                'total' => 54000,
                'notes' => 'Pewangi aroma lavender.',
            ]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $orderReady->id, 'service_id' => $svcKomplit->id],
            [
                'quantity' => 5.5,
                'unit_price' => 8000,
                'subtotal' => 44000,
            ]
        );
        OrderStatusHistory::firstOrCreate(
            ['order_id' => $orderReady->id, 'status' => OrderStatus::READY],
            [
                'note' => 'Laundry selesai dicuci, dikeringkan, dan disetrika rapi. Siap dikirim setelah pembayaran.',
                'changed_by' => $admin->id,
                'created_at' => now()->subHours(1),
            ]
        );

        // Order 4: Completed Order
        $orderCompleted = Order::firstOrCreate(
            ['order_number' => 'ORD-20261001-0004'],
            [
                'customer_id' => $customer2->id,
                'pickup_address_id' => $cust2Address->id,
                'delivery_address_id' => $cust2Address->id,
                'service_type' => ServiceType::PICKUP_AND_DELIVERY,
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'estimated_weight' => 3.0,
                'actual_weight' => 3.2,
                'subtotal' => 48000,
                'delivery_fee' => 10000,
                'additional_fee' => 0,
                'total' => 58000,
                'notes' => null,
            ]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $orderCompleted->id, 'service_id' => $svcExpress->id],
            [
                'quantity' => 3.2,
                'unit_price' => 15000,
                'subtotal' => 48000,
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $orderCompleted->id],
            [
                'method' => PaymentMethod::TRANSFER,
                'amount' => 58000,
                'status' => PaymentStatus::PAID,
                'reference' => 'TRX-BCA-889911',
                'paid_at' => now()->subDay(),
            ]
        );
        OrderStatusHistory::firstOrCreate(
            ['order_id' => $orderCompleted->id, 'status' => OrderStatus::COMPLETED],
            [
                'note' => 'Pesanan telah selesai dan diterima dengan baik oleh customer.',
                'changed_by' => $admin->id,
                'created_at' => now()->subDay(),
            ]
        );
    }
}
