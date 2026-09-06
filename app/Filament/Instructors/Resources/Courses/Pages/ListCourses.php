<?php

namespace App\Filament\Instructors\Resources\Courses\Pages;

use App\Filament\Instructors\Resources\Courses\CourseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('instructor.courses.actions.create')),
        ];
    }
}
