<?php

namespace App\Filament\Instructors\Pages;

use App\Filament\Instructors\Widgets\CoursePerformanceWidget;
use App\Filament\Instructors\Widgets\InstructorStatsWidget;
use App\Filament\Instructors\Widgets\RecentEnrollmentsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('instructor.navigation.dashboard');
    }

    public function getTitle(): string
    {
        return __('instructor.dashboard.title');
    }

    public function getWidgets(): array
    {
        return [
            InstructorStatsWidget::class,
            RecentEnrollmentsWidget::class,
            CoursePerformanceWidget::class,
        ];
    }
}
