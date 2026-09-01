<main class="wl-page">
    <section class="wl-hero">
        <div class="wl-orb wl-orb-one"></div><div class="wl-orb wl-orb-two"></div>
        <div class="cp-container wl-hero-inner">
            <div class="wl-hero-copy">
                <span class="wl-eyebrow"><i>♥</i> {{ __('lms.wishlists.eyebrow') }}</span>
                <h1>{{ __('lms.wishlists.title') }}</h1>
                <p>{{ __('lms.wishlists.subtitle') }}</p>
            </div>
            <div class="wl-hero-stat">
                <span>♥</span>
                <strong>{{ $wishlists->total() }}</strong>
                <small>{{ __('lms.wishlists.saved_label') }}</small>
            </div>
        </div>
    </section>

    <section class="cp-container wl-content">
        @if($wishlists->isEmpty())
            <div class="wl-empty">
                <div class="wl-empty-icon"><span>♡</span><i>✦</i></div>
                <span class="wl-empty-kicker">{{ __('lms.wishlists.empty_kicker') }}</span>
                <h2>{{ __('lms.wishlists.empty_title') }}</h2>
                <p>{{ __('lms.wishlists.empty_description') }}</p>
                <a href="{{ route('courses.latest') }}">{{ __('lms.wishlists.browse_courses') }} <span>→</span></a>
            </div>
        @else
            <div class="wl-toolbar">
                <div><span>{{ __('lms.wishlists.collection') }}</span><h2>{{ trans_choice('lms.wishlists.saved_count', $wishlists->total(), ['count' => $wishlists->total()]) }}</h2></div>
                <a href="{{ route('courses.latest') }}">＋ {{ __('lms.wishlists.discover_more') }}</a>
            </div>

            <div class="wl-grid">
                @foreach($wishlists as $wishlist)
                    @php($course = $wishlist->course)
                    <article class="wl-card" wire:key="wishlist-{{ $wishlist->id }}">
                        <div class="wl-cover-wrap">
                            <a class="wl-cover" href="{{ route('courses.show', $course->slug) }}">
                                @if($this->thumbnailUrl($course->thumbnail))
                                    <img src="{{ $this->thumbnailUrl($course->thumbnail) }}" alt="{{ $course->title }}">
                                @else
                                    <span>{{ mb_substr($course->title, 0, 1) }}</span>
                                @endif
                                <div class="wl-cover-shade"></div>
                                <em>{{ __('lms.course_show.levels.'.$course->level) }}</em>
                            </a>
                            <button class="wl-heart" type="button" wire:click="remove({{ $wishlist->id }})" wire:loading.attr="disabled" wire:target="remove({{ $wishlist->id }})" aria-label="{{ __('lms.wishlists.remove') }}"><span wire:loading.remove wire:target="remove({{ $wishlist->id }})">♥</span><span wire:loading wire:target="remove({{ $wishlist->id }})">···</span></button>
                        </div>
                        <div class="wl-card-body">
                            <div class="wl-meta"><span>{{ $course->category?->name ?? __('lms.wishlists.learning') }}</span><span>★ {{ number_format((float) ($course->course_reviews_avg_rating ?? 0), 1) }}</span></div>
                            <h3><a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a></h3>
                            <p class="wl-author">{{ __('lms.wishlists.by', ['name' => $course->instructor?->name ?? config('app.name')]) }}</p>
                            <div class="wl-facts"><span>◷ {{ max(1, (int) ceil(((float) $course->duration) / 3600)) }} {{ __('lms.wishlists.hours') }}</span><span>▣ {{ $course->number_lessons ?? 0 }} {{ __('lms.wishlists.lessons') }}</span></div>
                            <div class="wl-card-footer">
                                <div><small>{{ __('lms.wishlists.price') }}</small><strong>{{ (float) ($course->price_after_discount ?: $course->price) > 0 ? number_format((float) ($course->price_after_discount ?: $course->price), 2).' EGP' : __('lms.course_show.free') }}</strong></div>
                                <a href="{{ route('courses.show', $course->slug) }}">{{ __('lms.wishlists.view_course') }} <span>→</span></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="wl-pagination">{{ $wishlists->links() }}</div>
        @endif
    </section>
</main>
