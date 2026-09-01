<?php

namespace App\Observers;

use App\Models\CourseRequirement;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class CourseRequirementObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(CourseRequirement $requirement): void
    {
        $this->forgetCourseCache($requirement);
    }

    public function deleted(CourseRequirement $requirement): void
    {
        $this->forgetCourseCache($requirement);

    }

    private function forgetCourseCache(CourseRequirement $requirement): void
    {
        $course = $requirement->course;

        if ($course === null) {
            return;
        }

        Cache::forget("instructor_courses_{$course->instructor_id}");
        Cache::forget("instructor_{$course->instructor_id}_course_{$course->id}");
        Cache::forget("courses.show.{$course->id}");
    }
}
