<?php

namespace App\Console\Commands;

use App\Enums\VideoStatus;
use App\Jobs\ProcessCourseVideo;
use App\Models\CourseVideo;
use Illuminate\Bus\UniqueLock;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class ProcessPendingCourseVideos extends Command
{
    protected $signature = 'videos:process-pending
        {--failed : Include failed videos}
        {--release-locks : Release stale unique locks before dispatching}';

    protected $description = 'Queue uploaded course videos that still need HLS processing';

    public function handle(): int
    {
        $statuses = [VideoStatus::Uploading, VideoStatus::Processing];
        if ($this->option('failed')) {
            $statuses[] = VideoStatus::Failed;
        }

        $queued = 0;
        CourseVideo::query()
            ->whereNotNull('url')
            ->whereIn('status', $statuses)
            ->select('id')
            ->chunkById(100, function ($videos) use (&$queued): void {
                foreach ($videos as $video) {
                    $job = new ProcessCourseVideo($video->id);
                    if ($this->option('release-locks')) {
                        (new UniqueLock(app(CacheRepository::class)))->release($job);
                    }

                    dispatch($job);
                    $queued++;
                }
            });

        $this->info("Queued $queued course video(s).");

        return self::SUCCESS;
    }
}
