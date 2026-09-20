<?php

namespace Tests\Feature;

use App\Enums\VideoStatus;
use App\Jobs\DeleteMuxVideo;
use App\Jobs\ProcessCourseVideo;
use App\Jobs\SendCourseVideoReadyNotification;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\User;
use App\Observers\CourseVideoObserver;
use App\Services\MuxVideoLifecycle;
use App\Services\R2FileService;
use App\Services\R2VideoUploadService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class VideoStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_upload_route_uses_existing_r2_service_and_checks_ownership(): void
    {
        [$course] = $this->courseVideo();
        $file = ['file_name' => 'lesson.mp4', 'file_size' => 1024, 'content_type' => 'video/mp4'];
        $this->mock(R2VideoUploadService::class, function (MockInterface $mock) use ($course, $file): void {
            $mock->shouldReceive('initiate')->once()->with($course->instructor_id, $course->id, $file)
                ->andReturn(['key' => 'private-video', 'upload_id' => 'multipart1']);
        });
        $this->actingAs($course->instructor, 'instructor')
            ->postJson(route('instructor.course-video-uploads.initiate', $course), $file)
            ->assertCreated()->assertJsonPath('upload_id', 'multipart1');
        $other = $course->instructor->replicate();
        $other->forceFill(['email' => 'other@example.test', 'slug' => 'other-teacher'])->saveQuietly();
        $this->actingAs($other, 'instructor')
            ->postJson(route('instructor.course-video-uploads.initiate', $course), $file)->assertForbidden();
    }

    public function test_observer_queues_mux_and_retains_replaced_original(): void
    {
        [, $video] = $this->courseVideo();
        Queue::fake();
        Storage::fake('r2_private');
        $source = $video->url;
        Storage::disk('r2_private')->put($source, 'master');
        (new CourseVideoObserver)->created($video);
        $this->assertNotNull($video->refresh()->mux_pending_reference);
        Queue::assertPushed(ProcessCourseVideo::class);
        $video->forceFill(['mux_asset_id' => 'oldAsset'])->saveQuietly();
        app(MuxVideoLifecycle::class)->queue($video, true);
        Queue::assertPushed(DeleteMuxVideo::class, fn ($job) => $job->assetId === 'oldAsset');
        Storage::disk('r2_private')->assertExists($source);
    }

    public function test_replacement_has_its_own_queue_lock_and_old_jobs_do_not_change_it(): void
    {
        [, $video] = $this->courseVideo();
        Queue::fake();
        Http::preventStrayRequests();
        app(MuxVideoLifecycle::class)->queue($video, true);
        $old = new ProcessCourseVideo($video->id, $video->refresh()->mux_pending_reference);
        app(MuxVideoLifecycle::class)->queue($video, true);
        $new = new ProcessCourseVideo($video->id, $video->refresh()->mux_pending_reference);
        $this->assertNotSame($old->uniqueId(), $new->uniqueId());
        app()->call([$old, 'handle']);
        $old->failed(new \RuntimeException);
        $this->assertSame(VideoStatus::Processing, $video->refresh()->status);
        Http::assertNothingSent();
    }

    public function test_mux_import_uses_signed_r2_url_once_and_retains_original(): void
    {
        [, $video] = $this->courseVideo();
        Queue::fake();
        Storage::fake('r2_private');
        Storage::disk('r2_private')->put($video->url, 'master');
        $source = $video->url;
        config(['services.mux.token_id' => 'test', 'services.mux.token_secret' => 'secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.mux.com/video/v1/assets' => Http::response(['data' => ['id' => 'asset1', 'status' => 'preparing']]),
            'https://api.mux.com/video/v1/assets/asset1' => Http::response(['data' => ['id' => 'asset1', 'status' => 'preparing']]),
        ]);
        $this->mock(R2FileService::class, function (MockInterface $mock) use ($source): void {
            $mock->shouldReceive('temporaryUrl')->once()->with($source, 360)->andReturn('https://private.example/source?signature=temporary');
        });
        $job = new ProcessCourseVideo($video->id);
        app()->call([$job, 'handle']);
        app()->call([$job, 'handle']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['inputs'][0]['url'] === 'https://private.example/source?signature=temporary'
            && $request['playback_policies'] === ['signed']);
        Http::assertSentCount(2);
        $this->assertSame($source, $video->refresh()->url);
        $this->assertSame('asset1', $video->mux_asset_id);
        $this->assertSame(VideoStatus::Processing, $video->status);
        $this->assertStringNotContainsString('signature=temporary', $video->toJson());
        Storage::disk('r2_private')->assertExists($source);
    }

    public function test_mux_webhooks_are_signed_idempotent_and_preserve_r2_path(): void
    {
        [, $video] = $this->courseVideo();
        Queue::fake();
        $source = $video->url;
        $video->forceFill(['status' => VideoStatus::Processing, 'mux_pending_reference' => 'reference1'])->saveQuietly();
        $data = ['id' => 'asset1', 'passthrough' => 'reference1', 'duration' => 65.2,
            'playback_ids' => [['id' => 'playback1', 'policy' => 'signed']]];
        $this->muxWebhook('video.asset.ready', $data)->assertNoContent();
        $processed = $video->refresh()->processed_at->toISOString();
        $this->travel(1)->minutes();
        $this->muxWebhook('video.asset.ready', $data)->assertNoContent();
        $this->muxWebhook('video.asset.errored', $data)->assertNoContent();
        $this->assertSame(VideoStatus::Ready, $video->refresh()->status);
        $this->assertSame('playback1', $video->mux_playback_id);
        $this->assertSame(66, $video->duration);
        $this->assertSame($processed, $video->processed_at->toISOString());
        $this->assertSame($source, $video->url);
        Queue::assertPushed(SendCourseVideoReadyNotification::class, 1);
    }

    public function test_mux_error_and_stale_replacement_webhooks(): void
    {
        [, $video] = $this->courseVideo();
        Queue::fake();
        $video->forceFill(['status' => VideoStatus::Processing, 'mux_asset_id' => 'asset1', 'mux_pending_reference' => 'ref1'])->saveQuietly();
        $data = ['id' => 'asset1', 'passthrough' => 'ref1', 'errors' => ['messages' => ['secret R2 URL']]];
        $this->muxWebhook('video.asset.errored', $data)->assertNoContent();
        $this->assertSame(VideoStatus::Failed, $video->refresh()->status);
        $this->assertStringNotContainsString('secret', $video->processing_error);
        app(MuxVideoLifecycle::class)->queue($video, true);
        $this->muxWebhook('video.asset.ready', $data + ['playback_ids' => [['id' => 'old', 'policy' => 'signed']]])->assertNoContent();
        $this->assertSame(VideoStatus::Processing, $video->refresh()->status);
        $this->assertNull($video->mux_playback_id);
    }

    public function test_mux_rejects_invalid_or_expired_signatures_and_public_playback(): void
    {
        [, $video] = $this->courseVideo();
        $video->forceFill(['status' => VideoStatus::Processing, 'mux_asset_id' => 'asset1'])->saveQuietly();
        config(['services.mux.webhook_secret' => 'webhook-secret']);
        $this->postJson('/webhooks/mux', [])->assertUnauthorized();
        $this->muxWebhook('video.asset.ready', ['id' => 'asset1'], now()->timestamp - 301)->assertUnauthorized();
        $this->muxWebhook('video.asset.ready', ['id' => 'asset1', 'playback_ids' => [['id' => 'public', 'policy' => 'public']]])->assertNoContent();
        $this->assertSame(VideoStatus::Failed, $video->refresh()->status);
        $this->assertNull($video->mux_playback_id);
    }

    public function test_mux_playback_checks_access_before_signing_and_issues_valid_jwts(): void
    {
        [$course, $video] = $this->courseVideo();
        $video->forceFill(['mux_playback_id' => 'playback1'])->saveQuietly();
        $student = User::factory()->create();
        $url = route('course-videos.stream.playback', [$course, $video]);
        $this->actingAs($student, 'student')->getJson($url)->assertForbidden();
        DB::table('course_purchases')->insert([
            'user_id' => $student->id, 'course_id' => $course->id, 'price' => 20,
            'payment_status' => 'completed', 'purchased_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $options = ['config' => base_path('tests/Fixtures/openssl.cnf'), 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $pem, null, $options);
        config(['services.mux.signing_key_id' => 'key1', 'services.mux.signing_private_key' => base64_encode($pem), 'services.mux.playback_token_ttl' => 900]);
        $response = $this->getJson($url)->assertOk()->assertJsonPath('expires_in', 900);
        foreach (['hls' => 'v', 'thumbnail' => 't'] as $field => $audience) {
            parse_str(parse_url($response->json($field), PHP_URL_QUERY), $query);
            $claims = JWT::decode($query['token'], ['key1' => new Key(openssl_pkey_get_details($key)['key'], 'RS256')]);
            $this->assertSame('playback1', $claims->sub);
            $this->assertSame($audience, $claims->aud);
            $this->assertEqualsWithDelta(now()->timestamp + 900, $claims->exp, 2);
        }
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('token=', $video->refresh()->toJson());
    }

    public function test_owner_can_preview_premium_video_with_supported_private_key_formats(): void
    {
        [$course, $video] = $this->courseVideo();
        $video->forceFill(['mux_playback_id' => 'signedPlayback1', 'is_free' => false, 'is_published' => false])->saveQuietly();
        $this->actingAs($course->instructor, 'instructor');
        Http::preventStrayRequests();
        $options = ['config' => base_path('tests/Fixtures/openssl.cnf'), 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $pem, null, $options);
        $verificationKey = new Key(openssl_pkey_get_details($key)['key'], 'RS256');
        foreach ([$pem, base64_encode($pem), str_replace("\n", '\\n', $pem)] as $privateKey) {
            config(['services.mux.signing_key_id' => 'previewKey', 'services.mux.signing_private_key' => $privateKey, 'services.mux.playback_token_ttl' => 900]);
            $response = $this->getJson(route('course-videos.stream.playback', [$course, $video]))->assertOk();
            $url = $response->json('hls');
            $this->assertSame('stream.mux.com', parse_url($url, PHP_URL_HOST));
            $this->assertSame('/signedPlayback1.m3u8', parse_url($url, PHP_URL_PATH));
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $this->assertNotEmpty($query['token']);
            $claims = JWT::decode($query['token'], ['previewKey' => $verificationKey]);
            $this->assertSame('signedPlayback1', $claims->sub);
            $this->assertSame('v', $claims->aud);
            $this->assertEqualsWithDelta(now()->timestamp + 900, $claims->exp, 2);
        }
        $markup = Blade::render('<x-course-video-player :course="$course" :video="$video" />', compact('course', 'video'));
        $this->assertStringContainsString('data-playback="'.route('course-videos.stream.playback', [$course, $video]).'"', $markup);
        $this->assertStringNotContainsString('token=', $markup);
        $this->assertSame('signedPlayback1', $video->refresh()->mux_playback_id);
        Http::assertNothingSent();
    }

    public function test_missing_signing_key_fails_before_hls_without_public_fallback(): void
    {
        [$course, $video] = $this->courseVideo();
        $video->forceFill(['mux_playback_id' => 'signedPlayback1'])->saveQuietly();
        config(['services.mux.signing_key_id' => 'key1', 'services.mux.signing_private_key' => null]);
        Http::preventStrayRequests();
        $response = $this->actingAs($course->instructor, 'instructor')
            ->getJson(route('course-videos.stream.playback', [$course, $video]))->assertStatus(503);
        $this->assertNull($response->json('hls'));
        $this->assertSame('signedPlayback1', $video->refresh()->mux_playback_id);
        Http::assertNothingSent();
    }

    public function test_video_runtime_does_not_require_local_transcoding(): void
    {
        foreach (['app', 'config', 'routes'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $this->assertDoesNotMatchRegularExpression('/ffmpeg|ffprobe|VIDEO_RENDITIONS/i', $file->getContents(), $file->getPathname());
            }
        }
    }

    private function muxWebhook(string $type, array $data, ?int $timestamp = null)
    {
        config(['services.mux.webhook_secret' => 'webhook-secret']);
        $body = json_encode(['type' => $type, 'data' => $data]);
        $timestamp ??= now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'webhook-secret');

        return $this->call('POST', '/webhooks/mux', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_MUX_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $body);
    }

    public function test_obsolete_playback_routes_are_removed_and_master_requires_mux(): void
    {
        [$course, $video] = $this->courseVideo();
        $this->get('/webhooks/cloudflare/stream')->assertNotFound();
        $this->actingAs($course->instructor, 'instructor')
            ->get(route('course-videos.stream.master', [$course, $video]))->assertNotFound();
    }

    private function courseVideo(): array
    {
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
