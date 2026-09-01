<?php

namespace App\Services;

use App\Notifications\PaymentWebhookAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class PaymentAlertService
{
    public function send(string $subject, string $message, array $context = []): void
    {
        Log::critical($subject, $context + ['message' => $message]);

        $email = config('paymob.alert_email');
        if (! is_string($email) || $email === '') {
            return;
        }

        try {
            Notification::route('mail', $email)->notify(new PaymentWebhookAlert($subject, $message, $context));
        } catch (Throwable $exception) {
            Log::error('Payment alert delivery failed', ['error' => $exception->getMessage()]);
        }
    }
}
