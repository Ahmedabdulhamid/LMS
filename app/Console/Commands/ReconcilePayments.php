<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Jobs\ProcessPaymentWebhook;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Services\PaymentAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=100}';

    protected $description = 'Retry failed payment events and report stale Paymob orders';

    public function handle(PaymentAlertService $alerts): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $retried = 0;

        PaymentWebhookEvent::query()
            ->where('status', 'failed')
            ->where(fn ($query) => $query->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now()))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (PaymentWebhookEvent $event) use (&$retried): void {
                $event->update(['status' => 'received', 'next_retry_at' => null]);
                ProcessPaymentWebhook::dispatch($event->id);
                $retried++;
            });

        $staleBefore = now()->subMinutes(max(5, (int) config('paymob.stale_pending_minutes', 30)));
        $staleOrders = Order::query()
            ->where('status', OrderStatus::Pending->value)
            ->where('payment_status', 'pending')
            ->whereNotNull('paymob_order_id')
            ->where('updated_at', '<=', $staleBefore)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($staleOrders as $order) {
            if (Cache::add("payment-stale-alert:{$order->id}", true, now()->addHour())) {
                $alerts->send('Stale Paymob payment requires reconciliation', 'No final callback was processed for this order.', [
                    'order_id' => $order->id,
                    'order_number' => $order->number,
                    'paymob_order_id' => $order->paymob_order_id,
                ]);
            }
        }

        $this->info("Retried {$retried} failed event(s); found {$staleOrders->count()} stale order(s).");

        return self::SUCCESS;
    }
}
