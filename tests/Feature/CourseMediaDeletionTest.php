<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseMediaDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_deletion_removes_source_and_partial_hls_after_commit(): void
    {
        Storage::fake('r2_private');
        Storage::fake('r2_public');
        $video = $this->video();
        $course = $video->section->course;
        $segment = "courses/{$course->id}/videos/{$video->id}/hls/720p/segment_00001.ts";
        Storage::disk('r2_private')->put($video->url, 'source');
        Storage::disk('r2_private')->put($segment, 'segment');
        Storage::disk('r2_private')->put('courses/999/videos/999/hls/master.m3u8', 'other');
        DB::transaction(function () use ($course, $video, $segment): void {
            $course->delete();
            Storage::disk('r2_private')->assertExists($video->url);
            Storage::disk('r2_private')->assertExists($segment);
        });
        Storage::disk('r2_private')->assertMissing($video->url);
        Storage::disk('r2_private')->assertMissing($segment);
        Storage::disk('r2_private')->assertExists('courses/999/videos/999/hls/master.m3u8');
        $this->assertDatabaseMissing('course_videos', ['id' => $video->id]);
    }

    public function test_video_deletion_removes_all_hls_renditions(): void
    {
        Storage::fake('r2_private');
        Storage::fake('r2_public');
        $video = $this->video();
        $base = "courses/{$video->section->course_id}/videos/{$video->id}/hls";
        $video->forceFill(['hls_path' => "$base/master.m3u8"])->saveQuietly();
        $paths = [$video->url, "$base/master.m3u8", "$base/360p/index.m3u8", "$base/360p/segment_00001.ts", "$base/720p/segment_00001.ts"];
        foreach ($paths as $path) {
            Storage::disk('r2_private')->put($path, 'data');
        }
        $video->delete();
        foreach ($paths as $path) {
            Storage::disk('r2_private')->assertMissing($path);
        }
    }

    public function test_rollback_preserves_video_files(): void
    {
        Storage::fake('r2_private');
        $video = $this->video();
        Storage::disk('r2_private')->put($video->url, 'source');
        DB::beginTransaction();
        $video->section->course->delete();
        DB::rollBack();
        Storage::disk('r2_private')->assertExists($video->url);
        $this->assertDatabaseHas('course_videos', ['id' => $video->id]);
    }

    private function video(): CourseVideo
    {
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => uniqid('teacher-'), 'email' => uniqid().'@example.test', 'password' => bcrypt('password'),
            'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]', 'skills' => '[]', 'experiences' => '[]',
            'specialization' => '[]', 'achivements' => '[]', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => uniqid('Category '), 'slug' => uniqid('category-'), 'small_description' => 'Courses', 'icon' => 'code',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $course = Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => uniqid('Course '),
            'description' => 'Description', 'price' => 20, 'lang' => 'en', 'level' => 'beginner',
        ]);
        $sectionId = DB::table('sections')->insertGetId(['course_id' => $course->id, 'title' => 'Section', 'created_at' => $now, 'updated_at' => $now]);
        $video = new CourseVideo(['title' => 'Lesson', 'url' => "instructors/$instructorId/courses/{$course->id}/videos/source.mp4"]);
        $video->section_id = $sectionId;
        $video->saveQuietly();

        return $video;
    }
}
