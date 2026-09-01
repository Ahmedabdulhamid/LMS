<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoursePurchase extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (CoursePurchase $purchase): void {
            if ($purchase->payment_status === 'completed' && $purchase->purchased_at === null) {
                $purchase->purchased_at = now();
            }

            if ($purchase->payment_status !== 'completed' && ! $purchase->exists) {
                $purchase->purchased_at = null;
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'purchase_id');
    }
}
