<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseVideo;
use App\Services\CourseAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LearnCourse extends Component
{
    public Course $course;

    public ?int $selectedVideoId = null;

    public bool $autoplaySelectedVideo = false;

    public function mount(Course $course, CourseAccessService $courseAccess): void
    {
        $course = Course::query()
            ->whereKey($course->getKey())
            ->where('is_published', true)
            ->with([
                'instructor',
                'sections' => fn ($query) => $query->orderBy('order'),
                'sections.videos' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('order'),
                'sections.videos.attachments',
            ])
            ->firstOrFail();

        abort_unless(
            $courseAccess->canAccessCourse(Auth::guard('student')->user(), $course),
            403,
        );

        $this->course = $course;
        $this->selectedVideoId = $course->sections->flatMap->videos->first()?->id;
    }

    public function selectVideo(int $videoId, CourseAccessService $courseAccess): void
    {
        $video = $this->course->sections->flatMap->videos->firstWhere('id', $videoId);
        abort_unless($video instanceof CourseVideo, 404);
        abort_unless($courseAccess->canAccessVideo(Auth::guard('student')->user(), $video), 403);

        $this->selectedVideoId = $video->id;
        $this->autoplaySelectedVideo = true;
        unset($this->selectedVideo);
        $this->dispatch('course-video-changed');
    }

    #[Computed]
    public function selectedVideo(): ?CourseVideo
    {
        return $this->course->sections->flatMap->videos->firstWhere('id', $this->selectedVideoId);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->course->thumbnail
            ? Storage::disk(config('lms-upload.disk'))->url($this->course->thumbnail)
            : null;
    }

    public function render(): View
    {
        return view('livewire.learn-course')->layout('layouts.course-public');
    }
}
