<?php

namespace App\Observers;

use App\Models\Section;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class SectionObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Section $section): void
    {
        $this->forgetCourseCache($section);
    }

    public function deleted(Section $section): void
    {
        $this->forgetCourseCache($section);
    }

    private function forgetCourseCache(Section $section): void
    {
        $course = $section->course;

        if ($course === null) {
            return;
        }

        Cache::forget("instructor_courses_{$course->instructor_id}");
        Cache::forget("instructor_{$course->instructor_id}_course_{$course->id}");
        Cache::forget("courses.show.{$course->id}");
    }
}
