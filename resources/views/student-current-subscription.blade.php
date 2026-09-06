<x-filament-widgets::widget>
    <x-filament::section :heading="__('student-panel.payments.current_subscription')" icon="heroicon-o-sparkles">
        @if ($subscription)
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs text-gray-500">{{ __('student-panel.payments.plan') }}</div><div class="mt-1 font-semibold">{{ $subscription->plan->name }}</div></div>
                <div><div class="text-xs text-gray-500">{{ __('student-panel.payments.starts_at') }}</div><div class="mt-1 font-semibold">{{ $subscription->starts_at?->toFormattedDateString() }}</div></div>
                <div><div class="text-xs text-gray-500">{{ __('student-panel.payments.ends_at') }}</div><div class="mt-1 font-semibold">{{ $subscription->ends_at?->toFormattedDateString() ?? '—' }}</div></div>
                <div><div class="text-xs text-gray-500">{{ __('student-panel.payments.status') }}</div><x-filament::badge color="success" class="mt-1">{{ __('student-panel.dashboard.active') }}</x-filament::badge></div>
            </div>
        @else
            <p class="text-sm text-gray-500">{{ __('student-panel.payments.no_subscription') }}</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
