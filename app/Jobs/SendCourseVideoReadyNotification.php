<?php

namespace App\Jobs;

use App\Enums\VideoStatus;
use App\Models\CourseVideo;
use App\Models\UserDeviceToken;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Throwable;

class SendCourseVideoReadyNotification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public int $uniqueFor = 3600;
    public $deleteWhenMissingModels = true;

    public function __construct(public readonly int $courseVideoId)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return (string) $this->courseVideoId;
    }


    public function handle(Messaging $messaging): void
    {
        $video = CourseVideo::query()->with('section.course')->find($this->courseVideoId);

        if (! $video || $video->status !== VideoStatus::Ready || ! $video->is_published) {
            return;
        }

        $course = $video->section?->course;
        if (! $course || ! $course->is_published) {
            return;
        }

        $message = CloudMessage::fromArray([
            'notification' => [
                'title' => 'درس جديد في '.$course->title,
                'body' => 'فيديو "'.$video->title.'" متاح الآن للمشاهدة.',
            ],
            'data' => [
                'type' => 'course_video_ready',
                'course_id' => (string) $course->id,
                'course_slug' => (string) $course->slug,
                'video_id' => (string) $video->id,
                'url' => route('courses.learn', $course),
            ],
        ])->withDefaultSounds()->withHighestPossiblePriority();

        UserDeviceToken::query()
            ->whereHas('user.coursePurchases', fn ($query) => $query
                ->where('course_purchases.course_id', $course->id)
                ->where('course_purchases.payment_status', 'completed'))
            ->orderBy('id')
            ->chunkById(500, function ($devices) use ($messaging, $message, $video): void {
                $tokens = $devices->pluck('token')->all();

                try {
                    $report = $messaging->sendMulticast($message, $tokens);
                    $staleTokens = array_values(array_unique([
                        ...$report->unknownTokens(),
                        ...$report->invalidTokens(),
                    ]));

                    if ($staleTokens !== []) {
                        UserDeviceToken::query()->whereIn('token', $staleTokens)->delete();
                    }
                } catch (Throwable $exception) {
                    Log::error('Failed to send course video ready notifications.', [
                        'course_video_id' => $video->id,
                        'exception' => $exception,
                    ]);

                    throw $exception;
                }
            });
    }
}
