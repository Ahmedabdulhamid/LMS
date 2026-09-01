<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PaymentWebhookEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'encrypted:array',
        'delivery_count' => 'integer',
        'processing_attempts' => 'integer',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('admin.payment-webhook-events.stats'));
        static::deleted(fn (): bool => Cache::forget('admin.payment-webhook-events.stats'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
