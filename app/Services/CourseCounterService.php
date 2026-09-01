<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseCounterService
{
    public function syncContent(Course $course): array
    {
        $content = DB::table('course_videos')
            ->join('sections', 'course_videos.section_id', '=', 'sections.id')
            ->where('sections.course_id', $course->id)
            ->selectRaw('COUNT(course_videos.id) as total_videos')
            ->selectRaw('COALESCE(SUM(course_videos.duration), 0) as total_duration')
            ->first();

        $counters = [
            'number_lessons' => (int) $content->total_videos,
            'duration' => (float) $content->total_duration,
        ];

        $course->forceFill($counters)->saveQuietly();

        return $counters;
    }

    public function syncStudents(Course $course): int
    {
        $count = $course->enrollments()->active()->distinct()->count('user_id');
        $course->forceFill(['students_count' => $count])->saveQuietly();

        return $count;
    }
}
