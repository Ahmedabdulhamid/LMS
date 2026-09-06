<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentPaymentResult extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public bool $successful,
        public string $notificationLocale = 'en',
    ) {
        $this->onQueue('payments');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = str_starts_with($this->notificationLocale, 'ar') ? 'ar' : 'en';
        $this->order->loadMissing('items.purchasable');

        $hasPlan = $this->order->items->contains(
            fn ($item): bool => $item->purchasable instanceof SubscriptionPlan,
        );
        $type = $hasPlan ? 'plan' : 'course';
        $status = $this->successful ? 'success' : 'failure';

        return (new MailMessage)
            ->subject(__("payment-result.{$type}.{$status}.subject", ['order' => $this->order->number], $locale))
            ->view('emails.student-payment-result', [
                'order' => $this->order,
                'locale' => $locale,
                'successful' => $this->successful,
                'type' => $type,
                'student' => $notifiable,
            ]);
    }
}
