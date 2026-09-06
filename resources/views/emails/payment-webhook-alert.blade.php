<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('payment-alert.title', locale: $locale) }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .shell { padding: 16px 8px !important; }
            .card { border-radius: 18px !important; }
            .header, .body, .footer { padding-left: 22px !important; padding-right: 22px !important; }
            .context-label, .context-value { display: block !important; width: auto !important; text-align: {{ $locale === 'ar' ? 'right' : 'left' }} !important; }
            .context-label { padding-bottom: 4px !important; }
            .context-value { padding-top: 0 !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;color:#27272a;font-family:Arial,Tahoma,sans-serif;-webkit-font-smoothing:antialiased;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $alertSubject }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;direction:{{ $locale === 'ar' ? 'rtl' : 'ltr' }};">
        <tr>
            <td class="shell" align="center" style="padding:42px 16px;">
                <table class="card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:660px;background:#fff;border-radius:26px;overflow:hidden;box-shadow:0 16px 40px rgba(24,24,27,.10);text-align:{{ $locale === 'ar' ? 'right' : 'left' }};">
                    <tr><td style="height:5px;background:#f59e0b;"></td></tr>
                    <tr>
                        <td class="header" style="padding:34px 42px;background:#18181b;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle">
                                        <div style="display:inline-block;padding:7px 11px;border:1px solid rgba(251,191,36,.35);border-radius:999px;color:#fbbf24;font-size:11px;font-weight:700;letter-spacing:1px;">{{ __('payment-alert.eyebrow', locale: $locale) }}</div>
                                        <h1 style="margin:17px 0 7px;color:#fff;font-size:28px;line-height:1.3;font-weight:800;">{{ __('payment-alert.title', locale: $locale) }}</h1>
                                        <p style="margin:0;color:#a1a1aa;font-size:14px;line-height:1.7;">{{ __('payment-alert.intro', locale: $locale) }}</p>
                                    </td>
                                    <td width="62" valign="top" align="{{ $locale === 'ar' ? 'left' : 'right' }}">
                                        <div style="width:54px;height:54px;border-radius:17px;background:#f59e0b;color:#18181b;font-size:25px;font-weight:900;line-height:54px;text-align:center;">!</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="body" style="padding:36px 42px 34px;">
                            <div style="margin-bottom:11px;color:#b45309;font-size:11px;font-weight:800;letter-spacing:1px;">{{ __('payment-alert.details', locale: $locale) }}</div>
                            <div style="padding:23px 24px;border:1px solid #fed7aa;border-radius:17px;background:#fff7ed;">
                                <h2 style="margin:0 0 10px;color:#9a3412;font-size:19px;line-height:1.45;">{{ $alertSubject }}</h2>
                                <p style="margin:0;color:#57534e;font-size:15px;line-height:1.8;white-space:pre-line;">{{ $alertMessage }}</p>
                            </div>

                            @if ($contextItems !== [])
                                <div style="margin:28px 0 11px;color:#71717a;font-size:11px;font-weight:800;letter-spacing:1px;">{{ __('payment-alert.context', locale: $locale) }}</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e4e4e7;border-radius:16px;border-collapse:separate;overflow:hidden;">
                                    @foreach ($contextItems as $item)
                                        <tr>
                                            <td class="context-label" width="38%" style="padding:13px 17px;border-bottom:{{ $loop->last ? '0' : '1px solid #e4e4e7' }};background:#fafafa;color:#71717a;font-size:12px;font-weight:700;">{{ $item['label'] }}</td>
                                            <td class="context-value" style="padding:13px 17px;border-bottom:{{ $loop->last ? '0' : '1px solid #e4e4e7' }};color:#3f3f46;font-family:Consolas,Monaco,monospace;font-size:12px;text-align:{{ $locale === 'ar' ? 'left' : 'right' }};word-break:break-word;direction:ltr;">{{ $item['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <div style="margin-top:26px;padding:15px 17px;border-radius:13px;background:#f4f4f5;color:#71717a;font-size:12px;line-height:1.7;">{{ __('payment-alert.notice', locale: $locale) }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer" style="padding:22px 42px;border-top:1px solid #e4e4e7;background:#fafafa;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="color:#71717a;font-size:11px;line-height:1.7;"><strong style="color:#3f3f46;">{{ config('app.name') }}</strong><br>{{ __('payment-alert.footer', locale: $locale) }}</td>
                                    <td align="{{ $locale === 'ar' ? 'left' : 'right' }}" style="color:#a1a1aa;font-size:10px;line-height:1.7;">{{ __('payment-alert.environment', locale: $locale) }}: {{ app()->environment() }}<br>{{ __('payment-alert.sent_at', locale: $locale) }}: {{ now()->format('Y-m-d H:i T') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
