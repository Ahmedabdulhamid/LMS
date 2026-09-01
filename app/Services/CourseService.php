<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CourseService
{
    private const SHOW_CACHE_TTL = 1800;

    public function __construct(
        private readonly GoalService $goalService,
        private readonly RequirementService $requirementService,
        private readonly SectionService $sectionService,
    ) {}

    public function getCoursesByInstructor(Instructor $instructor): Collection
    {
        return Cache::rememberForever("instructor_courses_{$instructor->id}", function () use ($instructor) {
            return $instructor->courses()
                ->with(['category', 'goals', 'requirements', 'sections'])
                ->get();
        });
    }

    public function getCourseById(Instructor $instructor, Course $course): Course
    {
        return Cache::rememberForever(
            "instructor_{$instructor->id}_course_{$course->id}",
            fn () => $instructor->courses()
                ->with(['category', 'goals', 'requirements', 'sections'])
                ->findOrFail($course->id),
        );
    }

    public function getPublishedCourseForShow(Course $course): Course
    {
        return Cache::remember(
            $this->showCacheKey($course),
            self::SHOW_CACHE_TTL,
            fn () => Course::query()
                ->whereKey($course->id)
                ->where('is_published', true)
                ->with([
                    'instructor', 'category', 'goals', 'requirements',
                    'sections.videos.attachments', 'courseReviews.user',
                ])
                ->withCount([
                    'enrollments as students_count' => fn ($query) => $query->active()->distinct('user_id'),
                    'wishlists', 'courseReviews',
                ])
                ->withAvg([
                    'courseReviews' => fn ($query) => $query->where('is_approved', true),
                ], 'rating')
                ->firstOrFail(),
        );
    }

    public function toggleWishlist(Course $course, User $student): bool
    {
        $wishlist = $course->wishlists()->where('user_id', $student->id)->first();

        $wishlist
            ? $wishlist->delete()
            : $course->wishlists()->create(['user_id' => $student->id]);

        $this->forgetShowCache($course);

        return $wishlist === null;
    }

    public function saveReview(Course $course, User $student, array $data): void
    {
        CourseReview::query()->updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $student->id],
            $data + ['is_approved' => true],
        );

        $this->forgetShowCache($course);
    }

    public function forgetShowCache(Course $course): void
    {
        Cache::forget($this->showCacheKey($course));
    }

    private function showCacheKey(Course $course): string
    {
        return "courses.show.{$course->id}";
    }

    public function createCourse(Instructor $instructor, array $data): Course
    {
        $course = DB::transaction(function () use ($instructor, $data) {
            $course = $instructor->courses()->create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'price_after_discount' => $data['price_after_discount'] ?? null,
                'lang' => $data['lang'],
                'thumbnail' => $data['thumbnail'] ?? null,
                'level' => $data['level'],
                'is_published' => $data['is_published'] ?? false,
            ]);

            if (isset($data['goals']) && is_array($data['goals'])) {
                $this->goalService->syncGoals($course, $data['goals']);
            }

            if (isset($data['requirements']) && is_array($data['requirements'])) {
                $this->requirementService->syncRequirements($course, $data['requirements']);
            }

            if (isset($data['sections']) && is_array($data['sections'])) {
                $this->sectionService->syncSections($course, $data['sections']);
            }

            return $course;
        });

        return $course;
    }

    public function updateCourse(Instructor $instructor, Course $course, array $data): Course
    {
        $course = DB::transaction(function () use ($instructor, $course, $data) {
            $course = $instructor->courses()->findOrFail($course->id);

            $course->update(Arr::only($data, [
                'category_id',
                'title',
                'description',
                'price',
                'price_after_discount',
                'lang',
                'thumbnail',
                'level',
                'is_published',
            ]));

            if (array_key_exists('goals', $data) && is_array($data['goals'])) {
                $this->goalService->syncGoals($course, $data['goals']);
            }

            if (array_key_exists('requirements', $data) && is_array($data['requirements'])) {
                $this->requirementService->syncRequirements($course, $data['requirements']);
            }

            if (array_key_exists('sections', $data) && is_array($data['sections'])) {
                $this->sectionService->syncSections($course, $data['sections']);
            }

            return $course->refresh();
        });

        Cache::forget("instructor_courses_{$instructor->id}");
        Cache::forget("instructor_{$instructor->id}_course_{$course->id}");
        $this->forgetShowCache($course);

        return $course->load(['category', 'goals', 'requirements', 'sections']);
    }
}
