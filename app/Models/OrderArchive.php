<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderArchive extends Model
{
    protected $table = 'order_archive';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'billing_details' => 'array',
            'items' => 'array',
            'payment_transactions' => 'array',
            'payment_webhook_events' => 'encrypted:array',
            'instructor_ids' => 'array',
            'paid_at' => 'datetime',
            'original_created_at' => 'datetime',
            'original_updated_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
