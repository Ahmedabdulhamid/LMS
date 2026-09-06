<?php

namespace App\Filament\Student\Pages;

use App\Filament\Student\Widgets\ContinueLearning;
use App\Filament\Student\Widgets\StudentStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('student-panel.navigation.dashboard');
    }

    public function getTitle(): string
    {
        return __('student-panel.dashboard.title');
    }

    public function getWidgets(): array
    {
        return [
            StudentStats::class,
            ContinueLearning::class,
        ];
    }
}
