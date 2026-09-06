<?php

namespace App\Filament\Instructors\Widgets;

use App\Models\Instructor;
use App\Services\InstructorDashboardService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CoursePerformanceWidget extends TableWidget
{
    /** @var array<int, array<string, mixed>> */
    public array $courses = [];

    protected int|string|array $columnSpan = 'full';

    public function mount(InstructorDashboardService $dashboard): void
    {
        /** @var Instructor $instructor */
        $instructor = auth('instructor')->user();
        $this->courses = $dashboard->getCoursePerformance($instructor->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('instructor.dashboard.course_performance'))
            ->records(fn (): array => $this->courses)
            ->columns([
                TextColumn::make('title')->label(__('instructor.dashboard.columns.course_title'))->searchable(),
                TextColumn::make('students_count')->label(__('instructor.dashboard.columns.students'))->numeric()->sortable(),
                TextColumn::make('completion_percentage')->label(__('instructor.dashboard.columns.completion'))->suffix('%')->sortable(),
                TextColumn::make('average_rating')->label(__('instructor.dashboard.columns.rating'))->suffix(' / 5')->sortable(),
            ])
            ->emptyStateHeading(__('instructor.dashboard.empty.courses'))
            ->paginated(false);
    }
}
