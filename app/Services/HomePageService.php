<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomePageService
{
    private const CACHE_TTL_MINUTES = 10;

    private const CACHE_KEYS = [
        'home:categories:limit:6:v2',
        'home:categories:all:v2',
        'home:courses:top:v2',
        'home:courses:top:limit:v2',
        'home:courses:latest:v2',
        'home:courses:latest:limit:v2',
        'home:reviews:limit:v2',
    ];

    public function forgetCache(): void
    {
        foreach (self::CACHE_KEYS as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    public function getLimitCategories(): Collection
    {
        return collect(Cache::remember(
            'home:categories:limit:6:v2',
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => Category::query()
                ->select('id', 'name', 'icon')
                ->withCount(['courses' => fn ($query) => $query->where('is_published', true)])
                ->latest()
                ->limit(6)
                ->get()
                ->toArray()
        ));
    }

    public function getTopRatedCourses(): Collection
    {
        return $this->getCourses('home:courses:top:v2');
    }

    public function getAllCategories(): Collection
    {
        return collect(Cache::remember(
            'home:categories:all:v2',
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => Category::query()
                ->select('id', 'name', 'icon')
                ->withCount(['courses' => fn ($query) => $query->where('is_published', true)])
                ->latest()
                ->get()
                ->toArray()
        ));
    }

    public function getLimitTopRatedCourses(): Collection
    {
        return $this->getCourses('home:courses:top:limit:v2', 6, true);
    }

    public function getAllLatestCourses(): Collection
    {
        return $this->getCourses('home:courses:latest:v2');

    }

    public function getLimitLatestCourses(): Collection
    {
        return $this->getCourses('home:courses:latest:limit:v2', 6);

    }

    public function getLimitCourseReviews(): Collection
    {
        return collect(Cache::remember('home:reviews:limit:v2', now()->addMinutes(self::CACHE_TTL_MINUTES), fn (): array => CourseReview::query()
            ->with(['user:id,name,image', 'course:id,title'])
            ->where('rating', '>=', 3)
            ->whereNotNull('comment')
            ->select('id', 'user_id', 'course_id', 'comment', 'rating')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray()));
    }

    private function getCourses(string $cacheKey, ?int $limit = null, bool $topRated = false): Collection
    {
        $courses = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($limit, $topRated): array {
            $query = Course::query()
                ->where('is_published', true)
                ->select('id', 'instructor_id', 'category_id', 'slug', 'duration', 'title', 'price', 'price_after_discount', 'thumbnail', 'lang', 'level')
                ->with(['instructor:id,name', 'category:id,name'])
                ->withCount([
                    'enrollments as students_count' => fn ($query) => $query->active()->distinct('user_id'),
                ])
                ->withAvg([
                    'courseReviews' => fn ($query) => $query->where('is_approved', true),
                ], 'rating');

            $topRated
                ? $query->whereHas('courseReviews')->orderByDesc('course_reviews_avg_rating')
                : $query->latest();

            if ($limit !== null) {
                $query->limit($limit);
            }

            return $query->get()->toArray();
        });

        return collect($courses);
    }
}
