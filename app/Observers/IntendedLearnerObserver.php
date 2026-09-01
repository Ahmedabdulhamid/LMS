<?php

namespace App\Observers;

use App\Models\IntendedLearner;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class IntendedLearnerObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(IntendedLearner $intendedLearner): void
    {
        $this->forgetCourseCache($intendedLearner);
    }

    public function deleted(IntendedLearner $intendedLearner): void
    {
        $this->forgetCourseCache($intendedLearner);
    }

    private function forgetCourseCache(IntendedLearner $intendedLearner): void
    {
        $course = $intendedLearner->course;

        if ($course === null) {
            return;
        }

        Cache::forget("instructor_courses_{$course->instructor_id}");
        Cache::forget("instructor_{$course->instructor_id}_course_{$course->id}");
        Cache::forget("courses.show.{$course->id}");
    }
}
