@php
    $settings = app(\App\Services\SettingService::class);
    $siteName = $settings->name();
    $dashboardUrl = auth('admin')->check() ? route('filament.admin.pages.dashboard')
        : (auth('instructor')->check() ? route('filament.instructors.pages.dashboard')
        : (auth('student')->check() ? route('filament.students.pages.dashboard') : null));
@endphp
<footer class="site-footer" aria-label="{{ __('footer.support') }}">
    <div class="sf-glow sf-glow-one"></div><div class="sf-glow sf-glow-two"></div>
    <div class="sf-inner">
        <div class="sf-top">
            <div class="sf-brand-block">
                <a class="sf-brand" href="{{ route('home') }}"><span><img src="{{ $settings->logoUrl() }}" alt=""></span><strong>{{ $siteName }}</strong></a>
                <p>{{ __('footer.tagline') }}</p>
                <div class="sf-trust"><i>✓</i>{{ __('footer.secure') }}</div>
            </div>
            <div class="sf-column"><h3>{{ __('footer.explore') }}</h3><a href="{{ route('home') }}">{{ __('footer.home') }}</a><a href="{{ route('courses.latest') }}">{{ __('footer.courses') }}</a><a href="{{ route('categories.index') }}">{{ __('footer.categories') }}</a><a href="{{ route('subscription-plans.index') }}">{{ __('footer.plans') }}</a></div>
            <div class="sf-column"><h3>{{ __('footer.support') }}</h3><a href="{{ route('faqs.index') }}">{{ __('footer.faqs') }}</a><a href="{{ route('contact.index') }}">{{ __('footer.contact') }}</a></div>
            <div class="sf-column"><h3>{{ __('footer.account') }}</h3>
                @if($dashboardUrl)
                    <a href="{{ $dashboardUrl }}">{{ __('footer.dashboard') }}</a>
                    @auth('student')<a href="{{ route('my-courses.index') }}">{{ __('footer.my_courses') }}</a>@endauth
                @else
                    <a href="{{ route('filament.students.auth.login') }}">{{ __('footer.student_login') }}</a>
                    <a href="{{ route('filament.instructors.auth.login') }}">{{ __('footer.instructor_login') }}</a>
                    <a href="{{ route('filament.students.auth.register') }}">{{ __('footer.student_signup') }}</a>
                    <a href="{{ route('filament.instructors.auth.register') }}">{{ __('footer.instructor_signup') }}</a>
                @endif
            </div>
        </div>
        <div class="sf-bottom"><span>© {{ now()->year }} {{ $siteName }}. {{ __('footer.rights') }}</span><a href="{{ route('locale.switch', app()->isLocale('ar') ? 'en' : 'ar') }}">{{ app()->isLocale('ar') ? 'English' : 'العربية' }}</a></div>
    </div>
</footer>
<style>
body>footer:not(.site-footer){display:none!important}
.site-footer{position:relative;isolation:isolate;overflow:hidden;margin-top:clamp(3rem,7vw,6rem);color:#dbe5f4;background:#07172f;font-family:inherit}.sf-glow{position:absolute;z-index:-1;width:25rem;height:25rem;border-radius:50%;filter:blur(1px);opacity:.22}.sf-glow-one{inset:-13rem auto auto -8rem;background:radial-gradient(circle,#f59e0b,transparent 68%)}.sf-glow-two{inset:auto -10rem -17rem auto;background:radial-gradient(circle,#2563eb,transparent 68%)}.sf-inner{width:min(1180px,calc(100% - 2rem));margin:auto;padding:4rem 0 0}.sf-top{display:grid;grid-template-columns:1.65fr repeat(3,1fr);gap:clamp(2rem,5vw,5rem)}.sf-brand-block{max-width:340px}.sf-brand{display:inline-flex;align-items:center;gap:.8rem;color:#fff;text-decoration:none}.sf-brand>span{display:grid;width:3.2rem;height:3.2rem;place-items:center;border:1px solid rgba(255,255,255,.13);border-radius:1rem;background:rgba(255,255,255,.08)}.sf-brand img{width:2.4rem;height:2.4rem;object-fit:contain}.sf-brand strong{font-size:1.2rem}.sf-brand-block>p{margin:1.25rem 0;color:#9caec6;line-height:1.85}.sf-trust{display:inline-flex;align-items:center;gap:.55rem;padding:.55rem .8rem;border:1px solid rgba(16,185,129,.2);border-radius:999px;color:#a7f3d0;background:rgba(16,185,129,.08);font-size:.72rem;font-weight:700}.sf-trust i{display:grid;width:1.1rem;height:1.1rem;place-items:center;border-radius:50%;background:#10b981;color:#052e25;font-style:normal}.sf-column{display:flex;flex-direction:column;align-items:flex-start;gap:.8rem}.sf-column h3{margin:.2rem 0 .65rem;color:#fff;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase}.sf-column a{position:relative;color:#9caec6;font-size:.86rem;text-decoration:none;transition:.2s}.sf-column a:hover{color:#fbbf24;transform:translateX(3px)}[dir=rtl] .sf-column a:hover{transform:translateX(-3px)}.sf-bottom{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-top:3.2rem;padding:1.35rem 0;border-top:1px solid rgba(255,255,255,.1);color:#7589a5;font-size:.75rem}.sf-bottom a{padding:.4rem .7rem;border:1px solid rgba(255,255,255,.12);border-radius:999px;color:#cbd5e1;text-decoration:none}@media(max-width:850px){.sf-top{grid-template-columns:1.4fr 1fr 1fr}.sf-column:last-child{grid-column:2/-1}.sf-brand-block{grid-row:span 2}}@media(max-width:580px){.sf-inner{padding-top:3rem}.sf-top{grid-template-columns:1fr 1fr;gap:2.2rem 1.4rem}.sf-brand-block{grid-column:1/-1;grid-row:auto;max-width:none}.sf-column:last-child{grid-column:auto}.sf-bottom{align-items:flex-start;flex-direction:column}}
</style>
