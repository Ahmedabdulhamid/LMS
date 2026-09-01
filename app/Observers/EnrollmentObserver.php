<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\CourseCounterService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class EnrollmentObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Enrollment $enrollment): void
    {
        $this->syncCourse($enrollment);
    }

    public function deleted(Enrollment $enrollment): void
    {
        $this->syncCourse($enrollment);
    }

    private function syncCourse(Enrollment $enrollment): void
    {
        Cache::forget("courses.show.{$enrollment->course_id}");

        if ($course = $enrollment->course) {
            app(CourseCounterService::class)->syncStudents($course);
        }
    }
}
