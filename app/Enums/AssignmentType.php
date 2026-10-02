<?php

namespace App\Enums;

enum AssignmentType: string
{
    case PICKUP = 'pickup';
    case DELIVERY = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::PICKUP => 'Penjemputan (Pickup)',
            self::DELIVERY => 'Pengantaran (Delivery)',
        };
    }
}
