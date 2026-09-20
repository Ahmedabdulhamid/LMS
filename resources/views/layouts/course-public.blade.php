<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    @include('partials.application-icons')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="student-authenticated" content="{{ auth('student')->check() ? 'true' : 'false' }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $course->title ?? __('lms.wishlists.title').' · '.app(\App\Services\SettingService::class)->name() }}</title>
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/css/course-show.css', 'resources/css/course-learn.css', 'resources/css/contact.css', 'resources/js/app.js', 'resources/js/course-show.js'])
</head>
<body class="course-public-body">
    <header class="cp-nav">
        <div class="cp-container">
            <a class="cp-brand" href="{{ route('home') }}"><img src="{{ app(\App\Services\SettingService::class)->logoUrl() }}" alt=""><b>{{ app(\App\Services\SettingService::class)->name() }}</b></a>
 <nav><a href="{{ route('home') }}">{{ __('lms.public_nav.home') }}</a><a href="{{ route('categories.index') }}">{{ __('lms.public_nav.categories') }}</a><a href="{{ route('courses.latest') }}">{{ __('lms.public_nav.courses') }}</a><a href="{{ route('subscription-plans.index') }}">{{ __('lms.public_nav.subscription_plans') }}</a><a href="{{ route('contact.index') }}">{{ __('contacts.singular') }}</a></nav>
            <div class="cp-nav-actions">
                @include('filament.components.language-switcher', ['floating' => false])
                @auth('student')
                    @php($wishlistCount = auth('student')->user()->wishlists()->count())
                    <a class="cp-wishlist-link" href="{{ route('wishlists.index') }}" aria-label="{{ __('lms.wishlists.title') }}" x-data="{ count: {{ $wishlistCount }} }" x-on:wishlist-count-updated.window="count = $event.detail.count">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                        <span x-text="count" x-show="count > 0" x-transition.scale>{{ $wishlistCount }}</span>
                    </a>
                    <a href="{{ route('my-courses.index') }}">{{ __('lms.public_nav.my_courses') }}</a>
                    <a href="/students">{{ __('lms.public_nav.dashboard') }}</a>
                @else
                    <a href="{{ route('filament.students.auth.login') }}">{{ __('lms.public_nav.sign_in') }}</a>
                    <a class="cp-nav-btn" href="{{ route('filament.students.auth.register') }}">{{ __('lms.public_nav.create_account') }}</a>
                @endauth
            </div>
        </div>
    </header>
    {{ $slot }}
    @include('partials.site-footer')
    @filamentScripts
</body>
</html>
