<?php

namespace App\Observers;

use App\Models\CourseReview;
use App\Services\HomePageService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class CourseReviewObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(CourseReview $courseReview): void
    {
        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($courseReview);
    }

    public function deleted(CourseReview $courseReview): void
    {
        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($courseReview);
    }

    public function restored(CourseReview $courseReview): void
    {
        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($courseReview);
    }

    private function forgetShowCache(CourseReview $courseReview): void
    {
        Cache::forget("courses.show.{$courseReview->course_id}");
    }
}
