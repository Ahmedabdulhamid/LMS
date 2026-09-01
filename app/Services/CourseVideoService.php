<?php

namespace App\Services;

use App\Models\CourseVideo;
use App\Models\Section;
use Illuminate\Support\Facades\DB;

class CourseVideoService
{
    public function __construct(
        private readonly CalcalateCourseDurationService $calcalateCourseDurationService,
    ) {}

    public function syncVideos(Section $section, array $videos): void
    {
        DB::transaction(function () use ($section, $videos): void {
            $existingVideos = $section->videos()->get()->keyBy('id');
            $videoIds = [];

            foreach ($videos as $index => $data) {
                $video = ! empty($data['id'])
                    ? $existingVideos->get($data['id'])
                    : null;

                $attributes = $this->attributes($data, $index, $video);

                if ($video) {
                    $video->update($attributes);
                } else {
                    $video = $section->videos()->create($attributes);
                }

                $videoIds[] = $video->id;
            }

            $existingVideos->except($videoIds)->each->delete();

            $this->calcalateCourseDurationService
                ->calculateCourseDuration($section->course);
        });
    }

    public function createVideo(Section $section, array $data): CourseVideo
    {
        return DB::transaction(function () use ($section, $data): CourseVideo {
            $video = $section->videos()->create(
                $this->attributes($data, $section->videos()->count()),
            );

            $this->calcalateCourseDurationService
                ->calculateCourseDuration($section->course);

            return $video;
        });
    }

    public function updateVideo(int $videoId, array $data): CourseVideo
    {
        return DB::transaction(function () use ($videoId, $data): CourseVideo {
            $video = CourseVideo::findOrFail($videoId);
            $video->update($this->attributes($data, $video->order, $video));

            $this->calcalateCourseDurationService
                ->calculateCourseDuration($video->section->course);

            return $video->refresh();
        });
    }

    public function deleteVideo(int $videoId): void
    {
        DB::transaction(function () use ($videoId): void {
            $video = CourseVideo::findOrFail($videoId);
            $course = $video->section->course;

            $video->delete();

            $this->calcalateCourseDurationService
                ->calculateCourseDuration($course);
        });
    }

    private function attributes(
        array $data,
        int $defaultOrder,
        ?CourseVideo $video = null,
    ): array {
        return [
            'title' => $data['title'] ?? $video?->title,
            'description' => $data['description'] ?? $video?->description,
            'url' => $data['url'] ?? $video?->url,
            'order' => $data['order'] ?? $video?->order ?? $defaultOrder,
            'is_free' => $data['is_free'] ?? $video?->is_free ?? false,
            'is_published' => $data['is_published'] ?? $video?->is_published ?? false,
        ];
    }
}
