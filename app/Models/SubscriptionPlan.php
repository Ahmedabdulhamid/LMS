<?php

namespace App\Models;

use App\Enums\SubscriptionDurationUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['name', 'description', 'price', 'currency', 'duration_value', 'duration_unit', 'is_active'])]
class SubscriptionPlan extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'duration_value' => 'integer',
        'duration_unit' => SubscriptionDurationUnit::class,
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'plan_course')->withTimestamps();
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'purchasable');
    }
}
