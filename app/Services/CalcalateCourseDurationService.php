<?php

namespace App\Services;

use App\Models\Course;

class CalcalateCourseDurationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CourseCounterService $courseCounterService) {}

    public function calculateCourseDuration(Course $course): array
    {
        $counters = $this->courseCounterService->syncContent($course);

        return [
            'total_videos' => $counters['number_lessons'],
            'total_duration' => $counters['duration'],
        ];
    }
}
