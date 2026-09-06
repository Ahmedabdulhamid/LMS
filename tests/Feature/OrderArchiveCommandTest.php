<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderArchive;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderArchiveCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_archives_terminal_orders_with_related_payment_data(): void
    {
        $user = User::factory()->create();
        $old = now()->subDays(400);

        $paidOrder = Order::query()->forceCreate([
            'user_id' => $user->id,
            'number' => 'ORD-ARCHIVE-PAID',
            'currency' => 'EGP',
            'subtotal' => 100,
            'discount_total' => 10,
            'total' => 90,
            'status' => OrderStatus::Paid,
            'billing_details' => ['city' => 'Cairo'],
            'payment_status' => 'completed',
            'created_at' => $old,
            'updated_at' => $old,
        ]);

        $pendingOrder = Order::query()->forceCreate([
            'user_id' => $user->id,
            'number' => 'ORD-ARCHIVE-PENDING',
            'currency' => 'EGP',
            'subtotal' => 50,
            'discount_total' => 0,
            'total' => 50,
            'status' => OrderStatus::Pending,
            'created_at' => $old,
            'updated_at' => $old,
        ]);

        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'purchasable_type' => 'course',
            'purchasable_id' => 123,
            'title' => 'Archived course',
            'unit_price' => 100,
            'discount_amount' => 10,
            'total' => 90,
            'metadata' => ['source' => 'test'],
        ]);

        PaymentTransaction::query()->create([
            'order_id' => $paidOrder->id,
            'provider' => 'paymob',
            'provider_transaction_id' => 'TX-ARCHIVE-1',
            'amount_cents' => 9000,
            'currency' => 'EGP',
            'status' => 'success',
            'received_at' => $old,
        ]);

        PaymentWebhookEvent::query()->create([
            'order_id' => $paidOrder->id,
            'provider' => 'paymob',
            'deduplication_key' => 'archive-webhook-1',
            'event_type' => 'transaction.processed',
            'payload' => ['secret' => 'sensitive-value'],
            'status' => 'processed',
            'received_at' => $old,
        ]);

        $this->assertSame(Command::SUCCESS, Artisan::call('app:order-archive'));

        $this->assertDatabaseMissing('orders', ['id' => $paidOrder->id]);
        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id]);

        $archive = OrderArchive::query()->where('original_order_id', $paidOrder->id)->firstOrFail();
        $this->assertSame(OrderStatus::Paid->value, $archive->status);
        $this->assertSame($user->name, $archive->user_name);
        $this->assertSame('Archived course', $archive->items[0]['title']);
        $this->assertSame('TX-ARCHIVE-1', $archive->payment_transactions[0]['provider_transaction_id']);
        $this->assertSame('sensitive-value', $archive->payment_webhook_events[0]['payload']['secret']);
        $this->assertStringNotContainsString(
            'sensitive-value',
            (string) DB::table('order_archive')->where('id', $archive->id)->value('payment_webhook_events'),
        );
    }

    public function test_dry_run_does_not_change_data(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->forceCreate([
            'user_id' => $user->id,
            'number' => 'ORD-ARCHIVE-DRY-RUN',
            'currency' => 'EGP',
            'subtotal' => 10,
            'discount_total' => 0,
            'total' => 10,
            'status' => OrderStatus::Paid,
            'created_at' => now()->subDays(400),
            'updated_at' => now()->subDays(400),
        ]);

        $this->assertSame(Command::SUCCESS, Artisan::call('app:order-archive', ['--dry-run' => true]));
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseCount('order_archive', 0);
    }
}
