<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Belum Dibayar',
            self::PAID => 'Lunas',
            self::FAILED => 'Gagal',
            self::REFUNDED => 'Dikembalikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning text-dark',
            self::PAID => 'bg-success',
            self::FAILED => 'bg-danger',
            self::REFUNDED => 'bg-secondary',
        };
    }
}
