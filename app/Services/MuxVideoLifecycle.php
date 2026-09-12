<?php

namespace App\Services;

use App\Enums\VideoStatus;
use App\Jobs\DeleteMuxVideo;
use App\Jobs\ProcessCourseVideo;
use App\Jobs\SendCourseVideoReadyNotification;
use App\Models\CourseVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MuxVideoLifecycle
{
    public function queue(CourseVideo $video, bool $replacement = false): void
    {
        DB::transaction(function () use ($video, $replacement): void {
            $video = CourseVideo::query()->lockForUpdate()->find($video->id);
            if (! $video || (! $replacement && $video->status === VideoStatus::Ready && $video->mux_playback_id)) {
                return;
            }
            if (blank($video->url)) {
                return;
            }
            $oldAsset = $replacement ? $video->mux_asset_id : null;
            $video->forceFill([
                'mux_asset_id' => $replacement ? null : $video->mux_asset_id,
                'mux_playback_id' => $replacement ? null : $video->mux_playback_id,
                'mux_pending_reference' => $replacement ? (string) Str::uuid() : ($video->mux_pending_reference ?: (string) Str::uuid()),
                'status' => VideoStatus::Processing, 'processing_stage' => 'queued',
                'processing_progress' => 0, 'processing_error' => null, 'processed_at' => null,
            ])->saveQuietly();
            if ($oldAsset) {
                DeleteMuxVideo::dispatch($oldAsset)->afterCommit();
            }
            ProcessCourseVideo::dispatch($video->id, $video->mux_pending_reference)->afterCommit();
        });
    }

    public function apply(array $asset): void
    {
        $id = $asset['id'] ?? null;
        if (! app(MuxVideoService::class)->validId($id)) {
            return;
        }
        DB::transaction(function () use ($asset, $id): void {
            $reference = $asset['passthrough'] ?? null;
            $video = CourseVideo::query()->where(function ($query) use ($id, $reference): void {
                $query->where('mux_asset_id', $id);
                if (is_string($reference) && $reference !== '') {
                    $query->orWhere('mux_pending_reference', $reference);
                }
            })->lockForUpdate()->first();
            if (! $video || ($video->mux_asset_id && $video->mux_asset_id !== $id)) {
                return;
            }
            if ($video->status === VideoStatus::Ready && $video->mux_playback_id) {
                return; // Duplicate and out-of-order terminal events cannot regress readiness.
            }
            $status = $asset['status'] ?? null;
            if (! in_array($status, ['ready', 'errored'], true)) {
                return;
            }
            $playback = collect($asset['playback_ids'] ?? [])->firstWhere('policy', 'signed');
            $ready = $status === 'ready' && app(MuxVideoService::class)->validId($playback['id'] ?? null)
                && ! collect($asset['playback_ids'] ?? [])->contains('policy', 'public');
            $video->forceFill([
                'mux_asset_id' => $id,
                'mux_playback_id' => $ready ? $playback['id'] : null,
                'status' => $ready ? VideoStatus::Ready : VideoStatus::Failed,
                'processing_stage' => $ready ? 'ready' : 'failed',
                'processing_progress' => $ready ? 100 : 0,
                'processing_error' => $ready ? null : 'The video could not be processed securely. Retry processing or upload a new copy.',
                'duration' => $ready ? max(0, (int) ceil((float) ($asset['duration'] ?? 0))) : $video->duration,
                'processed_at' => $ready ? now() : null,
            ])->save();
            if ($ready) {
                app(CalcalateCourseDurationService::class)->calculateCourseDuration($video->section->course);
                SendCourseVideoReadyNotification::dispatch($video->id)->afterCommit();
            }
        });
    }
}
