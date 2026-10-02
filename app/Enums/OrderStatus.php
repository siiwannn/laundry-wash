<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PICKUP_ASSIGNED = 'pickup_assigned';
    case COURIER_TO_PICKUP = 'courier_to_pickup';
    case PICKED_UP = 'picked_up';
    case RECEIVED_AT_LAUNDRY = 'received_at_laundry';
    case WASHING = 'washing';
    case DRYING = 'drying';
    case IRONING = 'ironing';
    case READY = 'ready';
    case WAITING_PAYMENT = 'waiting_payment';
    case PAID = 'paid';
    case DELIVERY_ASSIGNED = 'delivery_assigned';
    case COURIER_TO_CUSTOMER = 'courier_to_customer';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Konfirmasi',
            self::CONFIRMED => 'Terkonfirmasi',
            self::PICKUP_ASSIGNED => 'Kurir Pickup Ditugaskan',
            self::COURIER_TO_PICKUP => 'Kurir Menuju Lokasi Pickup',
            self::PICKED_UP => 'Laundry Berhasil Dijemput',
            self::RECEIVED_AT_LAUNDRY => 'Diterima di Laundry',
            self::WASHING => 'Sedang Dicuci',
            self::DRYING => 'Sedang Dikeringkan',
            self::IRONING => 'Sedang Disetrika',
            self::READY => 'Selesai & Siap Diambil/Dikirim',
            self::WAITING_PAYMENT => 'Menunggu Pembayaran',
            self::PAID => 'Pembayaran Terkonfirmasi',
            self::DELIVERY_ASSIGNED => 'Kurir Antar Ditugaskan',
            self::COURIER_TO_CUSTOMER => 'Kurir Sedang Mengantar',
            self::DELIVERED => 'Laundry Telah Diantar',
            self::COMPLETED => 'Pesanan Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning text-dark',
            self::CONFIRMED => 'bg-info text-dark',
            self::PICKUP_ASSIGNED, self::COURIER_TO_PICKUP => 'bg-primary',
            self::PICKED_UP, self::RECEIVED_AT_LAUNDRY => 'bg-secondary',
            self::WASHING, self::DRYING, self::IRONING => 'bg-primary text-white',
            self::READY => 'bg-success',
            self::WAITING_PAYMENT => 'bg-warning text-dark',
            self::PAID => 'bg-success',
            self::DELIVERY_ASSIGNED, self::COURIER_TO_CUSTOMER => 'bg-info text-white',
            self::DELIVERED, self::COMPLETED => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::PENDING => 1,
            self::CONFIRMED => 2,
            self::PICKUP_ASSIGNED, self::COURIER_TO_PICKUP => 3,
            self::PICKED_UP, self::RECEIVED_AT_LAUNDRY => 4,
            self::WASHING => 5,
            self::DRYING => 6,
            self::IRONING => 7,
            self::READY => 8,
            self::WAITING_PAYMENT => 9,
            self::PAID => 10,
            self::DELIVERY_ASSIGNED, self::COURIER_TO_CUSTOMER => 11,
            self::DELIVERED => 12,
            self::COMPLETED => 13,
            self::CANCELLED => 0,
        };
    }
}
