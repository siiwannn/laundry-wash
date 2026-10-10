<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const SHIPPING_FEE = 10000;

    protected $fillable = [
        'order_number',
        'customer_id',
        'pickup_address_id',
        'delivery_address_id',
        'pickup_date',
        'pickup_time',
        'status',
        'payment_status',
        'estimated_weight',
        'actual_weight',
        'price_per_kg',
        'subtotal',
        'pickup_fee',
        'delivery_fee',
        'shipping_fee',
        'additional_fee',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'estimated_weight' => 'decimal:2',
            'actual_weight' => 'decimal:2',
            'pickup_date' => 'date',
            'price_per_kg' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'pickup_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'additional_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'pickup_address_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function serviceItem(): HasOne
    {
        return $this->hasOne(OrderItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }

    public function pickupAssignment(): HasOne
    {
        return $this->hasOne(CourierAssignment::class)->where('type', AssignmentType::PICKUP);
    }

    public function deliveryAssignment(): HasOne
    {
        return $this->hasOne(CourierAssignment::class)->where('type', AssignmentType::DELIVERY);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(CourierAssignment::class)->whereIn('status', ['assigned', 'on_the_way'])->latestOfMany();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED]);
    }

    public function canAcceptPayment(): bool
    {
        return in_array($this->status, [OrderStatus::READY, OrderStatus::WAITING_PAYMENT], true)
            && $this->payment_status === PaymentStatus::PENDING;
    }
}
