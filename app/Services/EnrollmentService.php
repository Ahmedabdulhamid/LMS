<?php

namespace App\Services;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnrollmentService
{
    public function grantFree(User $user, Course $course): Enrollment
    {
        if (! $course->isFree()) {
            throw new InvalidArgumentException('Only free courses can create a free enrollment.');
        }

        return DB::transaction(function () use ($user, $course): Enrollment {
            Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();

            $existing = Enrollment::query()
                ->forUser($user)
                ->forCourse($course)
                ->where('source_type', EnrollmentSourceType::Free->value)
                ->active()
                ->first();

            return $existing ?? Enrollment::query()->create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'source_type' => EnrollmentSourceType::Free,
                'source_id' => null,
                'starts_at' => now(),
                'ends_at' => null,
                'status' => EnrollmentStatus::Active,
            ]);
        });
    }

    public function grantFromPurchase(CoursePurchase $purchase): Enrollment
    {
        if ($purchase->payment_status !== 'completed') {
            throw new InvalidArgumentException('Only completed purchases can grant course access.');
        }

        return Enrollment::query()->updateOrCreate(
            [
                'source_type' => EnrollmentSourceType::OneTime->value,
                'source_id' => $purchase->id,
            ],
            [
                'user_id' => $purchase->user_id,
                'course_id' => $purchase->course_id,
                'starts_at' => $purchase->purchased_at ?? $purchase->created_at,
                'ends_at' => null,
                'status' => EnrollmentStatus::Active,
            ],
        );
    }
}
