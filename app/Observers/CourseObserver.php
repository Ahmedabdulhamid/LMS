<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\HomePageService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CourseObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Course $course): void
    {
        if ($course->isDirty('thumbnail')) {
            // Delete the old icon file if it exists
            $oldThumbnailPath = $course->getOriginal('thumbnail');
            if ($oldThumbnailPath && Storage::disk(config('lms-upload.disk'))->exists($oldThumbnailPath)) {
                Storage::disk(config('lms-upload.disk'))->delete($oldThumbnailPath);
            }
        }

    }

    public function saved(Course $course): void
    {
        $this->forget($course->instructor_id, $course->id);
        app(HomePageService::class)->forgetCache();

        $originalInstructorId = $course->getOriginal('instructor_id');

        if ($originalInstructorId && $originalInstructorId !== $course->instructor_id) {
            $this->forget($originalInstructorId, $course->id);
        }
    }

    public function deleted(Course $course): void
    {
        $this->forget($course->instructor_id, $course->id);
        app(HomePageService::class)->forgetCache();
        if ($course->thumbnail && Storage::disk(config('lms-upload.disk'))->exists($course->thumbnail)) {
            Storage::disk(config('lms-upload.disk'))->delete($course->thumbnail);
        }
    }

    private function forget(int $instructorId, int $courseId): void
    {
        Cache::forget("instructor_courses_{$instructorId}");
        Cache::forget("instructor_{$instructorId}_course_{$courseId}");
        Cache::forget("courses.show.{$courseId}");
    }
}
