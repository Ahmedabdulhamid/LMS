<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Cache;

class InstructorDashboardService
{
    private const CACHE_MINUTES = 15;

    /** @return array{total_courses: int, total_students: int, total_revenue: float, average_rating: float} */
    public function getStatistics(int $instructorId): array
    {
        return Cache::remember(
            $this->statsCacheKey($instructorId),
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): array => [
                'total_courses' => Course::query()->where('instructor_id', $instructorId)->count(),
                'total_students' => Enrollment::query()
                    ->active()
                    ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructorId))
                    ->distinct()
                    ->count('user_id'),
                'total_revenue' => (float) CoursePurchase::query()
                    ->where('payment_status', 'completed')
                    ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructorId))
                    ->sum('price'),
                'average_rating' => $this->getAverageRating($instructorId),
            ],
        );
    }

    /** @return array<int, array{id: int, title: string, students_count: int, completion_percentage: float, average_rating: float}> */
    public function getCoursePerformance(int $instructorId): array
    {
        return Cache::remember(
            $this->coursesCacheKey($instructorId),
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): array => Course::query()
                ->where('instructor_id', $instructorId)
                ->withCount(['enrollments as students_count' => fn ($query) => $query->active()])
                ->withAvg('progress as completion_percentage', 'progress')
                ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
                ->orderByDesc('students_count')
                ->get()
                ->map(fn (Course $course): array => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'students_count' => (int) $course->students_count,
                    'completion_percentage' => round((float) ($course->completion_percentage ?? 0), 1),
                    'average_rating' => round((float) ($course->average_rating ?? 0), 1),
                ])
                ->all(),
        );
    }

    /** @return array<int, array{id: int, student_name: string, course_name: string, enrollment_date: string}> */
    public function getRecentEnrollments(int $instructorId, int $limit = 10): array
    {
        return Cache::remember(
            $this->studentsCacheKey($instructorId),
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): array => Enrollment::query()
                ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructorId))
                ->with(['user:id,name', 'course:id,title'])
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Enrollment $enrollment): array => [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->user->name,
                    'course_name' => $enrollment->course->title,
                    'enrollment_date' => $enrollment->created_at->toDateTimeString(),
                ])
                ->all(),
        );
    }

    public function clearInstructorDashboardCache(int $instructorId): void
    {
        foreach ($this->cacheKeys($instructorId) as $key) {
            Cache::forget($key);
        }
    }

    /** @return array<int, string> */
    public function cacheKeys(int $instructorId): array
    {
        return [
            $this->statsCacheKey($instructorId),
            $this->coursesCacheKey($instructorId),
            $this->studentsCacheKey($instructorId),
            $this->reviewsCacheKey($instructorId),
        ];
    }

    public function statsCacheKey(int $instructorId): string
    {
        return "instructor:{$instructorId}:dashboard:stats";
    }

    public function coursesCacheKey(int $instructorId): string
    {
        return "instructor:{$instructorId}:dashboard:courses";
    }

    public function studentsCacheKey(int $instructorId): string
    {
        return "instructor:{$instructorId}:dashboard:students";
    }

    public function reviewsCacheKey(int $instructorId): string
    {
        return "instructor:{$instructorId}:dashboard:reviews";
    }

    private function getAverageRating(int $instructorId): float
    {
        return Cache::remember(
            $this->reviewsCacheKey($instructorId),
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): float => round((float) Course::query()
                ->where('instructor_id', $instructorId)
                ->join('course_reviews', 'courses.id', '=', 'course_reviews.course_id')
                ->where('course_reviews.is_approved', true)
                ->avg('course_reviews.rating'), 1),
        );
    }
}
