<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-calendar-days">
        <x-slot name="heading">{{ __('student.payments.current_subscription') }}</x-slot>
        @if ($subscription)
            <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-sm text-gray-500">{{ __('student.payments.course') }}</dt><dd class="mt-1 font-semibold">{{ $subscription->plan->courses->pluck('title')->join(', ') ?: $subscription->plan->name }}</dd></div>
                <div><dt class="text-sm text-gray-500">{{ __('student.payments.start_date') }}</dt><dd class="mt-1 font-semibold">{{ $subscription->starts_at?->translatedFormat('j M Y') ?? '—' }}</dd></div>
                <div><dt class="text-sm text-gray-500">{{ __('student.payments.expiry_date') }}</dt><dd class="mt-1 font-semibold">{{ $subscription->ends_at?->translatedFormat('j M Y') ?? '—' }}</dd></div>
                <div><dt class="text-sm text-gray-500">{{ __('student.payments.status') }}</dt><dd class="mt-1"><x-filament::badge :color="$subscription->status->value === 'active' ? 'success' : 'danger'">{{ __('student.status.'.$subscription->status->value) }}</x-filament::badge></dd></div>
            </dl>
        @else
            <p class="text-sm text-gray-500">{{ __('student.payments.no_subscription') }}</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
