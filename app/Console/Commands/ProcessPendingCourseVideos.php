<?php

namespace App\Console\Commands;

use App\Enums\VideoStatus;
use App\Models\CourseVideo;
use App\Services\MuxVideoLifecycle;
use Illuminate\Console\Command;

class ProcessPendingCourseVideos extends Command
{
    protected $signature = 'videos:process-pending {--failed : Retry failed imports} {--legacy : Include ready R2 videos without Mux}';

    protected $description = 'Queue R2 originals for Mux import or reconcile processing assets';

    public function handle(MuxVideoLifecycle $lifecycle): int
    {
        $statuses = ['uploading', 'processing'];
        if ($this->option('failed')) {
            $statuses[] = 'failed';
        }
        if ($this->option('legacy')) {
            $statuses[] = 'ready';
        }
        $count = 0;
        CourseVideo::whereNotNull('url')->where('url', '!=', '')
            ->whereNull('mux_playback_id')->whereIn('status', $statuses)
            ->chunkById(100, function ($videos) use ($lifecycle, &$count): void {
                foreach ($videos as $video) {
                    $lifecycle->queue($video, $video->status === VideoStatus::Failed);
                    $count++;
                }
            });
        $this->info("Queued {$count} video(s).");

        return self::SUCCESS;
    }
}
