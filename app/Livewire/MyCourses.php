<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class MyCourses extends Component
{
    public function thumbnailUrl(?string $path): ?string
    {
        return $path ? Storage::disk(config('lms-upload.disk'))->url($path) : null;
    }

    public function render(): View
    {
        return view('livewire.my-courses', [
            'courses' => Auth::guard('student')->user()
                ->enrolledCourses()
                ->with(['category', 'instructor'])
                ->latest('courses.created_at')
                ->get(),
        ])->layout('layouts.course-public');
    }
}
