<?php

namespace App\Services;

use App\Models\CourseVideo;
use App\Models\User;
use App\Models\UserCourseProgress;
use App\Models\UserVideoProgress;
use Illuminate\Support\Facades\DB;

class StudentProgressService
{
    public function record(User $student, CourseVideo $video, int $positionSeconds, int $durationSeconds, bool $ended = false): UserVideoProgress
    {
        return DB::transaction(function () use ($student, $video, $positionSeconds, $durationSeconds, $ended): UserVideoProgress {
            $duration = max(1, $durationSeconds ?: (int) $video->duration);
            $position = min(max(0, $positionSeconds), $duration);
            $percentage = min(100, (int) floor(($position / $duration) * 100));
            $completed = $ended || $percentage >= 90;

            $videoProgress = UserVideoProgress::query()->firstOrNew([
                'user_id' => $student->id,
                'video_id' => $video->id,
            ]);

            $videoProgress->progress = max((int) $videoProgress->progress, $completed ? 100 : $percentage);
            $videoProgress->last_position_seconds = max((int) $videoProgress->last_position_seconds, $position);
            $videoProgress->last_watched_at = now();
            $videoProgress->is_completed = (bool) $videoProgress->is_completed || $completed;

            if ($videoProgress->is_completed && ! $videoProgress->completed_at) {
                $videoProgress->completed_at = now();
            }

            $videoProgress->save();
            $this->recalculateCourse($student, $video);

            return $videoProgress;
        });
    }

    private function recalculateCourse(User $student, CourseVideo $video): void
    {
        $courseId = $video->section()->value('course_id');
        $publishedVideoIds = CourseVideo::query()
            ->whereHas('section', fn ($query) => $query->where('course_id', $courseId))
            ->where('is_published', true)
            ->pluck('id');
        $total = $publishedVideoIds->count();
        $completed = UserVideoProgress::query()
            ->where('user_id', $student->id)
            ->whereIn('video_id', $publishedVideoIds)
            ->where('is_completed', true)
            ->count();
        $percentage = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;

        $courseProgress = UserCourseProgress::query()->firstOrNew([
            'user_id' => $student->id,
            'course_id' => $courseId,
        ]);
        $courseProgress->progress = $percentage;
        $courseProgress->last_accessed_at = now();
        $courseProgress->completed_at = $percentage === 100 ? ($courseProgress->completed_at ?? now()) : null;
        $courseProgress->save();
    }
}
