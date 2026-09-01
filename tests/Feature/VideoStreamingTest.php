<?php

namespace Tests\Feature;

use App\Enums\VideoStatus;
use App\Jobs\ProcessCourseVideo;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\User;
use App\Services\R2FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class VideoStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_student_cannot_read_private_playlists(): void
    {
        [$course, $video] = $this->courseVideo();
        $student = User::factory()->create();

        $this->actingAs($student, 'student')
            ->get(route('course-videos.stream.master', [$course, $video]))
            ->assertForbidden();
        $this->actingAs($student, 'student')
            ->get(route('course-videos.stream.variant', [$course, $video, 720]))
            ->assertForbidden();
    }

    public function test_enrolled_student_receives_rewritten_private_playlists(): void
    {
        [$course, $video] = $this->courseVideo();
        $student = User::factory()->create();
        DB::table('course_purchases')->insert([
            'user_id' => $student->id, 'course_id' => $course->id, 'price' => 20,
            'payment_status' => 'completed', 'purchased_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->mock(R2FileService::class, function (MockInterface $mock) use ($course, $video): void {
            $mock->shouldReceive('get')->with($video->hls_path)
                ->andReturn("#EXTM3U\n#EXT-X-STREAM-INF:BANDWIDTH=3000000,RESOLUTION=1280x720\n720p/index.m3u8\n");
            $mock->shouldReceive('get')->with("courses/{$course->id}/videos/{$video->id}/hls/720p/index.m3u8")
                ->andReturn("#EXTM3U\n#EXTINF:6,\nsegment_00001.ts\n");
            $mock->shouldReceive('hlsTemporaryUrl')->once()->andReturn('https://signed.example/segment.ts?expires=soon');
        });

        $this->actingAs($student, 'student')
            ->get(route('course-videos.stream.master', [$course, $video]))
            ->assertOk()->assertHeader('Content-Type', 'application/vnd.apple.mpegurl')
            ->assertSee(route('course-videos.stream.variant', [$course, $video, 720]), false);
        $this->actingAs($student, 'student')
            ->get(route('course-videos.stream.variant', [$course, $video, 720]))
            ->assertOk()->assertSee('https://signed.example/segment.ts?expires=soon', false)
            ->assertDontSee('segment_00001.ts', false);
    }

    private function courseVideo(): array
    {
        Queue::fake([ProcessCourseVideo::class]);
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'teacher', 'email' => 'teacher@example.test', 'password' => bcrypt('password'),
            'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]', 'skills' => '[]', 'experiences' => '[]',
            'specialization' => '[]', 'achivements' => '[]', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Development', 'slug' => 'development', 'small_description' => 'Courses', 'icon' => 'code',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $course = Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Secure video',
            'description' => 'Description', 'price' => 20, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
        $sectionId = DB::table('sections')->insertGetId(['course_id' => $course->id, 'title' => 'Section', 'created_at' => $now, 'updated_at' => $now]);
        $video = new CourseVideo([
            'title' => 'Lesson', 'url' => "instructors/$instructorId/courses/{$course->id}/videos/source.mp4",
            'status' => VideoStatus::Ready, 'hls_path' => "courses/{$course->id}/videos/1/hls/master.m3u8",
            'duration' => 60, 'is_published' => true,
        ]);
        $video->section_id = $sectionId;
        $video->saveQuietly();
        $video->forceFill(['hls_path' => "courses/{$course->id}/videos/{$video->id}/hls/master.m3u8"])->saveQuietly();

        return [$course, $video->refresh()];
    }
}
