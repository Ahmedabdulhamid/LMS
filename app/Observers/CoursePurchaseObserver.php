<?php

namespace App\Observers;

use App\Models\CoursePurchase;
use App\Services\CourseCounterService;
use App\Services\EnrollmentService;
use App\Services\HomePageService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class CoursePurchaseObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(CoursePurchase $coursePurchase): void
    {
        if ($coursePurchase->payment_status === 'completed') {
            app(EnrollmentService::class)->grantFromPurchase($coursePurchase);
        }

        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($coursePurchase);
    }

    public function deleted(CoursePurchase $coursePurchase): void
    {
        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($coursePurchase);
    }

    public function restored(CoursePurchase $coursePurchase): void
    {
        app(HomePageService::class)->forgetCache();
        $this->forgetShowCache($coursePurchase);
    }

    private function forgetShowCache(CoursePurchase $coursePurchase): void
    {
        Cache::forget("courses.show.{$coursePurchase->course_id}");

        if ($course = $coursePurchase->course) {
            app(CourseCounterService::class)->syncStudents($course);
        }
    }
}
