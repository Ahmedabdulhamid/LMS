<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Learn practical skills from expert instructors through focused, flexible online courses.">
    <title>{{ config('app.name', 'EduPath') }} — Learn without limits</title>
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/css/catalog.css', 'resources/js/app.js'])
</head>
<body>
@php
    $ar = app()->getLocale() === 'ar';
    $imageUrl = static function (?string $path): ?string {
        if (blank($path)) return null;
        try { return \Illuminate\Support\Facades\Storage::disk(config('lms-upload.disk'))->url($path); }
        catch (\Throwable) { return null; }
    };
    $duration = static function ($seconds) use ($ar): string {
        $hours = max(1, (int) ceil(((float) $seconds) / 3600));
        return $ar ? $hours.' ساعة' : $hours.' hours';
    };
@endphp

<header class="header" id="top">
    <div class="container nav">
        <a class="brand" href="#top"><img src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{ config('app.name', 'EduPath') }}</b></a>
        <nav>
            <a href="#categories">{{ $ar ? 'التصنيفات' : 'Categories' }}</a>
            <a href="#courses">{{ $ar ? 'الكورسات' : 'Courses' }}</a>
            <a href="{{ route('subscription-plans.index') }}">{{ $ar ? 'الاشتراكات' : 'Subscriptions' }}</a>
            <a href="#reviews">{{ $ar ? 'آراء الطلاب' : 'Stories' }}</a>
        </nav>
        <div class="nav-actions">
            <a class="lang" href="{{ route('locale.switch', $ar ? 'en' : 'ar') }}">{{ $ar ? 'EN' : 'ع' }}</a>
            <a class="login" href="/students/login">{{ $ar ? 'دخول' : 'Sign in' }}</a>
            <a class="btn small" href="/students/register">{{ $ar ? 'ابدأ الآن' : 'Start learning' }}</a>
            <button class="menu" aria-label="Menu" aria-expanded="false"><i></i><i></i><i></i></button>
        </div>
    </div>
    <div class="mobile">
        <a href="#categories">{{ $ar ? 'التصنيفات' : 'Categories' }}</a>
        <a href="#courses">{{ $ar ? 'الكورسات' : 'Courses' }}</a>
        <a href="{{ route('subscription-plans.index') }}">{{ $ar ? 'الاشتراكات' : 'Subscriptions' }}</a>
        <a href="#reviews">{{ $ar ? 'آراء الطلاب' : 'Stories' }}</a>
        <a href="/students/login">{{ $ar ? 'دخول الطالب' : 'Student sign in' }}</a>
    </div>
</header>

<main>
    <section class="hero home-hero">
        <div class="dots"></div>
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <span class="kicker">{{ $ar ? 'تعلم اليوم. اصنع الغد.' : 'LEARN TODAY. BUILD TOMORROW.' }}</span>
                <h1>{{ $ar ? 'المهارة التي تغيّر' : 'The skill that changes' }} <em>{{ $ar ? 'كل شيء تبدأ هنا.' : 'everything starts here.' }}</em></h1>
                <p>{{ $ar ? 'تعلّم من خبراء حقيقيين، طبّق من أول درس، وابنِ مستقبلك خطوة بخطوة في تجربة صُممت حول طموحك.' : 'Learn from real experts, build from your first lesson, and move toward your future in an experience designed around your ambition.' }}</p>
                <div class="hero-actions">
                    <a class="btn" href="#courses">{{ $ar ? 'استكشف الكورسات' : 'Explore courses' }} <b>→</b></a>
                    <a class="btn outline" href="/instructors/register">{{ $ar ? 'درّس معنا' : 'Teach with us' }}</a>
                </div>
                <div class="trust">
                    <div class="faces"><i>AM</i><i>SK</i><i>NY</i><i>+</i></div>
                    <p><b>{{ $ar ? 'مجتمع يتعلم كل يوم' : 'A community that grows daily' }}</b><small>{{ $ar ? 'طلاب ومدرسون يجتمعون حول هدف واحد' : 'Learners and instructors, one shared goal' }}</small></p>
                </div>
            </div>
            <div class="hero-art reveal">
                <div class="halo"></div>
                <div class="hero-orbit orbit-one">✦</div><div class="hero-orbit orbit-two">01</div>
                <img src="{{ asset('images/learning-platform-logo.png') }}" alt="{{ config('app.name') }}">
                <aside class="float progress"><i>↗</i><p><small>{{ $ar ? 'تقدمك الأسبوعي' : 'Weekly progress' }}</small><b>84%</b><span><u></u></span></p></aside>
                <aside class="float lesson"><i>▶</i><p><small>{{ $ar ? 'جاهز للمشاهدة' : 'Ready to watch' }}</small><b>{{ $ar ? 'درسك القادم' : 'Your next lesson' }}</b></p></aside>
                <div class="badge">✓ {{ $ar ? 'تعلّم بلا حدود' : 'Learn without limits' }}</div>
            </div>
        </div>
        <div class="container stats reveal">
            <div><b>{{ $latestCourses->count() + $topRatedCourses->count() }}+</b><span>{{ $ar ? 'كورس مختار' : 'Curated courses' }}</span></div>
            <div><b>{{ $categories->count() }}+</b><span>{{ $ar ? 'مجال تعليمي' : 'Learning fields' }}</span></div>
            <div><b>4.9</b><span>{{ $ar ? 'متوسط التقييم' : 'Average rating' }}</span></div>
            <div><b>24/7</b><span>{{ $ar ? 'تعلّم في أي وقت' : 'Learn anytime' }}</span></div>
        </div>
    </section>

    <section class="section category-section" id="categories">
        <div class="container">
            <header class="section-head reveal"><span>{{ $ar ? 'ابحث عن شغفك' : 'FIND YOUR DIRECTION' }}</span><h2>{{ $ar ? 'مساحة لكل طموح' : 'A space for every ambition' }}</h2><p>{{ $ar ? 'ابدأ من المجال الذي يحمسك، واترك لنا ترتيب الطريق.' : 'Start with what excites you, and let us make the path feel clear.' }}</p><a class="home-all-link" href="{{ route('categories.index') }}">{{ $ar ? 'عرض الكل' : 'View all' }} →</a></header>
            <div class="category-grid">
                @forelse($categories as $category)
                    <a class="category-card reveal" href="#courses">
                        <span class="category-icon">
                            @if($imageUrl($category['icon'] ?? null))<img src="{{ $imageUrl($category['icon']) }}" alt="">@else<span>{{ mb_substr($category['name'], 0, 1) }}</span>@endif
                        </span>
                        <strong>{{ $category['name'] }}</strong>
                        <small>{{ $category['courses_count'] }} {{ $ar ? 'كورس' : 'courses' }}</small>
                        <i>↗</i>
                    </a>
                @empty
                    @foreach(['Development','Design','Business','Marketing','Languages','Lifestyle'] as $category)
                        <div class="category-card reveal"><span class="category-icon"><span>{{ mb_substr($category,0,1) }}</span></span><strong>{{ $category }}</strong><small>{{ $ar ? 'قريبًا' : 'Coming soon' }}</small><i>↗</i></div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section class="section courses-section" id="courses">
        <div class="container">
            <div class="home-split reveal"><div><span>{{ $ar ? 'اختيارات الطلاب' : 'LEARNERS’ FAVORITES' }}</span><h2>{{ $ar ? 'كورسات تستحق وقتك' : 'Courses worth your time' }}</h2><a class="home-all-link" href="{{ route('courses.top-rated') }}">{{ $ar ? 'عرض الكل' : 'View all' }} →</a></div><p>{{ $ar ? 'محتوى عملي، مدرسون مميزون، وتجارب تعلم حصلت على أعلى التقييمات.' : 'Practical content, remarkable instructors, and learning experiences rated by real students.' }}</p></div>
            <div class="course-grid">
                @forelse($topRatedCourses as $course)
                    @include('partials.course-card', ['course' => $course, 'imageUrl' => $imageUrl, 'duration' => $duration, 'ar' => $ar, 'featured' => true])
                @empty
                    <div class="home-empty">{{ $ar ? 'ستظهر الكورسات الأعلى تقييمًا هنا قريبًا.' : 'Top-rated courses will appear here soon.' }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section latest-section">
        <div class="container">
            <div class="home-split reveal"><div><span>{{ $ar ? 'جديد المنصة' : 'FRESH FROM THE STUDIO' }}</span><h2>{{ $ar ? 'أحدث ما يمكنك تعلمه' : 'The newest ways to grow' }}</h2><a class="home-all-link" href="{{ route('courses.latest') }}">{{ $ar ? 'عرض الكل' : 'View all' }} →</a></div><p>{{ $ar ? 'ابدأ بخطوة جديدة مع أحدث الكورسات المضافة للمنصة.' : 'Take your next step with the newest courses added to the platform.' }}</p></div>
            <div class="course-grid">
                @forelse($latestCourses as $course)
                    @include('partials.course-card', ['course' => $course, 'imageUrl' => $imageUrl, 'duration' => $duration, 'ar' => $ar, 'featured' => false])
                @empty
                    <div class="home-empty">{{ $ar ? 'كورسات جديدة قيد التجهيز.' : 'New courses are being prepared.' }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section stories-section" id="reviews">
        <div class="container">
            <header class="section-head light reveal"><span>{{ $ar ? 'قصص حقيقية' : 'REAL STORIES' }}</span><h2>{{ $ar ? 'النجاح يبدو أجمل عندما يُحكى' : 'Growth sounds better in their words' }}</h2><p>{{ $ar ? 'تجارب من طلاب حوّلوا وقتهم إلى مهارة.' : 'Experiences from learners who turned their time into momentum.' }}</p></header>
            <div class="review-grid">
                @forelse($reviews as $review)
                    <article class="review-card reveal">
                        <div class="review-stars">{{ str_repeat('★', (int) $review['rating']) }}{{ str_repeat('☆', 5 - (int) $review['rating']) }}</div>
                        <blockquote>“{{ $review['comment'] }}”</blockquote>
                        <footer><span>{{ mb_substr($review['user']['name'] ?? 'L', 0, 1) }}</span><div><b>{{ $review['user']['name'] ?? ($ar ? 'طالب بالمنصة' : 'Platform learner') }}</b><small>{{ $review['course']['title'] ?? '' }}</small></div></footer>
                    </article>
                @empty
                    <article class="review-card reveal"><div class="review-stars">★★★★★</div><blockquote>“{{ $ar ? 'منصة تجعل التعلم واضحًا، ممتعًا، وقابلًا للتطبيق.' : 'A platform that makes learning clear, engaging, and immediately useful.' }}”</blockquote><footer><span>L</span><div><b>{{ $ar ? 'أحد طلابنا' : 'One of our learners' }}</b><small>{{ config('app.name') }}</small></div></footer></article>
                @endforelse
            </div>
        </div>
    </section>

    <section class="cta"><div class="container cta-card reveal"><div><span>{{ $ar ? 'هذه لحظتك' : 'THIS IS YOUR MOMENT' }}</span><h2>{{ $ar ? 'ابدأ صغيرًا. وصل بعيدًا.' : 'Start small. Go remarkably far.' }}</h2><p>{{ $ar ? 'حساب واحد يفتح لك عالمًا كاملًا من المعرفة.' : 'One account opens a whole world of practical knowledge.' }}</p></div><aside><a class="btn white" href="{{ route('filament.students.auth.login') }}">{{ $ar ? 'انضم كطالب' : 'Join as a learner' }}</a><a class="btn clear" href="/instructors/register">{{ $ar ? 'انضم كمدرس' : 'Become an instructor' }}</a></aside></div></section>
</main>

<footer><div class="container footer"><a class="brand" href="#top"><img src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{ config('app.name','EduPath') }}</b></a><p>© {{ date('Y') }} {{ config('app.name','EduPath') }}. {{ $ar ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</p><div><a href="/students/login">{{ $ar ? 'دخول الطالب' : 'Student login' }}</a><a href="/instructors/login">{{ $ar ? 'دخول المدرس' : 'Instructor login' }}</a></div></div></footer>
</body>
</html>
