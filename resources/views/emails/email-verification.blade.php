<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ __('email-verification.email.subject', locale: $locale) }}</title><style>@media(max-width:620px){.shell{padding:14px 8px!important}.card{border-radius:18px!important}.header,.body,.footer{padding-left:22px!important;padding-right:22px!important}.button{display:block!important}}</style></head>
<body style="margin:0;background:#f4f4f5;color:#27272a;font-family:Arial,Tahoma,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;direction:{{ $locale === 'ar' ? 'rtl' : 'ltr' }}"><tr><td class="shell" align="center" style="padding:42px 16px">
<table class="card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:650px;background:#fff;border-radius:26px;overflow:hidden;box-shadow:0 16px 40px rgba(24,24,27,.1);text-align:{{ $locale === 'ar' ? 'right' : 'left' }}">
<tr><td style="height:5px;background:#f59e0b"></td></tr>
<tr><td class="header" style="padding:38px 42px;background:#18181b">
    <div style="width:58px;height:58px;border-radius:18px;background:#f59e0b;color:#18181b;font-size:28px;font-weight:900;line-height:58px;text-align:center">✉</div>
    <p style="margin:20px 0 7px;color:#fbbf24;font-size:11px;font-weight:800;letter-spacing:1.4px">{{ __('email-verification.email.eyebrow', locale: $locale) }}</p>
    <h1 style="margin:0;color:#fff;font-size:29px;line-height:1.3">{{ __('email-verification.email.heading', locale: $locale) }}</h1>
</td></tr>
<tr><td class="body" style="padding:36px 42px">
    <p style="margin:0 0 18px;color:#3f3f46;font-size:15px;font-weight:700">{{ __('email-verification.email.greeting', ['name' => $user->name], $locale) }}</p>
    <p style="margin:0;color:#52525b;font-size:15px;line-height:1.8">{{ __('email-verification.email.message', locale: $locale) }}</p>
    <div style="margin:30px 0;text-align:center"><a class="button" href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 27px;border-radius:11px;background:#f59e0b;color:#18181b;text-decoration:none;font-size:14px;font-weight:800">{{ __('email-verification.email.button', locale: $locale) }}</a></div>
    <div style="padding:14px 16px;border-radius:12px;background:#fffbeb;color:#92400e;font-size:12px;line-height:1.7">🔒 {{ __('email-verification.email.expiry', ['minutes' => config('auth.verification.expire', 60)], $locale) }}</div>
    <p style="margin:25px 0 8px;color:#71717a;font-size:12px;line-height:1.7">{{ __('email-verification.email.copy_link', locale: $locale) }}</p>
    <p style="margin:0;padding:12px;border:1px solid #e4e4e7;border-radius:10px;color:#52525b;font-family:monospace;font-size:11px;line-height:1.6;overflow-wrap:anywhere;direction:ltr;text-align:left">{{ $verificationUrl }}</p>
    <p style="margin:24px 0 0;color:#71717a;font-size:12px;line-height:1.7">{{ __('email-verification.email.ignore', locale: $locale) }}</p>
</td></tr>
<tr><td class="footer" style="padding:21px 42px;border-top:1px solid #e4e4e7;background:#fafafa;color:#71717a;font-size:11px"><strong style="color:#3f3f46">{{ app(\App\Services\SettingService::class)->name() }}</strong> · {{ __('email-verification.email.footer', locale: $locale) }}</td></tr>
</table></td></tr></table>
</body></html>
