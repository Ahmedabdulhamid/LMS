<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'EduPath') }}</title>
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/css/catalog.css', 'resources/js/app.js'])
</head>
<body>
@php
    $ar = app()->getLocale() === 'ar';
    $isCategories = $type === 'categories';
    $title = $isCategories ? ($ar ? 'كل التصنيفات' : 'All categories') : ($type === 'top-rated' ? ($ar ? 'أعلى الكورسات تقييمًا' : 'Top-rated courses') : ($ar ? 'أحدث الكورسات' : 'Latest courses'));
    $eyebrow = $isCategories ? ($ar ? 'اختر مجالك' : 'CHOOSE YOUR FIELD') : ($type === 'top-rated' ? ($ar ? 'الأفضل حسب الطلاب' : 'LEARNERS’ FAVORITES') : ($ar ? 'أضيف حديثًا' : 'FRESHLY ADDED'));
    $imageUrl = static function (?string $path): ?string { if (blank($path)) return null; try { return \Illuminate\Support\Facades\Storage::disk(config('lms-upload.disk'))->url($path); } catch (\Throwable) { return null; } };
    $duration = static function ($seconds) use ($ar): string { $hours = max(1, (int) ceil(((float) $seconds) / 3600)); return $ar ? $hours.' ساعة' : $hours.' hours'; };
@endphp
<header class="header"><div class="container nav"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{ config('app.name', 'EduPath') }}</b></a><nav><a href="{{ route('home') }}">{{ $ar ? 'الرئيسية' : 'Home' }}</a><a href="{{ route('categories.index') }}">{{ $ar ? 'التصنيفات' : 'Categories' }}</a><a href="{{ route('courses.latest') }}">{{ $ar ? 'الكورسات' : 'Courses' }}</a><a href="{{ route('contact.index') }}">{{ $ar ? 'تواصل معنا' : 'Contact' }}</a></nav><div class="nav-actions"><a class="lang" href="{{ route('locale.switch', $ar ? 'en' : 'ar') }}">{{ $ar ? 'EN' : 'ع' }}</a><a class="login" href="{{ route('filament.students.auth.login') }}">{{ $ar ? 'دخول' : 'Sign in' }}</a><a class="btn small" href="{{ route('filament.students.auth.register') }}">{{ $ar ? 'ابدأ الآن' : 'Get started' }}</a></div></div></header>
<main>
    <section class="catalog-hero"><div class="container"><span>{{ $eyebrow }}</span><h1>{{ $title }}</h1><p>{{ $ar ? 'استكشف كل الخيارات المتاحة واختر خطوتك القادمة بثقة.' : 'Explore every available option and choose your next step with confidence.' }}</p></div></section>
    <section class="section catalog-content"><div class="container">
        @if($isCategories)
            <div class="category-grid">@forelse($items as $category)<a class="category-card" href="{{ route('courses.latest') }}"><span class="category-icon">@if($imageUrl($category['icon'] ?? null))<img src="{{ $imageUrl($category['icon']) }}" alt="">@else<span>{{ mb_substr($category['name'],0,1) }}</span>@endif</span><strong>{{ $category['name'] }}</strong><small>{{ $category['courses_count'] }} {{ $ar ? 'كورس' : 'courses' }}</small><i>↗</i></a>@empty<div class="home-empty">{{ $ar ? 'لا توجد تصنيفات حاليًا.' : 'No categories are available yet.' }}</div>@endforelse</div>
        @else
            <div class="course-grid">@forelse($items as $course) @include('partials.course-card', ['course'=>$course,'imageUrl'=>$imageUrl,'duration'=>$duration,'ar'=>$ar,'featured'=>$type==='top-rated']) @empty<div class="home-empty">{{ $ar ? 'لا توجد كورسات منشورة حاليًا.' : 'No published courses are available yet.' }}</div>@endforelse</div>
        @endif
    </div></section>
</main>
<footer><div class="container footer"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{ config('app.name','EduPath') }}</b></a><p>© {{ date('Y') }} {{ config('app.name','EduPath') }}</p><div><a href="{{ route('home') }}">{{ $ar ? 'الرئيسية' : 'Home' }}</a></div></div></footer>
</body></html>
