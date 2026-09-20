<?php

namespace App\Notifications;

use Filament\Auth\Notifications\VerifyEmail as FilamentVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends FilamentVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $locale = in_array(app()->getLocale(), ['ar', 'en'], true) ? app()->getLocale() : 'en';

        return (new MailMessage)
            ->subject(__('email-verification.email.subject', locale: $locale))
            ->view('emails.email-verification', [
                'user' => $notifiable,
                'verificationUrl' => $this->verificationUrl($notifiable),
                'locale' => $locale,
            ]);
    }
}
