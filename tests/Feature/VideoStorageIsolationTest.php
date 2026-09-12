<?php

namespace Tests\Feature;

use App\Observers\CourseVideoObserver;
use App\Services\R2FileService;
use App\Services\R2VideoUploadService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoStorageIsolationTest extends TestCase
{
    public function test_original_urls_are_signed_by_private_storage_and_images_use_public_urls(): void
    {
        Storage::fake('r2_public');
        Storage::fake('r2_private');
        config(['filesystems.disks.r2_public.url' => 'https://images.example.test']);
        $path = 'instructors/1/courses/1/videos/master.mp4';
        Storage::disk('r2_private')->buildTemporaryUrlsUsing(fn ($key, $expires, $options) => 'https://private.example.test/'.$key);
        $service = app(R2FileService::class);
        $this->assertSame('https://private.example.test/'.$path, $service->temporaryUrl($path));
        $this->assertSame('https://images.example.test/courses/cover.jpg', $service->publicUrl('courses/cover.jpg'));
    }

    public function test_video_cleanup_leaves_public_objects_untouched(): void
    {
        Storage::fake('r2_public');
        Storage::fake('r2_private');
        $source = 'instructors/1/courses/1/videos/source.mp4';
        $master = 'courses/1/videos/2/hls/master.m3u8';
        $segment = 'courses/1/videos/2/hls/720p/segment_00001.ts';
        foreach ([$source, $master, $segment] as $path) {
            Storage::disk('r2_public')->put($path, 'public');
            Storage::disk('r2_private')->put($path, 'private');
        }

        // Exercise cleanup directly without dispatching a processing job.
        $observer = new CourseVideoObserver;
        (new \ReflectionMethod($observer, 'deleteStoredFile'))->invoke($observer, $source);
        (new \ReflectionMethod($observer, 'deleteHlsDirectory'))->invoke($observer, $master);

        foreach ([$source, $master, $segment] as $path) {
            Storage::disk('r2_private')->assertMissing($path);
            Storage::disk('r2_public')->assertExists($path);
        }
    }

    public function test_upload_service_uses_separate_disks_for_videos_and_attachments(): void
    {
        Storage::fake('r2_public');
        Storage::fake('r2_private');
        $video = 'instructors/1/courses/1/videos/source.mp4';
        $attachment = 'instructors/1/courses/1/attachments/notes.pdf';
        foreach (['r2_public', 'r2_private'] as $disk) {
            Storage::disk($disk)->put($video, 'video');
            Storage::disk($disk)->put($attachment, 'notes');
        }

        $service = app(R2VideoUploadService::class);
        $service->delete($video);
        $service->delete($attachment);

        Storage::disk('r2_private')->assertMissing($video);
        Storage::disk('r2_public')->assertExists($video);
        Storage::disk('r2_public')->assertMissing($attachment);
        Storage::disk('r2_private')->assertExists($attachment);
    }
}
