<?php

namespace App\Observers;

use App\Models\CourseVideo;
use App\Models\VideoAttachment;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class VideoAttachmentObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(VideoAttachment $attachment): void
    {
        $this->forgetShowCache($attachment);
    }

    public function deleted(VideoAttachment $attachment): void
    {
        $this->forgetShowCache($attachment);
    }

    private function forgetShowCache(VideoAttachment $attachment): void
    {
        $videoIds = array_unique(array_filter([
            $attachment->video_id,
            $attachment->getOriginal('video_id'),
        ]));

        CourseVideo::query()
            ->whereKey($videoIds)
            ->with('section:id,course_id')
            ->get()
            ->pluck('section.course_id')
            ->filter()
            ->unique()
            ->each(fn (int $courseId) => Cache::forget("courses.show.{$courseId}"));
    }
}
