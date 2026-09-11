<?php

namespace App\Jobs;

use App\Enums\VideoStatus;
use App\Models\CourseVideo;
use App\Services\CalcalateCourseDurationService;
use App\Services\HlsVideoTranscoder;
use App\Services\R2FileService;
use App\Services\VideoMetadataService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessCourseVideo implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 22000;

    public int $uniqueFor = 24000;

    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $courseVideoId)
    {
        $this->onQueue((string) config('video.queue', 'video-processing'));
    }

    public function uniqueId(): string
    {
        return (string) $this->courseVideoId;
    }

    public function handle(
        R2FileService $storage,
        VideoMetadataService $metadataService,
        HlsVideoTranscoder $transcoder,
        CalcalateCourseDurationService $courseDuration,
    ): void {
        $video = CourseVideo::query()->with('section.course')->find($this->courseVideoId);
        if (! $video || blank($video->url)) {
            return;
        }

        $course = $video->section->course;
        $expectedPrefix = sprintf('instructors/%d/courses/%d/videos/', $course->instructor_id, $course->id);
        if (! str_starts_with($video->url, $expectedPrefix) || str_contains($video->url, '..')) {
            throw new RuntimeException('The original video object does not belong to this course.');
        }

        $temporaryDirectory = storage_path('app/video-processing/'.bin2hex(random_bytes(16)));
        $input = $temporaryDirectory.DIRECTORY_SEPARATOR.'source'.($this->safeExtension($video->url));
        $output = $temporaryDirectory.DIRECTORY_SEPARATOR.'hls';

        $video->forceFill([
            'status' => VideoStatus::Processing,
            'processing_stage' => 'downloading',
            'processing_progress' => 1,
            'processing_error' => null,
            'processing_started_at' => now(),
            'processed_at' => null,
        ])->saveQuietly();

        try {
            File::ensureDirectoryExists($temporaryDirectory);
            $source = $storage->readStream($video->url);
            $destination = fopen($input, 'wb');
            if (! is_resource($destination)) {
                throw new RuntimeException('Could not create a temporary video file.');
            }
            try {
                stream_copy_to_stream($source, $destination);
            } finally {
                fclose($source);
                fclose($destination);
            }

            $video->forceFill(['processing_stage' => 'probing', 'processing_progress' => 8])->saveQuietly();
            $metadata = $metadataService->probe($input);
            if (! is_int($metadata['duration']) || ! is_int($metadata['width']) || ! is_int($metadata['height'])) {
                throw new RuntimeException('FFprobe did not return valid video metadata. Verify the configured FFprobe binary.');
            }

            $video->forceFill(['processing_stage' => 'transcoding', 'processing_progress' => 10])->saveQuietly();
            $transcoder->transcode(
                $input,
                $output,
                $metadata['width'],
                $metadata['height'],
                $metadata['duration'],
                function (int $progress) use ($video): void {
                    $overallProgress = 10 + (int) floor($progress * 0.8);
                    if ($overallProgress > (int) $video->processing_progress) {
                        $video->forceFill(['processing_progress' => $overallProgress])->saveQuietly();
                    }
                },
            );
            $baseKey = sprintf('courses/%d/videos/%d/hls', $course->id, $video->id);
            if (! CourseVideo::query()->whereKey($video->id)->exists()) {
                return;
            }
            $video->forceFill(['processing_stage' => 'uploading', 'processing_progress' => 92])->saveQuietly();
            $storage->uploadHlsDirectory($output, $baseKey);
            if (! CourseVideo::query()->whereKey($video->id)->exists()) {
                return;
            }

            $video->forceFill([
                'status' => VideoStatus::Ready,
                'hls_path' => $baseKey.'/master.m3u8',
                'duration' => $metadata['duration'],
                'source_width' => $metadata['width'],
                'source_height' => $metadata['height'],
                'processing_stage' => 'ready',
                'processing_progress' => 100,
                'processing_error' => null,
                'processed_at' => now(),
            ])->saveQuietly();
            $courseDuration->calculateCourseDuration($course);
        } catch (Throwable $exception) {
            $video->forceFill([
                'status' => VideoStatus::Failed,
                'processing_stage' => 'failed',
                'processing_error' => 'Video processing failed. Check the application logs and retry.',
            ])->saveQuietly();
            Log::error('Course video HLS processing failed.', [
                'course_video_id' => $video->id,
                'exception' => $exception,
            ]);
            throw $exception;
        } finally {
            File::deleteDirectory($temporaryDirectory);
            if (! CourseVideo::query()->whereKey($video->id)->exists()) {
                Storage::disk(config('filesystems.uploads', 'r2_private'))
                    ->deleteDirectory(sprintf('courses/%d/videos/%d/hls', $course->id, $video->id));
            }
        }

        SendCourseVideoReadyNotification::dispatch($video->id);
    }

    private function safeExtension(string $key): string
    {
        $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));

        return preg_match('/^[a-z0-9]{1,8}$/', $extension) ? '.'.$extension : '.video';
    }
}
