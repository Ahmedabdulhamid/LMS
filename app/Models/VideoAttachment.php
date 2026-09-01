<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['video_id', 'file_name', 'file_path', 'file_size'])]
class VideoAttachment extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (VideoAttachment $attachment) {
            $disk = config('filesystems.uploads', 'r2');

            if ($attachment->file_path && Storage::disk($disk)->exists($attachment->file_path)) {
                Storage::disk($disk)->delete($attachment->file_path);
            }
        });
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(CourseVideo::class);
    }
}
