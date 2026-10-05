<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Service;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly SettingService $settings, private readonly ActivityLogService $activityLog) {}

    /**
     * Create a new laundry order for a customer.
     */
    public function createOrder(User $customer, array $data): Order
    {
        return DB::transaction(function () use ($customer, $data) {
            $orderNumber = $this->generateOrderNumber();
            $estimatedWeight = isset($data['estimated_weight']) ? (float) $data['estimated_weight'] : null;

            $setting = $this->settings->current();
            $pickupFee = (float) $setting->pickup_fee;
            $deliveryFee = (float) $setting->delivery_fee;
            $additionalFee = 0.00;

            // Compute estimated subtotal
            $subtotal = 0.00;
            $serviceId = (int) $data['service_id'];
            $service = Service::whereKey($serviceId)->where('is_active', true)->first();

            if (! $service) {
                throw new Exception('Layanan laundry tidak ditemukan atau sedang tidak aktif.');
            }

            $pickupAddressId = $data['pickup_address_id'] ?? null;

            if (! $customer->addresses()->whereKey($pickupAddressId)->exists()) {
                throw new Exception('Alamat penjemputan tidak valid atau bukan milik customer.');
            }

            if ($estimatedWeight && $estimatedWeight > 0) {
                $subtotal = $estimatedWeight * (float) $service->price_per_kg;
            }

            $pricePerKg = (float) $setting->laundry_price_per_kg;
            $subtotal = $estimatedWeight ? $estimatedWeight * $pricePerKg : 0.00;
            $total = $subtotal + $pickupFee + $deliveryFee + $additionalFee;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'pickup_address_id' => $pickupAddressId,
                'delivery_address_id' => $pickupAddressId,
                'pickup_date' => $data['pickup_date'] ?? null,
                'pickup_time' => $data['pickup_time'] ?? null,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'estimated_weight' => $estimatedWeight,
                'actual_weight' => null,
                'price_per_kg' => $pricePerKg,
                'subtotal' => $subtotal,
                'pickup_fee' => $pickupFee,
                'delivery_fee' => $deliveryFee,
                'additional_fee' => $additionalFee,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'quantity' => $estimatedWeight ?: 1.0,
                'unit_price' => $pricePerKg,
                'subtotal' => $subtotal,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::PENDING,
                'note' => 'Pesanan berhasil dibuat oleh pelanggan.',
                'changed_by' => $customer->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($customer, "Membuat order {$order->order_number}");

            return $order;
        });
    }

    /**
     * Admin confirms an order.
     */
    public function confirmOrder(Order $order, User $admin): Order
    {
        if ($order->status !== OrderStatus::PENDING) {
            throw new Exception('Pesanan hanya dapat dikonfirmasi dari status Pending.');
        }

        return DB::transaction(function () use ($order, $admin) {
            $order->update(['status' => OrderStatus::CONFIRMED]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::CONFIRMED,
                'note' => 'Pesanan telah diverifikasi dan dikonfirmasi oleh Admin.',
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($admin, "Mengonfirmasi order {$order->order_number}");

            return $order;
        });
    }

    /**
     * Admin records actual weight upon arrival at laundry facility and calculates final bill.
     */
    public function recordWeight(Order $order, float $actualWeight, ?float $additionalFee, User $admin): Order
    {
        if ($order->status !== OrderStatus::RECEIVED_AT_LAUNDRY) {
            throw new Exception('Berat aktual hanya dapat diinput setelah laundry diterima di outlet.');
        }

        return DB::transaction(function () use ($order, $actualWeight, $additionalFee, $admin) {
            $orderItem = $order->items()->first();
            $unitPrice = (float) $order->price_per_kg;

            $subtotal = round($actualWeight * $unitPrice, 2);
            $addFee = $additionalFee !== null ? (float) $additionalFee : (float) $order->additional_fee;
            $total = $subtotal + (float) $order->pickup_fee + (float) $order->delivery_fee + $addFee;

            if ($orderItem) {
                $orderItem->update([
                    'quantity' => $actualWeight,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update([
                'actual_weight' => $actualWeight,
                'subtotal' => $subtotal,
                'additional_fee' => $addFee,
                'total' => $total,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => "Penimbangan selesai: berat aktual {$actualWeight} kg. Total tagihan: Rp ".number_format($total, 0, ',', '.'),
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($admin, "Input berat aktual order {$order->order_number}: {$actualWeight} kg");

            return $order;
        });
    }

    /**
     * Advance laundry processing stage: washing -> drying -> ironing -> ready.
     */
    public function updateLaundryStage(Order $order, OrderStatus $newStage, User $actor, ?string $note = null): Order
    {
        $allowedTransitions = [
            OrderStatus::RECEIVED_AT_LAUNDRY->value => OrderStatus::WASHING,
            OrderStatus::WASHING->value => OrderStatus::DRYING,
            OrderStatus::DRYING->value => OrderStatus::IRONING,
            OrderStatus::IRONING->value => OrderStatus::READY,
        ];

        $expectedStage = $allowedTransitions[$order->status->value] ?? null;

        if ($expectedStage !== $newStage) {
            $expectedLabel = $expectedStage?->label() ?? 'tidak ada';

            throw new Exception(
                "Transisi status tidak valid. Tahap berikutnya dari {$order->status->label()} adalah {$expectedLabel}."
            );
        }

        return DB::transaction(function () use ($order, $newStage, $actor, $note) {
            $order->update(['status' => $newStage]);

            $defaultNote = match ($newStage) {
                OrderStatus::WASHING => 'Pakaian sedang dicuci dengan deterjen khusus.',
                OrderStatus::DRYING => 'Proses pencucian selesai, pakaian dipindahkan ke mesin pengering.',
                OrderStatus::IRONING => 'Pakaian telah kering dan sedang disetrika uap rapi.',
                OrderStatus::READY => 'Laundry telah selesai diproses dan siap diserahkan / diantar.',
                default => 'Perubahan status pesanan.',
            };

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStage,
                'note' => $note ?: $defaultNote,
                'changed_by' => $actor->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($actor, "Mengubah status order {$order->order_number} menjadi {$newStage->value}");

            return $order;
        });
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order, User $actor, string $reason): Order
    {
        if (! $order->canBeCancelled()) {
            throw new Exception('Pesanan tidak dapat dibatalkan pada tahapan proses saat ini.');
        }

        return DB::transaction(function () use ($order, $actor, $reason) {
            $order->update(['status' => OrderStatus::CANCELLED]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::CANCELLED,
                'note' => 'Pesanan dibatalkan: '.$reason,
                'changed_by' => $actor->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($actor, "Membatalkan order {$order->order_number}");

            return $order;
        });
    }

    /**
     * Generate unique sequential order number formatted ORD-YYYYMMDD-XXXX
     */
    private function generateOrderNumber(): string
    {
        $datePrefix = date('Ymd');
        $prefix = "ORD-{$datePrefix}-";

        $latestOrder = Order::where('order_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $latestOrder) {
            return "{$prefix}0001";
        }

        $lastSeq = (int) substr($latestOrder->order_number, -4);
        $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextSeq}";
    }
}
