<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('contacts.reply_email.subject', ['subject' => $contact->subject], $locale) }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 18px 10px !important; }
            .email-card { border-radius: 18px !important; }
            .email-header, .email-body, .email-footer { padding-left: 22px !important; padding-right: 22px !important; }
            .meta-label { display: block !important; width: 100% !important; padding-bottom: 4px !important; }
            .meta-value { display: block !important; width: 100% !important; }
        }

        .reply-content p { margin: 0 0 14px; }
        .reply-content p:last-child { margin-bottom: 0; }
        .reply-content ul, .reply-content ol { margin: 12px 0; padding-left: 24px; }
        .reply-content a { color: #d97706; font-weight: 700; }
        .reply-content blockquote { margin: 16px 0; padding: 10px 16px; border-left: 3px solid #f59e0b; background: #fffbeb; }
    </style>
</head>
<body style="margin:0; padding:0; background:#f4f4f5; color:#27272a; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ __('contacts.reply_email.subject', ['subject' => $contact->subject], $locale) }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;">
        <tr>
            <td class="email-shell" align="center" style="padding:42px 16px;">
                <table class="email-card" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:640px; background:#ffffff; border-radius:26px; overflow:hidden; box-shadow:0 16px 40px rgba(24,24,27,.10);">
                    <tr>
                        <td class="email-header" style="padding:38px 42px; background:#18181b;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <div style="display:inline-block; padding:7px 11px; border:1px solid rgba(251,191,36,.35); border-radius:999px; color:#fbbf24; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;">
                                            {{ __('contacts.reply_email.eyebrow', locale: $locale) }}
                                        </div>
                                        <h1 style="margin:18px 0 7px; color:#ffffff; font-size:29px; line-height:1.25; font-weight:800;">{{ __('contacts.reply_email.title', locale: $locale) }}</h1>
                                        <p style="margin:0; color:#a1a1aa; font-size:15px; line-height:1.7;">{{ __('contacts.reply_email.intro', locale: $locale) }}</p>
                                    </td>
                                    <td width="58" valign="top" align="right">
                                        <div style="width:52px; height:52px; border-radius:16px; background:#f59e0b; color:#18181b; font-size:20px; font-weight:900; line-height:52px; text-align:center;">
                                            {{ Str::upper(Str::substr(config('app.name', 'N'), 0, 1)) }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-body" style="padding:38px 42px 34px;">
                            <p style="margin:0 0 8px; color:#71717a; font-size:14px;">{{ __('contacts.reply_email.hello', ['name' => $contact->name], $locale) }}</p>
                            <p style="margin:0 0 28px; color:#3f3f46; font-size:16px; line-height:1.75;">{{ __('contacts.reply_email.thanks', locale: $locale) }}</p>

                            <div style="padding:26px; border:1px solid #fde68a; border-radius:18px; background:#fffbeb;">
                                <div style="margin-bottom:14px; color:#b45309; font-size:11px; font-weight:800; letter-spacing:1.4px; text-transform:uppercase;">{{ __('contacts.reply_email.our_reply', locale: $locale) }}</div>
                                <div class="reply-content" style="color:#27272a; font-size:16px; line-height:1.8;">
                                    {!! $replyHtml !!}
                                </div>
                            </div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px; border:1px solid #e4e4e7; border-radius:16px; border-collapse:separate; overflow:hidden;">
                                <tr>
                                    <td colspan="2" style="padding:15px 18px; border-bottom:1px solid #e4e4e7; background:#fafafa; color:#52525b; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase;">{{ __('contacts.reply_email.original', locale: $locale) }}</td>
                                </tr>
                                <tr>
                                    <td class="meta-label" width="100" style="padding:15px 10px 7px 18px; color:#a1a1aa; font-size:12px; font-weight:700;">{{ __('contacts.fields.subject', locale: $locale) }}</td>
                                    <td class="meta-value" style="padding:15px 18px 7px 0; color:#3f3f46; font-size:14px; font-weight:700;">{{ $contact->subject }}</td>
                                </tr>
                                <tr>
                                    <td class="meta-label" width="100" valign="top" style="padding:7px 10px 17px 18px; color:#a1a1aa; font-size:12px; font-weight:700;">{{ __('contacts.fields.message', locale: $locale) }}</td>
                                    <td class="meta-value" style="padding:7px 18px 17px 0; color:#71717a; font-size:14px; line-height:1.65;">{{ $contact->message }}</td>
                                </tr>
                            </table>

                            <p style="margin:26px 0 0; color:#71717a; font-size:14px; line-height:1.7;">{{ __('contacts.reply_email.help', locale: $locale) }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-footer" style="padding:24px 42px; border-top:1px solid #e4e4e7; background:#fafafa;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="color:#71717a; font-size:12px; line-height:1.6;">
                                        <strong style="color:#3f3f46;">{{ config('app.name') }}</strong><br>
                                        {{ __('contacts.reply_email.footer', locale: $locale) }}
                                    </td>
                                    <td align="right" valign="top" style="color:#a1a1aa; font-size:11px;">
                                        {{ __('contacts.reply_email.reference', locale: $locale) }} #{{ str_pad((string) $contact->id, 6, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="margin:20px 0 0; color:#a1a1aa; font-size:11px;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
