<article class="course-card reveal">
    <div class="course-cover {{ $course['thumbnail'] ? '' : 'course-placeholder' }}">
        @if($imageUrl($course['thumbnail'] ?? null))<img src="{{ $imageUrl($course['thumbnail']) }}" alt="{{ $course['title'] }}">@else<div><span>✦</span><b>{{ mb_substr($course['title'], 0, 1) }}</b></div>@endif
        <span class="course-label">{{ $featured ? ($ar ? 'الأعلى تقييمًا' : 'Top rated') : ($ar ? 'جديد' : 'New') }}</span>
        <span class="course-level">{{ ucfirst($course['level'] ?? 'All levels') }}</span>
    </div>
    <div class="course-body">
        <div class="course-meta"><span>{{ $course['category']['name'] ?? ($ar ? 'تعليم' : 'Learning') }}</span><span class="course-rating">★ {{ number_format((float) ($course['course_reviews_avg_rating'] ?? 0), 1) }}</span></div>
        <h3>{{ $course['title'] }}</h3>
        <p>{{ $ar ? 'بواسطة' : 'By' }} {{ $course['instructor']['name'] ?? config('app.name') }}</p>
        <div class="course-facts"><span>◷ {{ $duration($course['duration'] ?? 0) }}</span><span>◎ {{ number_format((int) ($course['students_count'] ?? 0)) }} {{ $ar ? 'طالب' : 'learners' }}</span></div>
        <div class="course-footer"><strong>{{ ($course['price_after_discount'] ?? $course['price'] ?? 0) > 0 ? number_format((float) ($course['price_after_discount'] ?: $course['price']), 2).' EGP' : ($ar ? 'مجاني' : 'Free') }}</strong><a href="{{ route('courses.show', $course['slug']) }}">{{ $ar ? 'عرض الكورس' : 'View course' }} →</a></div>
    </div>
</article>
