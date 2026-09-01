<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseVideo;
use App\Services\CourseAccessService;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowCourse extends Component
{
    public Course $course;

    public ?int $selectedVideoId = null;

    public int $rating = 5;

    public string $comment = '';

    public ?string $notice = null;

    public string $noticeType = 'success';

    public function mount(Course $course, CourseService $courseService): void
    {
        $this->course = $courseService->getPublishedCourseForShow($course);
        $this->selectedVideoId = $this->course->sections->flatMap->videos->firstWhere('is_free', true)?->id;

        if ($student = Auth::guard('student')->user()) {
            $review = $this->course->courseReviews->firstWhere('user_id', $student->id);
            $this->rating = $review?->rating ?? 5;
            $this->comment = $review?->comment ?? '';
        }
    }

    public function selectVideo(int $videoId): void
    {
        $video = $this->course->sections->flatMap->videos->firstWhere('id', $videoId);
        abort_unless($video instanceof CourseVideo, 404);

        if (! app(CourseAccessService::class)->canAccessVideo(Auth::guard('student')->user(), $video)) {
            $this->showNotice(__('lms.course_show.messages.video_locked'), 'warning');

            return;
        }

        $this->selectedVideoId = $video->id;
        $this->dispatch('course-video-changed');
    }

    public function toggleWishlist(CourseService $courseService): void
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            $this->redirectRoute('filament.students.auth.login');

            return;
        }

        $isWishlisted = $courseService->toggleWishlist($this->course, $student);
        $this->course = $courseService->getPublishedCourseForShow($this->course);
        unset($this->isWishlisted);
        $this->dispatch('wishlist-count-updated', count: $student->wishlists()->count());
        $this->showNotice(__('lms.course_show.messages.'.($isWishlisted ? 'wishlist_added' : 'wishlist_removed')));
    }

    public function saveRating(CourseService $courseService): void
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            $this->redirectRoute('filament.students.auth.login');

            return;
        }

        if (! $this->hasPurchased()) {
            $this->showNotice(__('lms.course_show.messages.rating_requires_enrollment'), 'warning');

            return;
        }

        $validated = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        $courseService->saveReview($this->course, $student, $validated);
        $this->course = $courseService->getPublishedCourseForShow($this->course);
        $this->showNotice(__('lms.course_show.messages.rating_saved'));
        $this->dispatch('rating-saved');
    }

    #[Computed]
    public function selectedVideo(): ?CourseVideo
    {
        return $this->course->sections->flatMap->videos->firstWhere('id', $this->selectedVideoId);
    }

    #[Computed]
    public function isWishlisted(): bool
    {
        $studentId = Auth::guard('student')->id();

        return (bool) ($studentId && $this->course->wishlists()->where('user_id', $studentId)->exists());
    }

    #[Computed]
    public function hasPurchased(): bool
    {
        return app(CourseAccessService::class)->canAccessCourse(
            Auth::guard('student')->user(),
            $this->course,
        );
    }

    public function enroll(EnrollmentService $enrollmentService, OrderService $orderService): void
    {
        $student = Auth::guard('student')->user();

        if (! $student) {
            session()->put('url.intended', route('courses.show', $this->course->slug));
            $this->redirectRoute('filament.students.auth.login');

            return;
        }

        if ($this->hasPurchased()) {
            $this->redirectRoute('courses.learn', $this->course);

            return;
        }

        if ($this->course->isFree()) {
            $enrollmentService->grantFree($student, $this->course);
            unset($this->hasPurchased);
            $this->redirectRoute('courses.learn', $this->course);

            return;
        }

        $order = $orderService->createCourseOrder($student, $this->course);
        $this->redirectRoute('checkout.orders.show', ['order' => $order]);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->course->thumbnail ? Storage::disk(config('lms-upload.disk'))->url($this->course->thumbnail) : null;
    }

    public function render(): View
    {
        return view('livewire.show-course')->layout('layouts.course-public');
    }

    private function showNotice(string $message, string $type = 'success'): void
    {
        $this->notice = $message;
        $this->noticeType = $type;
    }
}
