<?php

namespace App\Http\Requests\CourseVideos;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

abstract class CourseVideoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');
        $instructor = $this->user('instructor');

        return $course instanceof Course
            && $instructor !== null
            && $course->instructor_id === $instructor->getAuthIdentifier();
    }
}
