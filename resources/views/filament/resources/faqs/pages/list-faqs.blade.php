<x-filament-panels::page>
    @php($faqs = $this->getFaqs())

    <style>
        [x-cloak] { display: none !important; }
        .faq-design { display: grid; gap: 22px; --accent: #d97706; --ink: #172033; --muted: #667085; }
        .faq-design > section:first-child { position: relative; isolation: isolate; overflow: hidden; min-height: 220px; box-sizing: border-box; padding: 36px; border-radius: 24px; color: white; background: linear-gradient(125deg, #111827 0%, #1f2937 58%, #78350f 100%); box-shadow: 0 22px 55px rgba(17,24,39,.17); }
        .faq-design > section:first-child::before, .faq-design > section:first-child::after { content: ''; position: absolute; z-index: -1; border-radius: 999px; background: rgba(245,158,11,.16); }
        .faq-design > section:first-child::before { width: 310px; height: 310px; right: -75px; top: -170px; }
        .faq-design > section:first-child::after { width: 180px; height: 180px; right: 220px; bottom: -135px; }
        .faq-design > section:first-child > div:first-child { position: relative; z-index: 1; max-width: 720px; }
        .faq-design > section:first-child p:first-child { display: inline-flex; align-items: center; gap: 9px; margin: 0 0 15px; color: #fbbf24; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        .faq-design > section:first-child p:first-child::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 0 6px rgba(245,158,11,.14); }
        .faq-design > section:first-child h2 { max-width: 650px; margin: 0; font-size: clamp(29px,4vw,44px); line-height: 1.08; font-weight: 850; letter-spacing: -.04em; }
        .faq-design > section:first-child p:last-child { max-width: 680px; margin: 17px 0 0; color: #d1d5db; font-size: 15px; line-height: 1.75; }
        .faq-design > div:nth-child(2) { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
        .faq-design > div:nth-child(2) h3 { margin: 0; color: var(--ink); font-size: 20px; font-weight: 800; letter-spacing: -.02em; }
        .faq-design > div:nth-child(2) p { margin: 5px 0 0; color: var(--muted); font-size: 13px; }
        .faq-design > div:nth-child(2) > span { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #bbf7d0; border-radius: 999px; color: #15803d; background: #f0fdf4; font-size: 12px; font-weight: 750; white-space: nowrap; }
        .faq-design > div:nth-child(2) > span > span { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.12); }
        .faq-design > section:last-child { display: grid; gap: 12px; }
        .faq-design article { overflow: hidden; border: 1px solid #eaecf0; border-radius: 18px; background: #fff; box-shadow: 0 4px 16px rgba(16,24,40,.04); transition: transform .2s, border-color .2s, box-shadow .2s; }
        .faq-design article:hover { transform: translateY(-2px); border-color: #f2c675; box-shadow: 0 14px 30px rgba(16,24,40,.09); }
        .faq-design article > div:first-child { display: flex; align-items: center; gap: 13px; padding: 18px; }
        .faq-design article button { display: flex; min-width: 0; flex: 1; align-items: center; gap: 16px; padding: 0; border: 0; color: inherit; background: transparent; cursor: pointer; text-align: left; }
        .faq-design article button > span:first-child { display: grid; width: 46px; height: 46px; flex: 0 0 46px; place-items: center; border-radius: 14px; color: #b45309; background: #fffbeb; font-size: 12px; font-weight: 850; }
        .faq-design article button > span:nth-child(2) { min-width: 0; flex: 1; color: var(--ink); font-size: 15px; font-weight: 750; line-height: 1.55; }
        .faq-design article button svg { width: 20px !important; height: 20px !important; flex: 0 0 20px; color: #98a2b3; transition: transform .22s, color .22s; }
        .faq-design article button[aria-expanded='true'] svg { color: var(--accent); transform: rotate(180deg); }
        .faq-design article a { flex: 0 0 auto; padding: 9px 13px; border-radius: 10px; color: #b45309; background: #fffbeb; font-size: 13px; font-weight: 750; text-decoration: none; transition: color .2s, background .2s; }
        .faq-design article a:hover { color: #fff; background: var(--accent); }
        .faq-design article > div:last-child > div { margin: 0 18px 18px 80px; padding: 18px 20px; border-top: 0 !important; border-left: 3px solid #f59e0b; border-radius: 4px 13px 13px 4px; color: #475467; background: #fffcf5; font-size: 14px; line-height: 1.8; }
        .faq-design > section:last-child > div { padding: 55px 24px; border: 1px dashed #d0d5dd; border-radius: 20px; background: #fff; text-align: center; }
        .faq-design > section:last-child > div > div { display: grid; width: 62px; height: 62px; margin: 0 auto; place-items: center; border-radius: 18px; color: #b45309; background: #fffbeb; font-size: 28px; font-weight: 800; }
        .faq-design > section:last-child > div h3 { margin: 16px 0 0; color: var(--ink); font-size: 17px; }
        .faq-design > section:last-child > div p { margin: 7px 0 0; color: var(--muted); font-size: 14px; }
        .dark .faq-design { --ink: #f9fafb; --muted: #98a2b3; }
        .dark .faq-design article, .dark .faq-design > section:last-child > div { border-color: rgba(255,255,255,.1); background: #111827; }
        .dark .faq-design article:hover { border-color: rgba(245,158,11,.48); }
        .dark .faq-design article button > span:first-child, .dark .faq-design article a { color: #fbbf24; background: rgba(245,158,11,.12); }
        .dark .faq-design article > div:last-child > div { color: #d1d5db; background: rgba(245,158,11,.07); }
        .dark .faq-design > div:nth-child(2) > span { border-color: rgba(34,197,94,.25); color: #86efac; background: rgba(34,197,94,.08); }
        @media (max-width: 700px) { .faq-design > section:first-child { min-height: auto; padding: 27px 22px; } .faq-design > div:nth-child(2) { align-items: flex-end; } .faq-design article > div:first-child { align-items: flex-start; } .faq-design article > div:last-child > div { margin-left: 18px; } }
    </style>

    <div class="faq-design space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-500 to-cyan-500 p-6 text-white shadow-lg sm:p-8">
            <div class="relative z-10 max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-white/75">{{ __('lms.faqs.hero.kicker') }}</p>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">{{ __('lms.faqs.hero.title') }}</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/80 sm:text-base">{{ __('lms.faqs.hero.description') }}</p>
            </div>
            <div class="absolute -right-12 -top-16 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-20 right-24 h-44 w-44 rounded-full bg-white/10"></div>
        </section>

        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('lms.faqs.list.title') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $faqs->count() }} {{ $faqs->count() === 1 ? __('lms.faqs.list.one') : __('lms.faqs.list.many') }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-success-50 px-3 py-1.5 text-xs font-semibold text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400">
                <span class="h-2 w-2 rounded-full bg-success-500"></span> {{ __('lms.faqs.list.cached') }}
            </span>
        </div>

        <section class="space-y-3">
            @forelse ($faqs as $faq)
                <article x-data="{ open: false }" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start gap-3 p-5 sm:p-6">
                        <button type="button" x-on:click="open = ! open" class="flex min-w-0 flex-1 items-start gap-4 text-left" x-bind:aria-expanded="open">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm font-bold text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0 flex-1 pt-2 font-semibold leading-6 text-gray-950 dark:text-white">{{ $faq['question'] }}</span>
                            <svg class="mt-2 h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200" x-bind:class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                        <a href="{{ \App\Filament\Resources\Faqs\FaqResource::getUrl('edit', ['record' => $faq['id']]) }}" class="mt-1 shrink-0 rounded-lg px-3 py-2 text-sm font-medium text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-400/10">{{ __('lms.faqs.list.edit') }}</a>
                    </div>
                    <div x-cloak x-show="open" x-collapse>
                        <div class="border-t border-gray-100 px-5 py-5 text-sm leading-7 text-gray-600 dark:border-white/10 dark:text-gray-300 sm:px-20">{!! nl2br(e($faq['answer'])) !!}</div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-white/15 dark:bg-gray-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary-50 text-2xl text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">?</div>
                    <h3 class="mt-4 font-semibold text-gray-950 dark:text-white">{{ __('lms.faqs.empty.title') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('lms.faqs.empty.description') }}</p>
                </div>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
