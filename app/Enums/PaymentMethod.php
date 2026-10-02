<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case QRIS = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai (Cash)',
            self::TRANSFER => 'Transfer Bank',
            self::QRIS => 'QRIS (Gopay/OVO/ShopeePay/BCA)',
        };
    }
}
