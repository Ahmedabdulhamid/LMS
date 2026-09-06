<?php

namespace App\Filament\Instructors\Widgets;

use App\Models\Instructor;
use App\Services\InstructorDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstructorStatsWidget extends StatsOverviewWidget
{
    /** @var array{total_courses: int, total_students: int, total_revenue: float, average_rating: float} */
    public array $statistics = [];

    public function mount(InstructorDashboardService $dashboard): void
    {
        /** @var Instructor $instructor */
        $instructor = auth('instructor')->user();
        $this->statistics = $dashboard->getStatistics($instructor->id);
    }

    protected function getStats(): array
    {
        return [
            Stat::make(__('instructor.dashboard.stats.courses'), number_format($this->statistics['total_courses']))
                ->icon('heroicon-o-academic-cap'),
            Stat::make(__('instructor.dashboard.stats.students'), number_format($this->statistics['total_students']))
                ->icon('heroicon-o-users'),
            Stat::make(__('instructor.dashboard.stats.revenue'), number_format($this->statistics['total_revenue'], 2).' EGP')
                ->icon('heroicon-o-banknotes'),
            Stat::make(__('instructor.dashboard.stats.rating'), number_format($this->statistics['average_rating'], 1).' / 5')
                ->icon('heroicon-o-star'),
        ];
    }
}
