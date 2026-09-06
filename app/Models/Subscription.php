<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'subscription_plan_id', 'order_id', 'starts_at', 'ends_at', 'status'])]
class Subscription extends Model
{
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'expiry_warning_for' => 'datetime',
        'status' => SubscriptionStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
