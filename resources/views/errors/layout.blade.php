<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $status }} — {{ $title }}</title>
    <style>
        :root { color-scheme: dark; --accent: {{ $accent }}; --accent-soft: {{ $accentSoft }}; }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #f8fafc; background: #060913; }
        a { color: inherit; text-decoration: none; }
        .error-shell { position: relative; min-height: 100vh; min-height: 100svh; display: grid; place-items: center; overflow: hidden; padding: 2rem; isolation: isolate; }
        .error-shell::before { content: ""; position: absolute; inset: 0; z-index: -3; background: radial-gradient(circle at 15% 18%, var(--accent-soft), transparent 28%), radial-gradient(circle at 88% 82%, rgba(37,99,235,.14), transparent 30%), linear-gradient(145deg,#050713 0%,#0b1020 48%,#070a12 100%); }
        .error-grid { position: absolute; inset: 0; z-index: -2; opacity: .13; background-image: linear-gradient(rgba(148,163,184,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.2) 1px,transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom,black,transparent 85%); }
        .error-orb { position: absolute; border-radius: 9999px; filter: blur(2px); opacity: .32; animation: drift 10s ease-in-out infinite alternate; }
        .error-orb.one { width: 20rem; height: 20rem; top: -9rem; inset-inline-end: -5rem; border: 1px solid var(--accent); box-shadow: inset 0 0 80px var(--accent-soft); }
        .error-orb.two { width: 9rem; height: 9rem; bottom: 8%; inset-inline-start: 6%; background: var(--accent-soft); filter: blur(35px); animation-delay: -4s; }
        .language { position: absolute; z-index: 5; inset-block-start: 1.25rem; inset-inline-end: 1.25rem; display: flex; align-items: center; gap: .25rem; padding: .3rem; border: 1px solid rgba(148,163,184,.2); border-radius: 999px; background: rgba(15,23,42,.72); box-shadow: 0 10px 30px rgba(0,0,0,.2); backdrop-filter: blur(16px); direction: ltr; }
        .language a { padding: .4rem .62rem; border-radius: 999px; color: #94a3b8; font-size: .7rem; font-weight: 800; transition: .2s ease; }
        .language a.active { color: #071018; background: var(--accent); }
        .error-card { width: min(100%, 68rem); display: grid; grid-template-columns: minmax(16rem,.82fr) minmax(20rem,1.18fr); overflow: hidden; border: 1px solid rgba(148,163,184,.17); border-radius: 2rem; background: rgba(12,17,31,.72); box-shadow: 0 38px 100px -35px rgba(0,0,0,.9), inset 0 1px rgba(255,255,255,.05); backdrop-filter: blur(22px); animation: enter .7s cubic-bezier(.2,.8,.2,1) both; }
        .error-visual { position: relative; min-height: 33rem; display: grid; place-items: center; overflow: hidden; border-inline-end: 1px solid rgba(148,163,184,.14); background: linear-gradient(145deg,var(--accent-soft),rgba(15,23,42,.12)); }
        .error-number { position: relative; font-size: clamp(7rem,18vw,13rem); font-weight: 900; line-height: .8; letter-spacing: -.09em; color: transparent; -webkit-text-stroke: 2px color-mix(in srgb,var(--accent) 72%,white); text-shadow: 0 0 55px var(--accent-soft); user-select: none; }
        .error-number::after { content: ""; position: absolute; width: .42em; height: .42em; top: -.18em; inset-inline-end: -.12em; border: 2px solid var(--accent); border-inline-start-color: transparent; border-radius: 50%; animation: spin 7s linear infinite; }
        .error-pulse { position: absolute; width: 17rem; height: 17rem; border: 1px solid color-mix(in srgb,var(--accent) 40%,transparent); border-radius: 50%; animation: pulse 3s ease-out infinite; }
        .error-content { padding: clamp(2rem,5vw,4.5rem); align-self: center; }
        .eyebrow { display: inline-flex; align-items: center; gap: .55rem; color: var(--accent); font-size: .72rem; font-weight: 800; letter-spacing: .17em; text-transform: uppercase; }
        .eyebrow::before { content: ""; width: 1.8rem; height: 2px; background: currentColor; box-shadow: 0 0 12px currentColor; }
        h1 { margin: 1rem 0 0; max-width: 13ch; font-size: clamp(2.25rem,5vw,4.35rem); line-height: 1.02; letter-spacing: -.055em; }
        .message { margin: 1.25rem 0 0; max-width: 40rem; color: #cbd5e1; font-size: 1.08rem; line-height: 1.8; }
        .description { margin: .65rem 0 0; max-width: 39rem; color: #64748b; font-size: .93rem; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: .55rem; min-height: 3rem; padding: .72rem 1.05rem; border: 1px solid rgba(148,163,184,.2); border-radius: .85rem; font-size: .88rem; font-weight: 750; transition: transform .2s ease,border-color .2s ease,background .2s ease; }
        .button:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.38); }
        .button.primary { border-color: transparent; color: #071018; background: var(--accent); box-shadow: 0 12px 30px -15px var(--accent); }
        .button.secondary { color: #e2e8f0; background: rgba(255,255,255,.045); }
        .button svg { width: 1.1rem; height: 1.1rem; }
        .reference { display: flex; align-items: center; gap: .6rem; margin-top: 2.25rem; color: #475569; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .reference span { color: #94a3b8; }
        @keyframes enter { from { opacity: 0; transform: translateY(18px) scale(.985); } to { opacity: 1; transform: none; } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0% { transform: scale(.7); opacity: .6; } 100% { transform: scale(1.35); opacity: 0; } }
        @keyframes drift { to { transform: translate3d(1.5rem,1rem,0) scale(1.06); } }
        @media(max-width:760px){.error-shell{padding:1rem}.error-card{grid-template-columns:1fr;border-radius:1.4rem}.error-visual{min-height:15rem;border-inline-end:0;border-bottom:1px solid rgba(148,163,184,.14)}.error-number{font-size:7rem}.error-pulse{width:10rem;height:10rem}.error-content{padding:2rem}.language{inset-block-start:.7rem;inset-inline-end:.7rem}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important}}
    </style>
</head>
<body>
    @php
        $dashboardUrl = match (true) {
            auth('admin')->check() => url('/admin'),
            auth('instructor')->check() => url('/instructors'),
            auth('student')->check() => url('/students'),
            default => url('/'),
        };
    @endphp

    <main class="error-shell">
        <div class="error-grid"></div>
        <div class="error-orb one"></div>
        <div class="error-orb two"></div>

        <nav class="language" aria-label="Language">
            <a href="{{ url('/locale/en') }}" @class(['active' => app()->isLocale('en')])>EN</a>
            <a href="{{ url('/locale/ar') }}" @class(['active' => app()->isLocale('ar')])>AR</a>
        </nav>

        <section class="error-card">
            <div class="error-visual" aria-hidden="true">
                <div class="error-pulse"></div>
                <div class="error-number">{{ $status }}</div>
            </div>

            <div class="error-content">
                <span class="eyebrow">{{ __('lms.errors.eyebrow') }}</span>
                <h1>{{ $title }}</h1>
                <p class="message">{{ $message }}</p>
                <p class="description">{{ __('lms.errors.description') }}</p>

                <div class="actions">
                    <a class="button primary" href="{{ $dashboardUrl }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                        {{ $dashboardUrl === url('/') ? __('lms.errors.home') : __('lms.errors.dashboard') }}
                    </a>
                    <button class="button secondary" type="button" onclick="history.back()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        {{ __('lms.errors.back') }}
                    </button>
                </div>

                <div class="reference">{{ __('lms.errors.reference') }} <span>#{{ $status }}</span></div>
            </div>
        </section>
    </main>
</body>
</html>
