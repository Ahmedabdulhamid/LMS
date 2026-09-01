<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PaymentTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount_cents' => 'integer',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('admin.payment-transactions.stats'));
        static::deleted(fn (): bool => Cache::forget('admin.payment-transactions.stats'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
