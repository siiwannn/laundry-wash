<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case COURIER = 'courier';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::CUSTOMER => 'Customer',
            self::COURIER => 'Kurir',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-danger',
            self::CUSTOMER => 'bg-primary',
            self::COURIER => 'bg-warning text-dark',
        };
    }
}
