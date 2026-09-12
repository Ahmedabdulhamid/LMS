<?php

namespace App\Filament\Instructors\Resources\Courses\Pages;

use App\Filament\Instructors\Resources\Courses\CourseResource;
use App\Services\CalcalateCourseDurationService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label(__('instructor.courses.actions.view')),

        ];
    }

    protected function afterSave(): void
    {
        app(CalcalateCourseDurationService::class)
            ->calculateCourseDuration($this->record);
    }
}
