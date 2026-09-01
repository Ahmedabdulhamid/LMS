<?php

namespace App\Jobs;

use App\Models\PaymentWebhookEvent;
use App\Services\PaymentAlertService;
use App\Services\PaymobTransactionProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPaymentWebhook implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 600;

    public function __construct(public readonly int $eventId)
    {
        $this->onQueue('payments');
    }

    public function uniqueId(): string
    {
        return (string) $this->eventId;
    }

    public function backoff(): array
    {
        return [10, 60, 300, 900];
    }

    public function handle(PaymobTransactionProcessor $processor): void
    {
        $event = PaymentWebhookEvent::query()->findOrFail($this->eventId);
        $processor->process($event);
    }

    public function failed(?Throwable $exception): void
    {
        $event = PaymentWebhookEvent::query()->find($this->eventId);
        if (! $event) {
            return;
        }

        $event->update([
            'status' => 'failed',
            'failure_reason' => $exception?->getMessage() ?? 'Queue processing failed',
            'next_retry_at' => now()->addMinutes(15),
        ]);

        app(PaymentAlertService::class)->send('Payment webhook processing failed', $event->failure_reason, [
            'event_id' => $event->id,
            'transaction_id' => $event->provider_transaction_id,
        ]);
    }
}
