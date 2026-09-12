<?php

namespace App\Jobs;

use App\Enums\VideoStatus;
use App\Exceptions\MuxVideoException;
use App\Models\CourseVideo;
use App\Services\MuxVideoLifecycle;
use App\Services\MuxVideoService;
use App\Services\R2FileService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProcessCourseVideo implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 1800;

    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $courseVideoId, public readonly ?string $reference = null)
    {
        $this->onQueue('video-processing');
    }

    public function uniqueId(): string
    {
        return $this->courseVideoId.':'.($this->reference ?? 'legacy');
    }

    public function handle(R2FileService $storage, MuxVideoService $mux, MuxVideoLifecycle $lifecycle): void
    {
        // Support existing serialized jobs that only contain the video ID. Persist
        // correlation before the API call so an early webhook can recover it.
        CourseVideo::whereKey($this->courseVideoId)->whereNotNull('url')
            ->whereNull('mux_pending_reference')->update([
                'mux_pending_reference' => (string) Str::uuid(),
            ]);
        // Serialize imports with replacements and webhooks; only small API requests occur here.
        $asset = DB::transaction(function () use ($storage, $mux): ?array {
            $video = CourseVideo::query()->lockForUpdate()->find($this->courseVideoId);
            if (! $video || blank($video->url)) {
                return null;
            }
            if (isset($this->reference) && $video->mux_pending_reference !== $this->reference) {
                return null;
            }
            if ($video->mux_asset_id) {
                return $mux->getAsset($video->mux_asset_id);
            }
            $course = $video->section->course;
            $prefix = "instructors/{$course->instructor_id}/courses/{$course->id}/videos/";
            if (! str_starts_with($video->url, $prefix) || str_contains($video->url, '..')) {
                throw new RuntimeException('The original video object does not belong to this course.');
            }
            $reference = $video->mux_pending_reference;
            $data = $mux->importVideo($reference, $storage->temporaryUrl($video->url, 360));
            if (! $mux->validId($data['id'] ?? null)) {
                throw new MuxVideoException(reason: 'invalid_response');
            }
            $video->forceFill([
                'mux_asset_id' => $data['id'], 'status' => VideoStatus::Processing,
                'processing_stage' => 'processing', 'processing_progress' => 10,
                'processing_error' => null, 'processing_started_at' => now(),
            ])->save();

            return $data;
        });
        if ($asset) {
            $lifecycle->apply($asset);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        CourseVideo::whereKey($this->courseVideoId)->where('status', 'processing')
            ->when($this->reference ?? null, fn ($query) => $query->where('mux_pending_reference', $this->reference))
            ->whereNull('mux_asset_id')->update([
                'status' => 'failed', 'processing_stage' => 'failed',
                'processing_error' => 'Mux import failed. Check configuration and retry processing.',
            ]);
    }
}
