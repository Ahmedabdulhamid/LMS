<main class="wl-page">
    <section class="wl-hero">
        <div class="cp-container wl-hero-copy">
            <span class="wl-eyebrow">{{ __('lms.my_courses.eyebrow') }}</span>
            <h1>{{ __('lms.my_courses.title') }}</h1>
            <p>{{ __('lms.my_courses.subtitle') }}</p>
        </div>
    </section>

    <section class="cp-container wl-content">
        @if($courses->isEmpty())
            <div class="wl-empty">
                <h2>{{ __('lms.my_courses.empty_title') }}</h2>
                <p>{{ __('lms.my_courses.empty_description') }}</p>
                <a href="{{ route('subscription-plans.index') }}">{{ __('lms.my_courses.browse_plans') }} <span>→</span></a>
            </div>
        @else
            <div class="wl-toolbar">
                <div><span>{{ __('lms.my_courses.collection') }}</span><h2>{{ trans_choice('lms.my_courses.course_count', $courses->count(), ['count' => $courses->count()]) }}</h2></div>
            </div>
            <div class="wl-grid">
                @foreach($courses as $course)
                    <article class="wl-card" wire:key="my-course-{{ $course->id }}">
                        <a class="wl-cover" href="{{ route('courses.learn', $course->slug) }}">
                            @if($this->thumbnailUrl($course->thumbnail))
                                <img src="{{ $this->thumbnailUrl($course->thumbnail) }}" alt="{{ $course->title }}">
                            @else
                                <span>{{ mb_substr($course->title, 0, 1) }}</span>
                            @endif
                        </a>
                        <div class="wl-card-body">
                            <div class="wl-meta"><span>{{ $course->category?->name ?? __('lms.my_courses.learning') }}</span></div>
                            <h3><a href="{{ route('courses.learn', $course->slug) }}">{{ $course->title }}</a></h3>
                            <p class="wl-author">{{ __('lms.wishlists.by', ['name' => $course->instructor?->name ?? config('app.name')]) }}</p>
                            <div class="wl-card-footer"><a href="{{ route('courses.learn', $course->slug) }}">{{ __('lms.my_courses.continue') }} <span>→</span></a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</main>
