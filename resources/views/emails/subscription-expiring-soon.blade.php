<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ __('subscription-expiry.title', locale: $locale) }}</title>
</head>
<body style="margin:0;background:#f4f4f5;color:#27272a;font-family:Arial,Tahoma,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="direction:{{ $locale === 'ar' ? 'rtl' : 'ltr' }};background:#f4f4f5">
    <tr><td align="center" style="padding:40px 16px">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:650px;overflow:hidden;background:#fff;border-radius:24px;text-align:{{ $locale === 'ar' ? 'right' : 'left' }};box-shadow:0 16px 40px rgba(24,24,27,.1)">
            <tr><td style="height:5px;background:#f59e0b"></td></tr>
            <tr><td style="padding:36px 40px;background:#18181b">
                <div style="width:56px;height:56px;border-radius:17px;background:#f59e0b;color:#fff;font-size:28px;font-weight:900;line-height:56px;text-align:center">!</div>
                <h1 style="margin:20px 0 8px;color:#fff;font-size:28px">{{ __('subscription-expiry.title', locale: $locale) }}</h1>
            </td></tr>
            <tr><td style="padding:34px 40px">
                <p style="margin:0 0 18px">{{ __('subscription-expiry.hello', ['name' => $student->name], $locale) }}</p>
                <p style="margin:0 0 24px;line-height:1.8;color:#52525b">{{ __('subscription-expiry.message', ['plan' => $subscription->plan?->name], $locale) }}</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e4e4e7;border-radius:14px">
                    <tr><td style="padding:13px 16px;background:#fafafa">{{ __('subscription-expiry.plan', locale: $locale) }}</td><td style="padding:13px 16px;font-weight:700">{{ $subscription->plan?->name }}</td></tr>
                    <tr><td style="padding:13px 16px;background:#fafafa">{{ __('subscription-expiry.ends_at', locale: $locale) }}</td><td style="padding:13px 16px;direction:ltr">{{ $subscription->ends_at->format('Y-m-d') }}</td></tr>
                    <tr><td style="padding:13px 16px;background:#fafafa">{{ __('subscription-expiry.remaining', locale: $locale) }}</td><td style="padding:13px 16px;color:#d97706;font-weight:700">{{ trans_choice('subscription-expiry.days', $daysRemaining, ['count' => $daysRemaining], $locale) }}</td></tr>
                </table>
                <div style="margin-top:26px;text-align:center"><a href="{{ route('subscription-plans.index') }}" style="display:inline-block;padding:13px 24px;border-radius:11px;background:#f59e0b;color:#fff;text-decoration:none;font-weight:700">{{ __('subscription-expiry.cta', locale: $locale) }}</a></div>
                <p style="margin:24px 0 0;color:#71717a;font-size:12px">{{ __('subscription-expiry.help', locale: $locale) }}</p>
            </td></tr>
            <tr><td style="padding:20px 40px;border-top:1px solid #e4e4e7;background:#fafafa;color:#71717a;font-size:11px"><strong>{{ config('app.name') }}</strong> · {{ __('subscription-expiry.footer', locale: $locale) }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
