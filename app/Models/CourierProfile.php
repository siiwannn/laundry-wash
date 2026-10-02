<?php

namespace App\Models;

use App\Enums\CourierStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_plate',
        'status',
        'current_latitude',
        'current_longitude',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourierStatus::class,
            'current_latitude' => 'float',
            'current_longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
