<x-filament-panels::page.simple>
    @php($email = filament()->auth()->user()->getEmailForVerification())
    <style>
        .verify-card{overflow:hidden;border:1px solid rgb(228 228 231);border-radius:1.6rem;background:rgb(255 255 255);box-shadow:0 20px 55px rgba(24,24,27,.13)}.dark .verify-card{border-color:rgb(63 63 70);background:rgb(24 24 27)}.verify-head{padding:2.5rem 2.25rem 2.15rem;text-align:center;background:rgb(24 24 27);color:white}.verify-icon{display:grid;place-items:center;width:4rem;height:4rem;margin:0 auto 1.15rem;border-radius:1.15rem;background:rgb(245 158 11);box-shadow:0 10px 28px rgba(245,158,11,.25)}.verify-icon svg{width:2rem}.verify-kicker{margin:0 0 .55rem;color:rgb(251 191 36);font-size:.7rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.verify-head h1{margin:0;font-size:1.75rem;font-weight:800;line-height:1.25}.verify-body{padding:2rem 2.25rem;text-align:center}.verify-message{margin:0;color:rgb(82 82 91);line-height:1.8}.dark .verify-message{color:rgb(212 212 216)}.verify-email{display:block;margin:.9rem 0 1.25rem;padding:.8rem 1rem;border:1px solid rgb(228 228 231);border-radius:.8rem;background:rgb(250 250 250);color:rgb(39 39 42);font-weight:800;overflow-wrap:anywhere;direction:ltr}.dark .verify-email{border-color:rgb(63 63 70);background:rgb(39 39 42);color:white}.verify-tip{display:flex;gap:.65rem;margin:0;padding:1rem;text-align:start;border-radius:.85rem;background:rgb(255 251 235);color:rgb(146 64 14);font-size:.82rem;line-height:1.65}.dark .verify-tip{background:rgba(245,158,11,.1);color:rgb(253 230 138)}.verify-actions{padding:1.45rem 2.25rem;border-top:1px solid rgb(228 228 231);text-align:center;background:rgb(250 250 250)}.dark .verify-actions{border-color:rgb(63 63 70);background:rgb(39 39 42)}.verify-actions p{margin:0 0 .8rem;color:rgb(113 113 122);font-size:.82rem}.verify-actions .fi-ac-action{display:inline-flex!important;padding:.72rem 1.15rem!important;border-radius:.7rem!important;background:rgb(245 158 11)!important;color:rgb(24 24 27)!important;font-weight:800!important;text-decoration:none!important}.verify-security{margin:1.25rem 0 0;color:rgb(161 161 170);font-size:.7rem;line-height:1.6}@media(max-width:520px){.verify-head,.verify-body,.verify-actions{padding-left:1.3rem;padding-right:1.3rem}.verify-head h1{font-size:1.5rem}}
    </style>
    <section class="verify-card">
        <header class="verify-head">
            <div class="verify-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
            <p class="verify-kicker">{{ __('email-verification.page.eyebrow') }}</p>
            <h1>{{ __('email-verification.page.heading') }}</h1>
        </header>
        <div class="verify-body">
            <p class="verify-message">{{ __('email-verification.page.message') }}</p>
            <strong class="verify-email">{{ $email }}</strong>
            <p class="verify-tip"><span>💡</span><span>{{ __('email-verification.page.tip') }}</span></p>
            <p class="verify-security">🔒 {{ __('email-verification.page.security', ['minutes' => config('auth.verification.expire', 60)]) }}</p>
        </div>
        <footer class="verify-actions">
            <p>{{ __('email-verification.page.not_received') }}</p>
            {{ $this->resendNotificationAction->label(__('email-verification.page.resend')) }}
        </footer>
    </section>
</x-filament-panels::page.simple>
