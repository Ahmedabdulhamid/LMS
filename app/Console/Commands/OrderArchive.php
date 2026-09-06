<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderArchive as OrderArchiveModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:order-archive {--days=365 : Archive terminal orders older than this many days} {--dry-run : Show how many orders qualify without changing data}')]
#[Description('Archive old terminal orders and their related payment records')]
class OrderArchive extends Command
{
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $terminalStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Cancelled->value,
            OrderStatus::Refunded->value,
        ];

        $query = Order::query()
            ->whereIn('status', $terminalStatuses)
            ->where('created_at', '<=', $cutoff)
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('order_archive')
                ->whereColumn('order_archive.original_order_id', 'orders.id'));

        if ($this->option('dry-run')) {
            $this->info($query->count().' order(s) qualify for archival.');

            return self::SUCCESS;
        }

        $archived = 0;

        $query->select('orders.id')->chunkById(500, function ($candidates) use (&$archived, $days): void {
            $ids = $candidates->modelKeys();

            DB::transaction(function () use ($ids, &$archived, $days): void {
                $orders = Order::query()
                    ->with(['user:id,name,email', 'items', 'paymentTransactions', 'paymentWebhookEvents'])
                    ->whereKey($ids)
                    ->lockForUpdate()
                    ->get();

                foreach ($orders as $order) {
                    $archive = OrderArchiveModel::query()->firstOrNew([
                        'original_order_id' => $order->getKey(),
                    ]);

                    if ($archive->exists) {
                        continue;
                    }

                    $archive->fill([
                        'user_id' => $order->user_id,
                        'user_name' => $order->user?->name,
                        'user_email' => $order->user?->email,
                        'number' => $order->number,
                        'currency' => $order->currency,
                        'subtotal' => $order->subtotal,
                        'discount_total' => $order->discount_total,
                        'total' => $order->total,
                        'status' => $order->status->value,
                        'coupon_id' => $order->coupon_id,
                        'coupon_code' => $order->coupon_code,
                        'billing_details' => $order->billing_details,
                        'paid_at' => $order->paid_at,
                        'paymob_order_id' => $order->paymob_order_id,
                        'payment_status' => $order->payment_status,
                        'paymob_transaction_id' => $order->paymob_transaction_id,
                        'items' => $order->items->toArray(),
                        'payment_transactions' => $order->paymentTransactions->toArray(),
                        'payment_webhook_events' => $order->paymentWebhookEvents->toArray(),
                        'instructor_ids' => Course::query()
                            ->whereKey($order->items
                                ->where('purchasable_type', (new Course)->getMorphClass())
                                ->pluck('purchasable_id'))
                            ->pluck('instructor_id')
                            ->unique()
                            ->values()
                            ->all(),
                        'archive_reason' => "Terminal order older than {$days} days",
                        'original_created_at' => $order->created_at,
                        'original_updated_at' => $order->updated_at,
                        'archived_at' => now(),
                    ])->save();

                    $order->delete();
                    $archived++;
                }
            }, attempts: 3);
        });

        $this->info("Archived {$archived} order(s).");

        return self::SUCCESS;
    }
}
