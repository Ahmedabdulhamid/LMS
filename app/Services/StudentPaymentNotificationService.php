<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\StudentPaymentResult;

class StudentPaymentNotificationService
{
    public function succeeded(int $orderId): void
    {
        $this->send($orderId, true);
    }

    public function failed(int $orderId): void
    {
        $this->send($orderId, false);
    }

    private function send(int $orderId, bool $successful): void
    {
        $order = Order::query()->with(['user', 'items'])->find($orderId);

        if (! $order?->user) {
            return;
        }

        if (($successful && $order->payment_status !== 'paid')
            || (! $successful && $order->payment_status === 'paid')) {
            return;
        }

        $locale = (string) data_get($order->items->first()?->metadata, 'locale', 'en');
        $locale = str_starts_with($locale, 'ar') ? 'ar' : 'en';

        $order->user->notify(new StudentPaymentResult($order, $successful, $locale));
    }
}
