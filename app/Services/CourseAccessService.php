<?php

namespace App\Services;

use App\Enums\EnrollmentSourceType;
use App\Enums\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\Instructor;
use App\Models\User;

class CourseAccessService
{
    public function canAccessCourse(?User $student, Course $course): bool
    {
        if ($student === null) {
            return false;
        }

        if ($course->enrollments()
            ->forUser($student)
            ->active()
            ->where(function ($query): void {
                $query
                    ->where('source_type', '!=', EnrollmentSourceType::Subscription->value)
                    ->orWhereExists(function ($subscriptionQuery): void {
                        $subscriptionQuery
                            ->selectRaw('1')
                            ->from('subscriptions')
                            ->whereColumn('subscriptions.id', 'enrollments.source_id')
                            ->where('subscriptions.status', SubscriptionStatus::Active->value)
                            ->where('subscriptions.starts_at', '<=', now())
                            ->where(fn ($query) => $query
                                ->whereNull('subscriptions.ends_at')
                                ->orWhere('subscriptions.ends_at', '>', now()));
                    });
            })
            ->exists()) {
            return true;
        }

        return $course->purchases()
            ->where('user_id', $student->getAuthIdentifier())
            ->where('payment_status', 'completed')
            ->exists();
    }

    public function canAccessVideo(?User $student, CourseVideo $video): bool
    {
        $course = $video->section?->course;

        if (! $course || ! $course->is_published || ! $video->is_published) {
            return false;
        }

        return $video->is_free || $this->canAccessCourse($student, $course);
    }

    public function canManageCourse(?Admin $admin, ?Instructor $instructor, Course $course): bool
    {
        return $admin !== null
            || ($instructor !== null && (int) $course->instructor_id === (int) $instructor->getAuthIdentifier());
    }
}
