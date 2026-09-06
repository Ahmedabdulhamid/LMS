<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PaymentWebhookAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $subject,
        private readonly string $message,
        private readonly array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = str_starts_with(app()->getLocale(), 'ar') ? 'ar' : 'en';

        return (new MailMessage)
            ->subject($this->subject)
            ->view('emails.payment-webhook-alert', [
                'alertSubject' => $this->subject,
                'alertMessage' => $this->message,
                'contextItems' => collect($this->context)
                    ->filter(fn (mixed $value): bool => is_scalar($value) || $value === null)
                    ->map(fn (mixed $value, string | int $key): array => [
                        'label' => Str::headline((string) $key),
                        'value' => match (true) {
                            is_bool($value) => $value ? __('payment-alert.yes', locale: $locale) : __('payment-alert.no', locale: $locale),
                            $value === null, $value === '' => '—',
                            default => (string) $value,
                        },
                    ])
                    ->values()
                    ->all(),
                'locale' => $locale,
            ]);
    }
}
