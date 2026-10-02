<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\CourierStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceType;
use App\Models\CourierAssignment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CourierAssignmentService
{
    /**
     * Admin assigns a courier for pickup or delivery.
     */
    public function assignCourier(Order $order, int $courierId, AssignmentType $type, User $admin): CourierAssignment
    {
        $courier = User::findOrFail($courierId);
        if (! $courier->isCourier()) {
            throw new Exception('Pengguna yang dipilih bukan kurir.');
        }

        if (! $courier->is_active) {
            throw new Exception('Kurir yang dipilih sedang tidak aktif.');
        }

        if ($courier->courierProfile?->status !== CourierStatus::AVAILABLE) {
            throw new Exception('Kurir yang dipilih sedang tidak tersedia.');
        }

        if ($type === AssignmentType::PICKUP && $order->status !== OrderStatus::CONFIRMED) {
            throw new Exception('Kurir pickup hanya dapat ditugaskan pada order berstatus Confirmed.');
        }

        if ($type === AssignmentType::DELIVERY
            && ($order->status !== OrderStatus::PAID || $order->payment_status !== PaymentStatus::PAID)) {
            throw new Exception('Kurir delivery hanya dapat ditugaskan setelah pembayaran dikonfirmasi lunas.');
        }

        if ($order->assignments()->where('type', $type->value)->whereIn('status', [
            AssignmentStatus::ASSIGNED->value,
            AssignmentStatus::ON_THE_WAY->value,
        ])->exists()) {
            throw new Exception('Order ini sudah memiliki penugasan aktif untuk tipe tersebut.');
        }

        return DB::transaction(function () use ($order, $courier, $type, $admin) {
            $assignment = CourierAssignment::create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'type' => $type,
                'status' => AssignmentStatus::ASSIGNED,
                'assigned_at' => now(),
            ]);

            $newOrderStatus = $type === AssignmentType::PICKUP
                ? OrderStatus::PICKUP_ASSIGNED
                : OrderStatus::DELIVERY_ASSIGNED;

            $order->update(['status' => $newOrderStatus]);

            if ($courier->courierProfile) {
                $courier->courierProfile->update(['status' => CourierStatus::BUSY]);
            }

            $typeLabel = $type === AssignmentType::PICKUP ? 'penjemputan' : 'pengantaran';
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newOrderStatus,
                'note' => "Tugas {$typeLabel} ditugaskan kepada kurir {$courier->name}.",
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);

            return $assignment;
        });
    }

    /**
     * Courier starts the journey towards customer.
     */
    public function startTrip(CourierAssignment $assignment, User $courier): CourierAssignment
    {
        if ($assignment->courier_id !== $courier->id) {
            throw new Exception('Anda tidak memiliki otorisasi untuk memulai tugas ini.');
        }

        if ($assignment->status !== AssignmentStatus::ASSIGNED) {
            throw new Exception('Tugas hanya dapat dimulai dari status Ditugaskan.');
        }

        $expectedOrderStatus = $assignment->type === AssignmentType::PICKUP
            ? OrderStatus::PICKUP_ASSIGNED
            : OrderStatus::DELIVERY_ASSIGNED;

        if ($assignment->order->status !== $expectedOrderStatus) {
            throw new Exception('Status order tidak sesuai untuk memulai perjalanan ini.');
        }

        if ($assignment->type === AssignmentType::DELIVERY
            && $assignment->order->payment_status !== PaymentStatus::PAID) {
            throw new Exception('Delivery tidak dapat dimulai sebelum pembayaran lunas.');
        }

        return DB::transaction(function () use ($assignment, $courier) {
            $assignment->update([
                'status' => AssignmentStatus::ON_THE_WAY,
                'started_at' => now(),
            ]);

            $order = $assignment->order;
            $newOrderStatus = $assignment->type === AssignmentType::PICKUP
                ? OrderStatus::COURIER_TO_PICKUP
                : OrderStatus::COURIER_TO_CUSTOMER;

            $order->update(['status' => $newOrderStatus]);

            $tripDesc = $assignment->type === AssignmentType::PICKUP
                ? "Kurir {$courier->name} sedang dalam perjalanan menuju lokasi penjemputan pakaian."
                : "Kurir {$courier->name} sedang dalam perjalanan mengantarkan pakaian bersih ke alamat tujuan.";

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newOrderStatus,
                'note' => $tripDesc,
                'changed_by' => $courier->id,
                'created_at' => now(),
            ]);

            return $assignment;
        });
    }

    /**
     * Courier completes pickup at customer's location.
     */
    public function completePickup(CourierAssignment $assignment, User $courier): CourierAssignment
    {
        if ($assignment->courier_id !== $courier->id) {
            throw new Exception('Anda tidak memiliki otorisasi untuk menyelesaikan tugas ini.');
        }

        if ($assignment->type !== AssignmentType::PICKUP
            || $assignment->status !== AssignmentStatus::ON_THE_WAY
            || $assignment->order->status !== OrderStatus::COURIER_TO_PICKUP) {
            throw new Exception('Tugas pickup belum berada pada tahap yang dapat diselesaikan.');
        }

        return DB::transaction(function () use ($assignment, $courier) {
            $assignment->update([
                'status' => AssignmentStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $order = $assignment->order;
            $order->update(['status' => OrderStatus::PICKED_UP]);

            if ($courier->courierProfile) {
                $courier->courierProfile->update(['status' => CourierStatus::AVAILABLE]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::PICKED_UP,
                'note' => "Pakaian telah berhasil dijemput oleh kurir {$courier->name} dan dibawa menuju workshop laundry.",
                'changed_by' => $courier->id,
                'created_at' => now(),
            ]);

            return $assignment;
        });
    }

    /**
     * Admin receives garments at the laundry facility.
     */
    public function receiveAtLaundry(Order $order, User $admin): Order
    {
        $isPickedUp = $order->status === OrderStatus::PICKED_UP;
        $isSelfDropOff = $order->service_type === ServiceType::SELF_DROP_OFF
            && $order->status === OrderStatus::CONFIRMED;

        if (! $isPickedUp && ! $isSelfDropOff) {
            throw new Exception('Laundry hanya dapat diterima setelah pickup selesai atau customer melakukan drop-off.');
        }

        return DB::transaction(function () use ($order, $admin) {
            $order->update(['status' => OrderStatus::RECEIVED_AT_LAUNDRY]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::RECEIVED_AT_LAUNDRY,
                'note' => 'Laundry telah tiba dan diterima dengan aman di outlet/workshop.',
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Courier completes delivery to customer.
     */
    public function completeDelivery(CourierAssignment $assignment, User $courier): CourierAssignment
    {
        if ($assignment->courier_id !== $courier->id) {
            throw new Exception('Anda tidak memiliki otorisasi untuk menyelesaikan tugas ini.');
        }

        if ($assignment->type !== AssignmentType::DELIVERY
            || $assignment->status !== AssignmentStatus::ON_THE_WAY
            || $assignment->order->status !== OrderStatus::COURIER_TO_CUSTOMER) {
            throw new Exception('Tugas delivery belum berada pada tahap yang dapat diselesaikan.');
        }

        if ($assignment->order->payment_status !== PaymentStatus::PAID) {
            throw new Exception('Delivery tidak dapat diselesaikan sebelum pembayaran lunas.');
        }

        return DB::transaction(function () use ($assignment, $courier) {
            $assignment->update([
                'status' => AssignmentStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $order = $assignment->order;

            $newStatus = OrderStatus::COMPLETED;

            $order->update(['status' => $newStatus]);

            if ($courier->courierProfile) {
                $courier->courierProfile->update(['status' => CourierStatus::AVAILABLE]);
            }

            $note = "Laundry telah diserahterimakan kepada customer oleh kurir {$courier->name}. Pesanan selesai!";

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'note' => $note,
                'changed_by' => $courier->id,
                'created_at' => now(),
            ]);

            return $assignment;
        });
    }
}
