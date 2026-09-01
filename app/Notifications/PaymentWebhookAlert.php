<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentWebhookAlert extends Notification
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
        $mail = (new MailMessage)->subject($this->subject)->line($this->message);

        foreach ($this->context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $mail->line(str_replace('_', ' ', ucfirst((string) $key)).': '.(string) $value);
            }
        }

        return $mail;
    }
}
