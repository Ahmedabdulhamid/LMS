<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use App\Models\Admin;
use App\Notifications\SubscriptionCreatedNotification;

class SendSubscriptionNotification
{
    public function handle(SubscriptionCreated $event): void
    {
        Admin::query()->each(fn (Admin $admin) => $admin->notifyNow(
            new SubscriptionCreatedNotification($event->subscription),
        ));
    }
}
