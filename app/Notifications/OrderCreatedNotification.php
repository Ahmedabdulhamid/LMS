<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->order->loadMissing('user');

        return [
            'format' => 'filament',
            'title' => 'New Order '.$this->order->number,
            'body' => 'A new order was created by '.($this->order->user?->name ?? 'a student')
                .' for '.$this->order->total.' '.$this->order->currency.'.',
            'duration' => 'persistent',
        ];
    }
}
