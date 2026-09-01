<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'discount_type', 'discount_value', 'max_uses', 'used_count', 'start_date', 'end_date', 'is_active'])]
class Coupon extends Model
{
    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
