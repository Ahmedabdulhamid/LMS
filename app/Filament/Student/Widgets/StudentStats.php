<?php

namespace App\Filament\Student\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        /** @var User $student */
        $student = auth('student')->user();
        $courseIds = $student->enrolledCourses()->pluck('courses.id');
        $courseCount = $courseIds->count();
        $progress = $courseCount > 0
            ? (int) round($student->courseProgress()->whereIn('course_id', $courseIds)->sum('progress') / $courseCount)
            : 0;
        $subscription = $student->subscriptions()->latest('ends_at')->first();
        $subscriptionLabel = match (true) {
            $subscription?->status === SubscriptionStatus::Active && (! $subscription->ends_at || $subscription->ends_at->isFuture()) => __('student-panel.dashboard.active'),
            $subscription !== null => __('student-panel.dashboard.expired'),
            default => __('student-panel.dashboard.no_subscription'),
        };

        return [
            Stat::make(__('student-panel.dashboard.total_courses'), $courseCount)->icon('heroicon-o-academic-cap')->color('primary'),
            Stat::make(__('student-panel.dashboard.completed_lessons'), $student->videoProgress()->where('is_completed', true)->count())->icon('heroicon-o-check-circle')->color('success'),
            Stat::make(__('student-panel.dashboard.learning_progress'), $progress.'%')->icon('heroicon-o-chart-bar')->color('info'),
            Stat::make(__('student-panel.dashboard.subscription_status'), $subscriptionLabel)->icon('heroicon-o-credit-card')->color($subscriptionLabel === __('student-panel.dashboard.active') ? 'success' : 'gray'),
        ];
    }
}
