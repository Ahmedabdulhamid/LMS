<?php

namespace App\Observers;

use App\Enums\VideoStatus;
use App\Jobs\ProcessCourseVideo;
use App\Models\CourseVideo;
use App\Models\Section;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CourseVideoObserver implements ShouldHandleEventsAfterCommit
{
    public function created(CourseVideo $video): void
    {
        $this->dispatchDurationJob($video);
    }

    public function updated(CourseVideo $video): void
    {
        if ($video->wasChanged('url')) {
            $this->deleteStoredFile($video->getOriginal('url'));
            $this->deleteHlsDirectory(
                $video->getOriginal('hls_path')
            );
            $this->dispatchDurationJob($video);
        } elseif ($video->wasChanged('hls_path')) {
            $this->deleteHlsDirectory($video->getOriginal('hls_path'));
        }
    }

    public function deleted(CourseVideo $video): void
    {
        $this->deleteStoredFile($video->url);
        $this->deleteHlsDirectory($video->hls_path);
        if ($video->section) {
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

    private function dispatchDurationJob(CourseVideo $video): void
    {
        if (filled($video->url)) {
            $video->forceFill([
                'status' => VideoStatus::Processing,
                'hls_path' => null,
                'processing_stage' => 'queued',
                'processing_progress' => 0,
                'processing_error' => null,
                'processing_started_at' => null,
                'processed_at' => null,
            ])->saveQuietly();
            ProcessCourseVideo::dispatch($video->id)->afterCommit();
        }
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
            ->each(fn (int $courseId) => Cache::forget("courses.show.{$courseId}"));
    }
}
