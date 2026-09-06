@php
    $ar = app()->isLocale('ar');
    $videos = $course->sections->flatMap->videos;
    $totalLessons = $videos->count();
    $currentLesson = $videos->search(fn ($video) => $video->id === $selectedVideoId);
    $formatDuration = static function ($seconds): string {
        $seconds = (int) $seconds;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    };
@endphp

<main class="learn-page">
    <header class="learn-heading">
        <div>
            <a href="{{ route('courses.show', $course) }}">{{ $ar ? 'العودة إلى تفاصيل الكورس' : 'Back to course details' }}</a>
            <span>{{ $ar ? 'مساحة التعلم' : 'Learning space' }}</span>
            <h1>{{ $course->title }}</h1>
            <p>{{ $ar ? 'مع' : 'With' }} {{ $course->instructor?->name }}</p>
        </div>
        <div class="learn-progress">
            <strong>{{ $totalLessons }}</strong>
            <span>{{ $ar ? 'درسًا متاحًا' : 'available lessons' }}</span>
        </div>
    </header>

    <div class="learn-layout">
        <section class="learn-content">
            @if($this->selectedVideo)
                <div class="learn-player" wire:key="learn-player-{{ $this->selectedVideo->id }}">
                    <x-course-video-player
                        :video="$this->selectedVideo"
                        :course="$course"
                        :poster="$this->thumbnailUrl()"
                        :watermark="auth('student')->user()?->email ?? config('app.name')"
                        :autoplay="$autoplaySelectedVideo"
                        :resume-at="($lessonProgress[$this->selectedVideo->id]['completed'] ?? false) ? 0 : ($lessonProgress[$this->selectedVideo->id]['position'] ?? 0)"
                    />
                </div>

                <article class="learn-lesson-info">
                    <div>
                        <span>{{ $ar ? 'الدرس' : 'Lesson' }} {{ $currentLesson === false ? '' : $currentLesson + 1 }} / {{ $totalLessons }}</span>
                        <h2>{{ $this->selectedVideo->title }}</h2>
                    </div>
                    <small>{{ $formatDuration($this->selectedVideo->duration) }}</small>
                    @if($this->selectedVideo->description)
                        <p>{{ $this->selectedVideo->description }}</p>
                    @endif
                </article>
            @else
                <div class="learn-empty">
                    <strong>{{ $ar ? 'لا توجد دروس منشورة حتى الآن' : 'No published lessons yet' }}</strong>
                    <p>{{ $ar ? 'سيظهر محتوى الكورس هنا بمجرد نشر المدرس للدروس.' : 'Course content will appear here when the instructor publishes lessons.' }}</p>
                </div>
            @endif
        </section>

        <aside class="learn-curriculum">
            <div class="learn-curriculum-head">
                <span>{{ $ar ? 'محتوى الكورس' : 'Course content' }}</span>
                <b>{{ $totalLessons }} {{ $ar ? 'درس' : 'lessons' }}</b>
            </div>

            @forelse($course->sections as $section)
                <details @if($section->videos->contains('id', $selectedVideoId) || $loop->first) open @endif>
                    <summary>
                        <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <b>{{ $section->title }}</b>
                        <small>{{ $section->videos->count() }}</small>
                    </summary>
                    <div class="learn-lessons">
                        @forelse($section->videos as $video)
                            <button
                                type="button"
                                wire:click="selectVideo({{ $video->id }})"
                                @class(['active' => $selectedVideoId === $video->id])
                            >
                                <i>{{ ($lessonProgress[$video->id]['completed'] ?? false) ? '✓' : ($selectedVideoId === $video->id ? '▶' : str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)) }}</i>
                                <span><b>{{ $video->title }}</b><small>{{ $formatDuration($video->duration) }}</small></span>
                                @if (($lessonProgress[$video->id]['progress'] ?? 0) > 0)
                                    <em>{{ $lessonProgress[$video->id]['progress'] }}%</em>
                                @endif
                            </button>
                        @empty
                            <p>{{ $ar ? 'لا توجد دروس منشورة في هذا القسم.' : 'No published lessons in this section.' }}</p>
                        @endforelse
                    </div>
                </details>
            @empty
                <div class="learn-empty compact">{{ $ar ? 'لا يوجد محتوى منشور.' : 'No published content.' }}</div>
            @endforelse
        </aside>
    </div>
</main>
