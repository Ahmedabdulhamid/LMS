<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->subscription->loadMissing(['user', 'plan']);

        return [
            'format' => 'filament',
            'title' => 'New Subscription',
            'body' => ($this->subscription->user?->name ?? 'A student')
                .' subscribed to '.($this->subscription->plan?->name ?? 'a subscription plan').'.',
            'duration' => 'persistent',
        ];
    }
}
