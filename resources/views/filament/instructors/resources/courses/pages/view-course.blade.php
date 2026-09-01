<x-filament-panels::page>
    @php
        $course = $this->getRecord();
        $videos = $course->sections->flatMap->videos;
        $attachments = $videos->flatMap->attachments;
        $formatDuration = static function ($seconds): string {
            $seconds = (int) $seconds;
            return $seconds > 0 ? sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60) : 'Not calculated';
        };
    @endphp

    <div class="space-y-6">
        <section class="grid gap-6 lg:grid-cols-3">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 lg:col-span-2">
                @if ($course->thumbnail)
                    <img src="{{ $this->r2Url($course->thumbnail) }}" alt="{{ $course->title }}" class="h-72 w-full object-cover">
                @endif
                <div class="space-y-4 p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-400/10 dark:text-primary-400">{{ $course->category?->name ?? 'No category' }}</span>
                        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $course->is_published ? 'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $course->title }}</h2>
                    <p class="whitespace-pre-line text-gray-600 dark:text-gray-300">{{ $course->description }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-5 text-lg font-semibold text-gray-950 dark:text-white">Course overview</h3>
                <dl class="space-y-4 text-sm">
                    <div><dt class="text-gray-500">Instructor</dt><dd class="font-medium text-gray-950 dark:text-white">{{ $course->instructor?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Language / Level</dt><dd class="font-medium text-gray-950 dark:text-white">{{ strtoupper($course->lang) }} · {{ ucfirst($course->level) }}</dd></div>
                    <div><dt class="text-gray-500">Price</dt><dd class="font-medium text-gray-950 dark:text-white">{{ number_format((float) ($course->price_after_discount ?: $course->price), 2) }} EGP @if($course->price_after_discount)<span class="ml-2 text-gray-400 line-through">{{ number_format((float) $course->price, 2) }} EGP</span>@endif</dd></div>
                    <div><dt class="text-gray-500">Duration</dt><dd class="font-medium text-gray-950 dark:text-white">{{ $formatDuration($course->duration) }}</dd></div>
                    <div><dt class="text-gray-500">Content</dt><dd class="font-medium text-gray-950 dark:text-white">{{ $course->sections->count() }} sections · {{ $videos->count() }} videos · {{ $attachments->count() }} attachments</dd></div>
                    <div><dt class="text-gray-500">Students</dt><dd class="font-medium text-gray-950 dark:text-white">{{ number_format((int) $course->students_count) }}</dd></div>
                    <div><dt class="text-gray-500">Slug</dt><dd class="break-all font-mono text-xs text-gray-700 dark:text-gray-300">{{ $course->slug }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Learning goals</h3>
                <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                    @forelse ($course->goals as $goal)<li class="flex gap-3"><span class="text-success-500">✓</span><span>{{ $goal->goal }}</span></li>@empty<li class="text-gray-500">No learning goals added.</li>@endforelse
                </ul>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Requirements</h3>
                <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                    @forelse ($course->requirements as $requirement)<li class="flex gap-3"><span class="text-primary-500">•</span><span>{{ $requirement->content }}</span></li>@empty<li class="text-gray-500">No requirements added.</li>@endforelse
                </ul>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm text-gray-500">Completed purchases</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $course->purchases->where('payment_status', 'completed')->count() }}</p></div>
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm text-gray-500">Revenue</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format((float) $course->purchases->where('payment_status', 'completed')->sum('price'), 2) }} EGP</p></div>
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm text-gray-500">Average rating</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format((float) $course->reviews->where('is_approved', true)->avg('rating'), 1) }} / 5</p></div>
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm text-gray-500">Wishlists</p><p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $course->wishlists->count() }}</p></div>
        </section>

        <section class="space-y-4">
            <h3 class="text-xl font-bold text-gray-950 dark:text-white">Curriculum</h3>
            @forelse ($course->sections as $section)
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10"><h4 class="font-semibold text-gray-950 dark:text-white">{{ $loop->iteration }}. {{ $section->title }}</h4></div>
                    <div class="space-y-6 p-6">
                        @forelse ($section->videos as $video)
                            <article class="space-y-4 rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div><h5 class="font-semibold text-gray-950 dark:text-white">{{ $video->title }}</h5><p class="mt-1 text-sm text-gray-500">{{ $formatDuration($video->duration) }} · {{ $video->is_free ? 'Free preview' : 'Paid' }} · {{ $video->is_published ? 'Published' : 'Hidden' }}</p></div>
                                </div>
                                @if ($video->description)<p class="whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $video->description }}</p>@endif
                                <x-course-video-player :video="$video" :course="$course" watermark="{{ config('app.name') }}" retryable />
                                @if ($video->attachments->isNotEmpty())
                                    <div><p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Attachments</p><div class="flex flex-wrap gap-2">@foreach ($video->attachments as $attachment)<a href="{{ $this->r2Url($attachment->file_path) }}" target="_blank" rel="noopener" class="rounded-lg bg-primary-50 px-3 py-2 text-sm font-medium text-primary-700 hover:bg-primary-100 dark:bg-primary-400/10 dark:text-primary-400">{{ $attachment->file_name }} @if($attachment->file_size)({{ Number::fileSize($attachment->file_size) }})@endif</a>@endforeach</div></div>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-gray-500">No videos in this section.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">No curriculum sections added yet.</div>
            @endforelse
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10"><h3 class="font-semibold text-gray-950 dark:text-white">Reviews ({{ $course->reviews->count() }})</h3></div>
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($course->reviews as $review)
                        <div class="p-5"><div class="flex justify-between gap-4"><p class="font-medium text-gray-950 dark:text-white">{{ $review->user?->name ?? 'Deleted user' }}</p><p class="text-warning-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p></div><p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $review->comment ?: 'No comment.' }}</p><p class="mt-2 text-xs text-gray-500">{{ $review->is_approved ? 'Approved' : 'Pending approval' }} · {{ $review->created_at?->diffForHumans() }}</p></div>
                    @empty
                        <p class="p-6 text-sm text-gray-500">No reviews yet.</p>
                    @endforelse
                </div>
            </div>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10"><h3 class="font-semibold text-gray-950 dark:text-white">Students and progress</h3></div>
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($course->progress as $item)
                        <div class="p-5"><div class="flex items-center justify-between gap-4"><p class="font-medium text-gray-950 dark:text-white">{{ $item->user?->name ?? 'Deleted user' }}</p><span class="text-sm font-semibold text-primary-600">{{ $item->progress }}%</span></div><div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-primary-500" style="width: {{ min(100, max(0, $item->progress)) }}%"></div></div><p class="mt-2 text-xs text-gray-500">Last access: {{ $item->last_accessed_at?->diffForHumans() ?? 'Never' }}</p></div>
                    @empty
                        <p class="p-6 text-sm text-gray-500">No student progress recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
