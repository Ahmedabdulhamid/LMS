<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('contacts.admin_email.subject', ['name' => $contact->name], $locale) }}</title>
    <style>@media(max-width:620px){.shell{padding:14px 8px!important}.card{border-radius:18px!important}.header,.body,.footer{padding-left:22px!important;padding-right:22px!important}.label,.value{display:block!important;width:auto!important}}</style>
</head>
<body style="margin:0;background:#f4f4f5;color:#27272a;font-family:Arial,Tahoma,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;direction:{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<tr><td class="shell" align="center" style="padding:42px 16px">
<table class="card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:650px;background:#fff;border-radius:26px;overflow:hidden;box-shadow:0 16px 40px rgba(24,24,27,.1);text-align:{{ $locale === 'ar' ? 'right' : 'left' }}">
<tr><td style="height:5px;background:#f59e0b"></td></tr>
<tr><td class="header" style="padding:35px 42px;background:#18181b">
<div style="display:inline-block;padding:7px 11px;border:1px solid #f59e0b66;border-radius:999px;color:#fbbf24;font-size:11px;font-weight:800">{{ __('contacts.admin_email.eyebrow', locale: $locale) }}</div>
<h1 style="margin:18px 0 7px;color:#fff;font-size:29px">{{ __('contacts.admin_email.title', locale: $locale) }}</h1>
<p style="margin:0;color:#a1a1aa;font-size:14px;line-height:1.7">{{ __('contacts.admin_email.intro', locale: $locale) }}</p>
</td></tr>
<tr><td class="body" style="padding:36px 42px">
<div style="color:#b45309;font-size:11px;font-weight:800;margin-bottom:10px">{{ __('contacts.admin_email.contact_details', locale: $locale) }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e4e4e7;border-radius:15px;border-collapse:separate;overflow:hidden">
@foreach (['name', 'email', 'phone', 'subject'] as $field)
<tr><td class="label" width="32%" style="padding:12px 16px;background:#fafafa;border-bottom:{{ $loop->last ? '0' : '1px solid #e4e4e7' }};color:#71717a;font-size:12px;font-weight:700">{{ __('contacts.fields.'.$field, locale: $locale) }}</td><td class="value" style="padding:12px 16px;border-bottom:{{ $loop->last ? '0' : '1px solid #e4e4e7' }};color:#27272a;font-size:14px">{{ $contact->{$field} ?: '—' }}</td></tr>
@endforeach
</table>
<div style="margin:27px 0 10px;color:#b45309;font-size:11px;font-weight:800">{{ __('contacts.admin_email.message', locale: $locale) }}</div>
<div style="padding:22px;border:1px solid #fde68a;border-radius:16px;background:#fffbeb;color:#3f3f46;font-size:15px;line-height:1.8;white-space:pre-wrap">{{ $contact->message }}</div>
</td></tr>
<tr><td class="footer" style="padding:21px 42px;border-top:1px solid #e4e4e7;background:#fafafa;color:#71717a;font-size:11px;line-height:1.7"><strong style="color:#3f3f46">{{ config('app.name') }}</strong> · {{ __('contacts.admin_email.footer', locale: $locale) }}<br>{{ __('contacts.admin_email.received', locale: $locale) }}: {{ $contact->created_at?->format('Y-m-d H:i') }} · #{{ str_pad((string) $contact->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
</table>
</td></tr></table>
</body>
</html>
