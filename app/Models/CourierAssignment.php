<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'type',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssignmentType::class,
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(CourierLocation::class, 'assignment_id');
    }

    public function latestLocation(): HasOne
    {
        return $this->hasOne(CourierLocation::class, 'assignment_id')->latestOfMany('recorded_at');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [AssignmentStatus::ASSIGNED, AssignmentStatus::ON_THE_WAY]);
    }
}
