<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;

class PaymobTransactionProcessor
{
    public function __construct(
        private readonly PaymobService $paymobService,
        private readonly PaymentSuccessHandler $paymentSuccessHandler,
        private readonly PaymentAlertService $alerts,
    ) {}

    public function process(PaymentWebhookEvent $event): void
    {
        DB::transaction(function () use ($event): void {
            $event = PaymentWebhookEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            if ($event->status === 'processed') {
                return;
            }

            $event->update([
                'status' => 'processing',
                'processing_attempts' => $event->processing_attempts + 1,
                'failure_reason' => null,
                'next_retry_at' => null,
            ]);

            $transaction = data_get($event->payload, 'obj');
            if (! is_array($transaction)) {
                $this->reject($event, 'Webhook transaction payload is missing');

                return;
            }

            $order = Order::query()->where('paymob_order_id', $event->provider_order_id)->lockForUpdate()->first();
            if (! $order) {
                $this->reject($event, 'Order not found for Paymob webhook');

                return;
            }

            $event->update(['order_id' => $order->id]);
            $attempt = PaymentTransaction::query()->updateOrCreate(
                ['provider' => 'paymob', 'provider_transaction_id' => $event->provider_transaction_id],
                [
                    'order_id' => $order->id,
                    'provider_order_id' => $event->provider_order_id,
                    'amount_cents' => is_numeric($transaction['amount_cents'] ?? null) ? (int) $transaction['amount_cents'] : null,
                    'currency' => strtoupper((string) ($transaction['currency'] ?? '')) ?: null,
                    'integration_id' => isset($transaction['integration_id']) ? (string) $transaction['integration_id'] : null,
                    'status' => 'received',
                    'received_at' => $event->received_at,
                ],
            );

            if ($error = $this->validateTransaction($order, $transaction)) {
                $attempt->update(['status' => 'rejected', 'failure_reason' => $error]);
                $this->reject($event, $error);

                return;
            }

            if ($this->truthy($transaction['is_refunded'] ?? false) || $this->truthy($transaction['is_voided'] ?? false)) {
                if ($order->payment_status === 'paid') {
                    $this->paymentSuccessHandler->refund($order);
                }
                $attempt->update(['status' => 'refunded', 'processed_at' => now()]);
                $this->complete($event);

                return;
            }

            if ($this->truthy($transaction['pending'] ?? false)) {
                $attempt->update(['status' => 'pending']);
                $this->complete($event);

                return;
            }

            $successful = $this->truthy($transaction['success'] ?? false)
                && ! $this->truthy($transaction['error_occured'] ?? false);
            if (! $successful) {
                if ($order->payment_status !== 'paid') {
                    $order->update([
                        'payment_status' => 'failed',
                        'paymob_transaction_id' => $event->provider_transaction_id,
                    ]);
                }
                $attempt->update(['status' => 'failed', 'processed_at' => now()]);
                $this->complete($event);

                return;
            }

            if ($order->payment_status !== 'paid') {
                $order->update(['paymob_transaction_id' => $event->provider_transaction_id]);
                $this->paymentSuccessHandler->handle($order);
            }
            $attempt->update(['status' => 'paid', 'processed_at' => now()]);
            $this->complete($event);
        }, attempts: 3);
    }

    private function validateTransaction(Order $order, array $transaction): ?string
    {
        if (! is_numeric($transaction['amount_cents'] ?? null)
            || (int) $transaction['amount_cents'] !== $this->paymobService->expectedAmountCents($order)) {
            return 'Payment amount does not match the order';
        }
        if (strtoupper((string) ($transaction['currency'] ?? '')) !== strtoupper($order->currency)) {
            return 'Payment currency does not match the order';
        }
        if (! $this->paymobService->isAllowedIntegrationId($transaction['integration_id'] ?? null)) {
            return 'Payment integration is not allowed';
        }

        return null;
    }

    private function reject(PaymentWebhookEvent $event, string $reason): void
    {
        $event->update(['status' => 'rejected', 'failure_reason' => $reason, 'processed_at' => now()]);
        DB::afterCommit(fn () => $this->alerts->send('Payment webhook rejected', $reason, [
            'event_id' => $event->id,
            'transaction_id' => $event->provider_transaction_id,
            'provider_order_id' => $event->provider_order_id,
        ]));
    }

    private function complete(PaymentWebhookEvent $event): void
    {
        $event->update(['status' => 'processed', 'processed_at' => now()]);
    }

    private function truthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
