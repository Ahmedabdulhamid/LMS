<?php

namespace App\Models;

use App\Enums\VideoStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['section_id', 'title', 'description', 'url', 'status', 'hls_path', 'duration', 'source_width', 'source_height', 'processing_stage', 'processing_progress', 'processing_error', 'processing_started_at', 'processed_at', 'order', 'is_free', 'is_published'])]
class CourseVideo extends Model
{
    protected $casts = [
        'duration' => 'integer',
        'status' => VideoStatus::class,
        'source_width' => 'integer',
        'source_height' => 'integer',
        'processed_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processing_progress' => 'integer',
        'order' => 'integer',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (CourseVideo $video): void {
            // Retain storage ownership and attachment keys before database cascades.
            $video->loadMissing(['section', 'attachments']);
        });
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(VideoAttachment::class, 'video_id');
    }
}
