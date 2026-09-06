<x-filament-widgets::widget>
    <div class="space-y-6">
        <x-filament::section icon="heroicon-o-hand-raised">
            <x-slot name="heading">{{ __('student.dashboard.welcome', ['name' => $student->name]) }} 👋</x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('student.dashboard.welcome_message') }}</p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">{{ __('student.continue_learning.title') }}</x-slot>
            @if ($course)
                <div class="grid items-center gap-6 md:grid-cols-[12rem_1fr_auto]">
                    <div class="aspect-video overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                        @if ($thumbnailUrl)
                            <img class="h-full w-full object-cover" src="{{ $thumbnailUrl }}" alt="{{ $course->title }}">
                        @else
                            <div class="flex h-full items-center justify-center text-3xl font-bold text-gray-400">{{ mb_substr($course->title, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="min-w-0 space-y-3">
                        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $course->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('student.continue_learning.last_lesson') }}: {{ $lastLesson?->title ?? __('student.continue_learning.not_started') }}</p>
                        <div class="flex items-center gap-3"><div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-full rounded-full bg-primary-600" style="width: {{ min(100, max(0, $progress)) }}%"></div></div><span class="text-sm font-medium">{{ $progress }}%</span></div>
                    </div>
                    <x-filament::button tag="a" :href="route('courses.learn', $course->slug)" icon="heroicon-o-play">{{ __('student.actions.continue') }}</x-filament::button>
                </div>
            @else
                <div class="py-6 text-center text-sm text-gray-500">{{ __('student.continue_learning.empty') }}</div>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
