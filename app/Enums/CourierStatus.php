<?php

namespace App\Enums;

enum CourierStatus: string
{
    case AVAILABLE = 'available';
    case BUSY = 'busy';
    case OFFLINE = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::BUSY => 'Sibuk',
            self::OFFLINE => 'Offline',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'bg-success',
            self::BUSY => 'bg-warning text-dark',
            self::OFFLINE => 'bg-secondary',
        };
    }
}
