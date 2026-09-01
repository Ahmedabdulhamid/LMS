<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseVideo;
use Illuminate\Http\Request;

class CourseVideoAccessService
{
    public function __construct(private readonly CourseAccessService $courseAccess) {}

    public function authorizeViewing(Request $request, Course $course, CourseVideo $video): void
    {
        abort_unless($video->section()->where('course_id', $course->id)->exists(), 404);

        $admin = $request->user('admin');
        $instructor = $request->user('instructor');
        $student = $request->user('student');

        abort_unless(
            $this->courseAccess->canManageCourse($admin, $instructor, $course)
                || $this->courseAccess->canAccessVideo($student, $video),
            403,
        );
    }
}
