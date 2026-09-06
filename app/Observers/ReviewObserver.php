<?php

namespace App\Observers;

use App\Models\CourseReview;
use App\Services\HomePageService;
use App\Services\InstructorDashboardService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class ReviewObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InstructorDashboardService $dashboard,
        private readonly HomePageService $homePage,
    ) {}

    public function created(CourseReview $review): void
    {
        $this->clear($review);
    }

    public function updated(CourseReview $review): void
    {
        $this->clear($review);
    }

    public function deleted(CourseReview $review): void
    {
        $this->clear($review);
    }

    private function clear(CourseReview $review): void
    {
        $this->homePage->forgetCache();
        Cache::forget("courses.show.{$review->course_id}");

        $instructorId = $review->course()->value('instructor_id');
        if ($instructorId) {
            $this->dashboard->clearInstructorDashboardCache((int) $instructorId);
        }
    }
}
