<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    @include('partials.application-icons')
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('payment-callback.page_title') }} · {{ app(\App\Services\SettingService::class)->name() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{color-scheme:light;--accent:#4f46e5;--ink:#172033;--muted:#657086}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:28px 16px;color:var(--ink);font-family:Inter,Arial,sans-serif;background:radial-gradient(circle at 15% 15%,#ddd6fe 0,transparent 28%),radial-gradient(circle at 85% 80%,#bfdbfe 0,transparent 30%),#f8fafc}.card{overflow:hidden;width:min(100%,680px);padding:38px;border:1px solid rgba(255,255,255,.75);border-radius:28px;background:rgba(255,255,255,.92);box-shadow:0 28px 80px rgba(30,41,59,.14);backdrop-filter:blur(12px)}.brand{display:flex;align-items:center;justify-content:center;gap:11px;margin-bottom:28px;color:#334155}.brand img{width:42px;height:42px;object-fit:contain;border-radius:12px}.visual{display:grid;place-items:center;width:86px;height:86px;margin:0 auto 20px;border-radius:50%}.visual svg{width:42px;height:42px;fill:none;stroke:currentColor;stroke-width:2.3;stroke-linecap:round;stroke-linejoin:round}.processing .visual{color:#b45309;background:#fff7ed;box-shadow:0 0 0 10px rgba(245,158,11,.08)}.success .visual{color:#15803d;background:#f0fdf4;box-shadow:0 0 0 10px rgba(34,197,94,.08)}.failed .visual{color:#b91c1c;background:#fef2f2;box-shadow:0 0 0 10px rgba(239,68,68,.08)}.processing .visual svg{animation:pulse 1.7s ease-in-out infinite}@keyframes pulse{50%{transform:scale(.86);opacity:.6}}.badge{display:table;margin:0 auto 12px;padding:7px 13px;border-radius:999px;font-size:12px;font-weight:800}.processing .badge{color:#92400e;background:#fef3c7}.success .badge{color:#166534;background:#dcfce7}.failed .badge{color:#991b1b;background:#fee2e2}h1{margin:0;text-align:center;font-size:clamp(1.65rem,5vw,2.3rem);letter-spacing:-.035em}.message{max-width:560px;margin:14px auto 20px;text-align:center;color:var(--muted);line-height:1.75}.note{padding:13px 16px;margin:0 0 25px;text-align:center;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:13px;font-size:14px}.details{display:grid;grid-template-columns:1fr 1fr;gap:1px;overflow:hidden;margin-bottom:25px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:16px}.detail{min-width:0;padding:15px 17px;background:#fff}.label{display:block;margin-bottom:5px;color:#94a3b8;font-size:12px}.value{display:block;overflow-wrap:anywhere;font-weight:750}.actions{display:flex;justify-content:center;flex-wrap:wrap;gap:10px}.btn{padding:12px 19px;border-radius:12px;text-decoration:none;font-weight:750;transition:transform .15s}.btn:hover{transform:translateY(-1px)}.primary{color:#fff;background:var(--accent);box-shadow:0 8px 20px rgba(79,70,229,.24)}.secondary{color:#334155;background:#eef2ff}.secure{text-align:center;margin:22px 0 0;color:#94a3b8;font-size:12px}@media(max-width:560px){.card{padding:28px 19px;border-radius:22px}.details{grid-template-columns:1fr}.detail{padding:13px 15px}.btn{width:100%;text-align:center}}
    </style>
</head>
<body>
<main id="payment-card" class="card {{ $paymentState }}" data-status-url="{{ route('payment.callback.status', $order) }}">
    <div class="brand"><img src="{{ app(\App\Services\SettingService::class)->logoUrl() }}" alt=""><strong>{{ app(\App\Services\SettingService::class)->name() }}</strong></div>
    <div class="visual" aria-hidden="true">
        @if($paymentState === 'success')<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
        @elseif($paymentState === 'failed')<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>
        @else<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>@endif
    </div>
    <span class="badge">{{ __("payment-callback.states.$paymentState.badge") }}</span>
    <h1>{{ __("payment-callback.states.$paymentState.title") }}</h1>
    <p class="message">{{ __("payment-callback.states.$paymentState.message") }}</p>
    <p class="note">{{ __("payment-callback.states.$paymentState.note") }}</p>
    <section class="details">
        <div class="detail"><span class="label">{{ __('payment-callback.order') }}</span><span class="value">#{{ $order->number }}</span></div>
        <div class="detail"><span class="label">{{ __('payment-callback.amount') }}</span><span class="value">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</span></div>
        <div class="detail"><span class="label">{{ __('payment-callback.transaction') }}</span><span class="value">{{ $transactionId ?: __('payment-callback.not_available') }}</span></div>
        <div class="detail"><span class="label">{{ __('payment-callback.status') }}</span><span class="value">{{ __("payment-callback.states.$paymentState.badge") }}</span></div>
    </section>
    <div class="actions">
        @if($paymentState === 'success')<a class="btn primary" href="{{ route('my-courses.index') }}">{{ __('payment-callback.my_courses') }}</a>
        @elseif($paymentState === 'failed')<a class="btn primary" href="{{ route('checkout.orders.show', $order) }}">{{ __('payment-callback.try_again') }}</a>
        @else<a class="btn primary" href="{{ route('checkout.orders.show', $order) }}">{{ __('payment-callback.view_order') }}</a>@endif
        <a class="btn secondary" href="{{ route('home') }}">{{ __('payment-callback.back_home') }}</a>
    </div>
    <p class="secure">🔒 {{ __('payment-callback.secure_payment') }}</p>
</main>
@include('partials.site-footer')
@if($paymentState === 'processing')
<script>
(()=>{const card=document.getElementById('payment-card');let checks=0;const poll=async()=>{if(++checks>40)return;try{const response=await fetch(card.dataset.statusUrl,{headers:{Accept:'application/json'},credentials:'same-origin'});if(response.ok&&(await response.json()).state!=='processing'){location.reload();return}}catch(error){}setTimeout(poll,3000)};setTimeout(poll,2500)})();
</script>
@endif
</body>
</html>
