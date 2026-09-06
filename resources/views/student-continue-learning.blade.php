<x-filament-widgets::widget>
    <div class="space-y-5">
        <div class="rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 p-6 text-zinc-950 shadow-sm dark:from-amber-400 dark:to-orange-400">
            <h2 class="text-2xl font-bold">{{ __('student-panel.dashboard.welcome', ['name' => $student->name]) }}</h2>
            <p class="mt-1 text-sm font-medium opacity-80">{{ __('student-panel.dashboard.welcome_message') }}</p>
        </div>
        <x-filament::section :heading="__('student-panel.dashboard.continue_learning')">
            @if ($course)
                <div class="grid items-center gap-5 md:grid-cols-[160px_1fr_auto]">
                    <img class="h-28 w-full rounded-xl object-cover md:w-40" src="{{ $course->thumbnail ? Storage::disk(config('lms-upload.disk'))->url($course->thumbnail) : asset('images/learning-platform-logo.png') }}" alt="{{ $course->title }}">
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-bold text-gray-950 dark:text-white">{{ $course->title }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('student-panel.dashboard.last_lesson') }}: {{ $lastLesson?->title ?? __('student-panel.dashboard.not_started') }}</p>
                        <div class="mt-4 flex items-center gap-3"><div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-primary-500" style="width: {{ $progress }}%"></div></div><span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $progress }}%</span></div>
                    </div>
                    <x-filament::button tag="a" :href="route('courses.learn', $course)" icon="heroicon-m-play">{{ __('student-panel.dashboard.continue') }}</x-filament::button>
                </div>
            @else
                <div class="py-8 text-center"><h3 class="font-semibold text-gray-950 dark:text-white">{{ __('student-panel.dashboard.no_course') }}</h3><p class="mt-1 text-sm text-gray-500">{{ __('student-panel.dashboard.no_course_hint') }}</p></div>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
