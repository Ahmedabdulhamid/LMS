<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Admin;
use App\Notifications\OrderCreatedNotification;

class SendOrderNotification
{
    public function handle(OrderCreated $event): void
    {
        Admin::query()->each(fn (Admin $admin) => $admin->notify(
            new OrderCreatedNotification($event->order),
        ));
    }
}
