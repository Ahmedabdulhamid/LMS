<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $guarded = ['id', 'number'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'status' => OrderStatus::class,
        'billing_details' => 'array',
        'paid_at' => 'datetime',
        'payment_status' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->number ??= static::generateNumber();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function paymentWebhookEvents(): HasMany
    {
        return $this->hasMany(PaymentWebhookEvent::class);
    }

    private static function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(10));
        } while (static::query()->where('number', $number)->exists());

        return $number;
    }
}
