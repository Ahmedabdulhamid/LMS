<?php

namespace App\Jobs;

use App\Models\CourseVideo;
use App\Services\MuxVideoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteMuxVideo implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public array $backoff = [60, 300, 900, 3600];

    public function __construct(public readonly string $assetId)
    {
        $this->onQueue('video-processing');
    }

    public function handle(MuxVideoService $mux): void
    {
        if (! CourseVideo::where('mux_asset_id', $this->assetId)->exists()) {
            $mux->deleteAsset($this->assetId);
        }
    }
}
