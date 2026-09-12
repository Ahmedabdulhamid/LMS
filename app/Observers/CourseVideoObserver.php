<?php

namespace App\Observers;

use App\Jobs\DeleteMuxVideo;
use App\Models\CourseVideo;
use App\Models\Section;
use App\Services\MuxVideoLifecycle;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CourseVideoObserver implements ShouldHandleEventsAfterCommit
{
    public function created(CourseVideo $video): void
    {
        app(MuxVideoLifecycle::class)->queue($video, true);
    }

    public function updated(CourseVideo $video): void
    {
        if ($video->wasChanged('url')) {
            // Original R2 masters are retained, including replaced source objects.
            app(MuxVideoLifecycle::class)->queue($video, true);
        }
    }

    public function deleted(CourseVideo $video): void
    {
        if ($video->mux_asset_id) {
            DeleteMuxVideo::dispatch($video->mux_asset_id)->afterCommit();
        }
        $this->deleteStoredFile($video->url);
        $this->deleteHlsDirectory($video->hls_path);
        if ($video->section && ($video->url || $video->hls_path)) {
            Storage::disk(config('filesystems.uploads', 'r2_private'))->deleteDirectory(
                sprintf('courses/%d/videos/%d/hls', $video->section->course_id, $video->id),
            );
        }
        foreach ($video->attachments as $attachment) {
            if (filled($attachment->file_path)) {
                Storage::disk(config('lms-upload.disk'))->delete($attachment->file_path);
            }
        }
        $this->forgetShowCache($video);
    }

    public function saved(CourseVideo $video): void
    {
        $this->forgetShowCache($video);
    }

    private function deleteStoredFile(?string $path): void
    {
        if (filled($path)) {
            Storage::disk(config('filesystems.uploads', 'r2_private'))->delete($path);
        }
    }

    private function deleteHlsDirectory(?string $path): void
    {
        if (! filled($path) || ! preg_match('#^courses/\d+/videos/\d+/hls/master\.m3u8$#D', $path)) {
            return;
        }

        Storage::disk(config('filesystems.uploads', 'r2_private'))->deleteDirectory(dirname($path));
    }

    private function forgetShowCache(CourseVideo $video): void
    {
        $sectionIds = array_unique(array_filter([
            $video->section_id,
            $video->getOriginal('section_id'),
        ]));

        Section::query()
            ->whereKey($sectionIds)
            ->pluck('course_id')
            ->each(function (int $courseId): void {
                Cache::forget("courses.show.{$courseId}");
                Cache::forget("course_{$courseId}");
            });
    }
}
