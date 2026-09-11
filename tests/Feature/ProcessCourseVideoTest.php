<?php

namespace Tests\Feature;

use App\Enums\VideoStatus;
use App\Jobs\ProcessCourseVideo;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Services\CalcalateCourseDurationService;
use App\Services\HlsVideoTranscoder;
use App\Services\R2FileService;
use App\Services\VideoMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class ProcessCourseVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_processing_persists_ffprobe_metadata_and_ready_status(): void
    {
        $video = $this->video();
        $storage = $this->mock(R2FileService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('readStream')->once()->andReturnUsing(function () {
                $stream = fopen('php://temp', 'w+b');
                fwrite($stream, 'video');
                rewind($stream);

                return $stream;
            });
            $mock->shouldReceive('uploadHlsDirectory')->once();
        });
        $metadata = $this->mock(VideoMetadataService::class, fn (MockInterface $mock) => $mock->shouldReceive('probe')->once()->andReturn([
            'duration' => 331, 'width' => 1280, 'height' => 720,
        ]));
        $transcoder = $this->mock(HlsVideoTranscoder::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transcode')->once()->andReturnUsing(function (string $input, string $output): array {
                File::ensureDirectoryExists($output.'/720p');
                File::put($output.'/master.m3u8', "#EXTM3U\n720p/index.m3u8\n");
                File::put($output.'/720p/index.m3u8', "#EXTM3U\nsegment_00001.ts\n");
                File::put($output.'/720p/segment_00001.ts', 'segment');

                return [720];
            });
        });
        $duration = $this->mock(CalcalateCourseDurationService::class, fn (MockInterface $mock) => $mock->shouldReceive('calculateCourseDuration')->once()->andReturn([]));

        (new ProcessCourseVideo($video->id))->handle($storage, $metadata, $transcoder, $duration);

        $video->refresh();
        self::assertSame(VideoStatus::Ready, $video->status);
        self::assertSame(331, $video->duration);
        self::assertSame(1280, $video->source_width);
        self::assertSame(720, $video->source_height);
        self::assertSame('ready', $video->processing_stage);
        self::assertSame(100, $video->processing_progress);
        self::assertNotNull($video->processed_at);
    }

    public function test_deletion_during_upload_cleans_late_hls_files(): void
    {
        $video = $this->video();
        Storage::fake('r2_private');
        $segment = "courses/{$video->section->course_id}/videos/{$video->id}/hls/720p/segment_00001.ts";
        $storage = $this->mock(R2FileService::class, function (MockInterface $mock) use ($video, $segment): void {
            $mock->shouldReceive('readStream')->once()->andReturnUsing(function () {
                $stream = fopen('php://temp', 'w+b');
                fwrite($stream, 'video');
                rewind($stream);

                return $stream;
            });
            $mock->shouldReceive('uploadHlsDirectory')->once()->andReturnUsing(function () use ($video, $segment): void {
                DB::table('course_videos')->where('id', $video->id)->delete();
                Storage::disk('r2_private')->put($segment, 'late upload');
            });
        });
        $metadata = $this->mock(VideoMetadataService::class, fn (MockInterface $mock) => $mock->shouldReceive('probe')->once()->andReturn([
            'duration' => 331, 'width' => 1280, 'height' => 720,
        ]));
        $transcoder = $this->mock(HlsVideoTranscoder::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transcode')->once()->andReturnUsing(function (string $input, string $output): array {
                File::ensureDirectoryExists($output.'/720p');
                File::put($output.'/master.m3u8', "#EXTM3U\n720p/index.m3u8\n");
                File::put($output.'/720p/index.m3u8', "#EXTM3U\nsegment_00001.ts\n");
                File::put($output.'/720p/segment_00001.ts', 'segment');

                return [720];
            });
        });
        $duration = $this->mock(CalcalateCourseDurationService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('calculateCourseDuration'));

        (new ProcessCourseVideo($video->id))->handle($storage, $metadata, $transcoder, $duration);

        Storage::disk('r2_private')->assertMissing($segment);
        $this->assertDatabaseMissing('course_videos', ['id' => $video->id]);
    }

    public function test_failed_processing_stores_safe_failure_state(): void
    {
        $video = $this->video();
        $storage = $this->mock(R2FileService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('readStream')->once()->andReturnUsing(function () {
                $stream = fopen('php://temp', 'w+b');
                rewind($stream);

                return $stream;
            });
        });
        $metadata = $this->mock(VideoMetadataService::class, fn (MockInterface $mock) => $mock->shouldReceive('probe')->once()->andThrow(new RuntimeException('C:\\secret\\binary failed')));
        $transcoder = $this->mock(HlsVideoTranscoder::class);
        $duration = $this->mock(CalcalateCourseDurationService::class);

        try {
            (new ProcessCourseVideo($video->id))->handle($storage, $metadata, $transcoder, $duration);
            self::fail('Expected processing to fail.');
        } catch (RuntimeException) {
        }

        $video->refresh();
        self::assertSame(VideoStatus::Failed, $video->status);
        self::assertSame('failed', $video->processing_stage);
        self::assertStringNotContainsString('secret', $video->processing_error);
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
