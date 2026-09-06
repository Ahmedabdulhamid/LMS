<?php

namespace App\Filament\Instructors\Widgets;

use App\Models\Instructor;
use App\Services\InstructorDashboardService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentEnrollmentsWidget extends TableWidget
{
    /** @var array<int, array<string, mixed>> */
    public array $enrollments = [];

    protected int|string|array $columnSpan = 'full';

    public function mount(InstructorDashboardService $dashboard): void
    {
        /** @var Instructor $instructor */
        $instructor = auth('instructor')->user();
        $this->enrollments = $dashboard->getRecentEnrollments($instructor->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('instructor.dashboard.recent_enrollments'))
            ->records(fn (): array => $this->enrollments)
            ->columns([
                TextColumn::make('student_name')->label(__('instructor.dashboard.columns.student'))->searchable(),
                TextColumn::make('course_name')->label(__('instructor.dashboard.columns.course')),
                TextColumn::make('enrollment_date')->label(__('instructor.dashboard.columns.enrollment_date'))->dateTime()->sortable(),
            ])
            ->emptyStateHeading(__('instructor.dashboard.empty.enrollments'))
            ->paginated(false);
    }
}
