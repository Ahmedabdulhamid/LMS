<?php

namespace App\Filament\Student\Widgets;

use App\Models\Course;
use App\Models\User;
use App\Models\UserCourseProgress;
use App\Models\UserVideoProgress;
use Filament\Widgets\Widget;

class ContinueLearning extends Widget
{
    protected string $view = 'student-continue-learning';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        /** @var User $student */
        $student = auth('student')->user();
        $courseIds = $student->enrolledCourses()->pluck('courses.id');
        $courseProgress = UserCourseProgress::query()
            ->where('user_id', $student->id)
            ->whereIn('course_id', $courseIds)
            ->with('course.instructor')
            ->latest('last_accessed_at')
            ->first();
        $course = $courseProgress?->course;

        if (! $course && $courseIds->isNotEmpty()) {
            $course = Course::query()->with('instructor')->find($courseIds->first());
        }

        $lastLesson = $course
            ? UserVideoProgress::query()
                ->where('user_id', $student->id)
                ->whereHas('video.section', fn ($query) => $query->where('course_id', $course->id))
                ->with('video')
                ->latest('completed_at')
                ->first()?->video
            : null;

        return [
            'student' => $student,
            'course' => $course,
            'lastLesson' => $lastLesson,
            'progress' => (int) ($courseProgress?->progress ?? 0),
        ];
    }
}
