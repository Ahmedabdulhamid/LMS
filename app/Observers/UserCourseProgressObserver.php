<?php

namespace App\Observers;

use App\Models\UserCourseProgress;
use App\Services\InstructorDashboardService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserCourseProgressObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly InstructorDashboardService $dashboard) {}

    public function saved(UserCourseProgress $progress): void
    {
        $this->clear($progress);
    }

    public function deleted(UserCourseProgress $progress): void
    {
        $this->clear($progress);
    }

    private function clear(UserCourseProgress $progress): void
    {
        $instructorId = $progress->course()->value('instructor_id');

        if ($instructorId) {
            $this->dashboard->clearInstructorDashboardCache((int) $instructorId);
        }
    }
}
