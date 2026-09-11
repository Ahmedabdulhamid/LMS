<?php

namespace App\Listeners;

use App\Events\AdminDatabaseNotificationsSent;
use App\Models\Admin;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\DB;
use Throwable;

class BroadcastAdminDatabaseNotification
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || ! $event->notifiable instanceof Admin) {
            return;
        }

        DB::afterCommit(function () use ($event): void {
            try {
                AdminDatabaseNotificationsSent::dispatch($event->notifiable);
            } catch (Throwable $exception) {
                // Preserve the saved notification if the realtime service is unavailable.
                report($exception);
            }
        });
    }
}
