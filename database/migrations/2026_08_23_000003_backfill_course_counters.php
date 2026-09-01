<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('courses')->select('id')->orderBy('id')->chunkById(100, function ($courses): void {
            foreach ($courses as $course) {
                $content = DB::table('course_videos')
                    ->join('sections', 'course_videos.section_id', '=', 'sections.id')
                    ->where('sections.course_id', $course->id)
                    ->selectRaw('COUNT(course_videos.id) as lessons')
                    ->selectRaw('COALESCE(SUM(course_videos.duration), 0) as duration')
                    ->first();

                DB::table('courses')->where('id', $course->id)->update([
                    'number_lessons' => (int) $content->lessons,
                    'duration' => (float) $content->duration,
                    'students_count' => DB::table('course_purchases')
                        ->where('course_id', $course->id)
                        ->where('payment_status', 'completed')
                        ->count(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Counter backfills are intentionally not reversed.
    }
};
