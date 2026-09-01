<?php

namespace App\Filament\Instructors\Resources\Courses\Pages;

use App\Enums\VideoStatus;
use App\Filament\Instructors\Resources\Courses\CourseResource;
use App\Jobs\ProcessCourseVideo;
use App\Models\CourseVideo;
use App\Services\R2FileService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Cache;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected string $view = 'filament.instructors.resources.courses.pages.view-course-premium';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record=Cache::memo()->remember("course_{$this->record->id}", now()->addMinutes(5), function () {
            return $this->record->load([
                'instructor',
                'category',
                'goals',
                'requirements',
                'sections.videos.attachments',
                'reviews.user',
                'purchases.user',
                'wishlists.user',
                'progress.user',
            ]);
        });





    }

    public function r2Url(?string $key): ?string
    {
        return app(R2FileService::class)->publicUrl($key);
    }

    public function retryVideo(int $videoId): void
    {
        $video = CourseVideo::query()
            ->whereKey($videoId)
            ->whereHas('section', fn ($query) => $query->where('course_id', $this->record->id))
            ->firstOrFail();
        abort_unless($video->status === VideoStatus::Failed, 422);

        $video->forceFill(['status' => VideoStatus::Processing, 'processing_error' => null])->saveQuietly();
        ProcessCourseVideo::dispatch($video->id);
        Notification::make()->title(__('lms.instructor.messages.video_processing_queued'))->success()->send();
        $this->record->load('sections.videos.attachments');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
