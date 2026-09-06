<?php

namespace App\Filament\Instructors\Resources\Courses\Pages;

use App\Filament\Instructors\Resources\Courses\CourseResource;
use App\Services\CalcalateCourseDurationService;
use Filament\Actions\Action;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

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
