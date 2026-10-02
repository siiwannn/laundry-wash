<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case ASSIGNED = 'assigned';
    case ON_THE_WAY = 'on_the_way';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED => 'Ditugaskan',
            self::ON_THE_WAY => 'Dalam Perjalanan',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ASSIGNED => 'bg-secondary',
            self::ON_THE_WAY => 'bg-primary text-white',
            self::COMPLETED => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }
}
