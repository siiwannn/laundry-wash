<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case QRIS = 'qris';
    case VIRTUAL_ACCOUNT = 'virtual_account';

    public function label(): string
    {
        return match ($this) {
            self::QRIS => 'QRIS',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
        };
    }
}
