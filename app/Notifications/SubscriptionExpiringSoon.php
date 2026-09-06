<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining,
        public string $notificationLocale = 'ar',
    ) {
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = str_starts_with($this->notificationLocale, 'ar') ? 'ar' : 'en';
        $this->subscription->loadMissing('plan');

        return (new MailMessage)
            ->subject(__('subscription-expiry.subject', [
                'plan' => $this->subscription->plan?->name,
            ], $locale))
            ->view('emails.subscription-expiring-soon', [
                'locale' => $locale,
                'student' => $notifiable,
                'subscription' => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
