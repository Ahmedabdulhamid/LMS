<?php

namespace App\Listeners;

use App\Events\ContactCreated;
use App\Models\Admin;
use App\Notifications\ContactBroadcastNotification;

class SendContactNotification
{
    public function handle(ContactCreated $event): void
    {
        Admin::query()->each(function (Admin $admin) use ($event): void {
            $admin->notifyNow(
                new ContactBroadcastNotification($event->contact)
            );
        });
    }
}
