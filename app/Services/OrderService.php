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
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * Create a new laundry order for a customer.
     */
    public function createOrder(User $customer, array $data): Order
    {
        return DB::transaction(function () use ($customer, $data) {
            $orderNumber = $this->generateOrderNumber();
            $additionalFee = 0.00;
            $shippingFee = Order::SHIPPING_FEE;

            $serviceId = (int) $data['service_id'];
            $service = Service::whereKey($serviceId)->where('is_active', true)->first();

            if (! $service) {
                throw new Exception('Layanan laundry tidak ditemukan atau sedang tidak aktif.');
            }

            $estimatedQuantity = $service->unit === 'pcs'
                ? (int) $data['estimated_quantity']
                : (float) $data['estimated_quantity'];

            $pickupAddressId = $data['pickup_address_id'] ?? null;

            if (! $customer->addresses()->whereKey($pickupAddressId)->exists()) {
                throw new Exception('Alamat penjemputan tidak valid atau bukan milik customer.');
            }

            $subtotal = round($estimatedQuantity * (float) $service->price_per_unit);
            $total = $subtotal + $shippingFee;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'pickup_address_id' => $pickupAddressId,
                'delivery_address_id' => $pickupAddressId,
                'pickup_date' => $data['pickup_date'] ?? null,
                'pickup_time' => $data['pickup_time'] ?? null,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'estimated_weight' => $service->unit === 'kg' ? $estimatedQuantity : null,
                'actual_weight' => null,
                'price_per_kg' => $service->unit === 'kg' ? $service->price_per_unit : 0,
                'subtotal' => $subtotal,
                'pickup_fee' => 0,
                'delivery_fee' => 0,
                'shipping_fee' => $shippingFee,
                'additional_fee' => $additionalFee,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'service_name_snapshot' => $service->name,
                'unit' => $service->unit,
                'quantity' => $estimatedQuantity,
                'estimated_quantity' => $estimatedQuantity,
                'actual_quantity' => null,
                'unit_price' => $service->price_per_unit,
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
    public function recordWeight(Order $order, float $actualQuantity, ?float $additionalFee, User $admin): Order
    {
        if ($order->status !== OrderStatus::RECEIVED_AT_LAUNDRY) {
            throw new Exception('Kuantitas aktual hanya dapat dicatat setelah cucian diterima di outlet.');
        }

        return DB::transaction(function () use ($order, $actualQuantity, $additionalFee, $admin) {
            $orderItem = $order->serviceItem()->firstOrFail();
            $actualQuantity = $orderItem->unit === 'pcs' ? (int) $actualQuantity : (float) $actualQuantity;
            $unitPrice = (float) $orderItem->unit_price;

            $subtotal = round($actualQuantity * $unitPrice);
            $addFee = $order->shipping_fee === null
                ? ($additionalFee !== null ? (float) $additionalFee : (float) $order->additional_fee)
                : 0.0;
            $shippingFee = $order->shipping_fee ?? ((float) $order->pickup_fee + (float) $order->delivery_fee);
            $total = $subtotal + $shippingFee + $addFee;

            $orderItem->update([
                'quantity' => $actualQuantity,
                'actual_quantity' => $actualQuantity,
                'subtotal' => $subtotal,
            ]);

            $order->update([
                'actual_weight' => $orderItem->unit === 'kg' ? $actualQuantity : null,
                'subtotal' => $subtotal,
                'additional_fee' => $addFee,
                'shipping_fee' => $order->shipping_fee,
                'total' => $total,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => "Kuantitas aktual {$actualQuantity} {$orderItem->unit} selesai dicatat. Total tagihan: Rp ".number_format($total, 0, ',', '.'),
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($admin, "Input kuantitas aktual order {$order->order_number}: {$actualQuantity} {$orderItem->unit}");

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
