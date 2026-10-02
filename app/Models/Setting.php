<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['laundry_price_per_kg', 'pickup_fee', 'delivery_fee', 'updated_by'];

    protected function casts(): array
    {
        return ['laundry_price_per_kg' => 'decimal:2', 'pickup_fee' => 'decimal:2', 'delivery_fee' => 'decimal:2'];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
