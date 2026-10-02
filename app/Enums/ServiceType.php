<?php

namespace App\Enums;

enum ServiceType: string
{
    case PICKUP_AND_DELIVERY = 'pickup_and_delivery';
    case SELF_DROP_OFF = 'self_drop_off';

    public function label(): string
    {
        return match ($this) {
            self::PICKUP_AND_DELIVERY => 'Antar Jemput (Pickup & Delivery)',
            self::SELF_DROP_OFF => 'Antar Sendiri ke Outlet (Self Drop-Off)',
        };
    }
}
