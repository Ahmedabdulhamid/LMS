<div @class(['lms-language-switcher', 'is-floating' => $floating ?? false]) dir="ltr" aria-label="{{ __('instructor.language_switcher.label') }}">
    <a href="{{ route('locale.switch', 'en') }}" hreflang="en" @class(['is-active' => app()->isLocale('en')])>{{ __('instructor.language_switcher.english') }}</a>
    <span></span>
    <a href="{{ route('locale.switch', 'ar') }}" hreflang="ar" @class(['is-active' => app()->isLocale('ar')])>{{ __('instructor.language_switcher.arabic') }}</a>
</div>

<style>
    .lms-language-switcher {
        z-index: 50;
        display: flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem;
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: 9999px;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        backdrop-filter: blur(12px);
    }

    .lms-language-switcher.is-floating {
        position: fixed;
        inset-block-start: 1rem;
        inset-inline-end: 1rem;
    }

    .dark .lms-language-switcher { background: rgba(17, 24, 39, .9); }
    .lms-language-switcher a { padding: .35rem .55rem; border-radius: 9999px; color: #64748b; font-size: .7rem; font-weight: 800; }
    .lms-language-switcher a.is-active { color: white; background: #d97706; }
    .lms-language-switcher span { width: 1px; height: .9rem; background: #cbd5e1; }
</style>
