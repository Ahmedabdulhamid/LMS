<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVideoProgress extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'progress' => 'integer',
        'last_position_seconds' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(CourseVideo::class, 'video_id');
    }
}
