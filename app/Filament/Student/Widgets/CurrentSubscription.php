<?php

namespace App\Filament\Student\Widgets;

use App\Enums\SubscriptionStatus;
use Filament\Widgets\Widget;

class CurrentSubscription extends Widget
{
    protected string $view = 'student-current-subscription';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $subscription = auth('student')->user()->subscriptions()
            ->with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latest('ends_at')
            ->first();

        return compact('subscription');
    }
}
