<?php

namespace App\Observers;

use App\Models\CoursePurchase;
use App\Services\InstructorDashboardService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PaymentObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly InstructorDashboardService $dashboard) {}

    public function created(CoursePurchase $payment): void
    {
        $this->clear($payment);
    }

    public function updated(CoursePurchase $payment): void
    {
        $this->clear($payment);
    }

    public function deleted(CoursePurchase $payment): void
    {
        $this->clear($payment);
    }

    private function clear(CoursePurchase $payment): void
    {
        $instructorId = $payment->course()->value('instructor_id');

        if ($instructorId) {
            $this->dashboard->clearInstructorDashboardCache((int) $instructorId);
        }
    }
}
