<?php

namespace App\Filament\Instructors\Resources\Courses\Pages;

use App\Filament\Instructors\Resources\Courses\CourseResource;
use App\Services\CalcalateCourseDurationService;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = auth('instructor')->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(CalcalateCourseDurationService::class)
            ->calculateCourseDuration($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return CourseResource::getUrl('edit', [
            'record' => $this->record,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('lms.instructor.messages.course_created');
    }
}
