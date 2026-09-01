<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionDurationUnit;
use App\Jobs\ProcessPaymentWebhook;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PaymobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class PaymobWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set([
            'paymob.verify_hmac' => false,
            'paymob.api_key' => 'test-api-key',
            'paymob.iframe_id' => '123',
            'paymob.paymob_integration_card_id' => '111',
            'paymob.paymob_integration_wallet_id' => '222',
        ]);
    }

    public function test_valid_callback_activates_order_and_is_idempotent(): void
    {
        $order = $this->subscriptionOrder();
        $payload = $this->payload($order);

        $this->postJson(route('paymob.webhook'), $payload)->assertAccepted();
        $this->postJson(route('paymob.webhook'), $payload)->assertAccepted();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseHas('payment_webhook_events', [
            'status' => 'processed',
            'delivery_count' => 2,
        ]);
    }

    public function test_callback_with_wrong_amount_currency_or_integration_is_rejected(): void
    {
        foreach ([
            ['amount_cents' => 1],
            ['currency' => 'USD'],
            ['integration_id' => 999],
        ] as $override) {
            $order = $this->subscriptionOrder((string) random_int(100000, 999999));
            $this->postJson(route('paymob.webhook'), $this->payload($order, $override))
                ->assertAccepted();
            $this->assertNotSame('paid', $order->fresh()->payment_status);
        }
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertSame(3, PaymentWebhookEvent::query()->where('status', 'rejected')->count());
    }

    public function test_pending_callback_does_not_grant_access(): void
    {
        $order = $this->subscriptionOrder();
        $this->postJson(route('paymob.webhook'), $this->payload($order, ['pending' => true]))
            ->assertStatus(202);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_paid_order_cannot_generate_another_payment_key(): void
    {
        Http::fake();
        $order = $this->subscriptionOrder();
        $order->update(['status' => OrderStatus::Paid, 'payment_status' => 'paid']);

        $this->expectException(RuntimeException::class);
        app(PaymobService::class)->getPaymentToken($order);
    }

    public function test_authenticated_webhook_is_persisted_safely_and_dispatched(): void
    {
        Queue::fake();
        $order = $this->subscriptionOrder();
        $payload = $this->payload($order);
        $payload['obj']['source_data'] = ['pan' => '4111111111111111'];
        $payload['obj']['billing_data'] = ['email' => 'private@example.test'];

        $this->postJson(route('paymob.webhook'), $payload)->assertAccepted();

        $event = PaymentWebhookEvent::query()->sole();
        $this->assertArrayNotHasKey('source_data', $event->payload['obj']);
        $this->assertArrayNotHasKey('billing_data', $event->payload['obj']);
        Queue::assertPushed(ProcessPaymentWebhook::class, fn (ProcessPaymentWebhook $job): bool => $job->eventId === $event->id);
    }

    public function test_invalid_signature_is_not_persisted(): void
    {
        config()->set(['paymob.verify_hmac' => true, 'paymob.hmac' => 'secret']);
        $order = $this->subscriptionOrder();

        $this->postJson(route('paymob.webhook').'?hmac=invalid', $this->payload($order))
            ->assertUnauthorized();

        $this->assertDatabaseCount('payment_webhook_events', 0);
    }

    public function test_reconciliation_requeues_failed_events(): void
    {
        Queue::fake();
        $order = $this->subscriptionOrder();
        $event = PaymentWebhookEvent::query()->create([
            'order_id' => $order->id,
            'provider' => 'paymob',
            'deduplication_key' => hash('sha256', 'failed-event'),
            'event_type' => 'transaction',
            'provider_transaction_id' => '9001',
            'provider_order_id' => $order->paymob_order_id,
            'payload' => $this->payload($order),
            'status' => 'failed',
            'received_at' => now(),
            'next_retry_at' => now()->subMinute(),
        ]);

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame('received', $event->fresh()->status);
        Queue::assertPushed(ProcessPaymentWebhook::class, fn (ProcessPaymentWebhook $job): bool => $job->eventId === $event->id);
    }

    private function subscriptionOrder(string $paymobOrderId = '987654'): Order
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Secure plan', 'price' => 300, 'currency' => 'EGP',
            'duration_value' => 1, 'duration_unit' => SubscriptionDurationUnit::Month,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id, 'currency' => 'EGP', 'subtotal' => 300,
            'discount_total' => 0, 'total' => 300, 'status' => OrderStatus::Pending,
            'payment_status' => 'pending', 'paymob_order_id' => $paymobOrderId,
        ]);
        $order->items()->create([
            'purchasable_type' => $plan->getMorphClass(), 'purchasable_id' => $plan->id,
            'title' => $plan->name, 'unit_price' => 300, 'discount_amount' => 0,
            'total' => 300, 'metadata' => ['duration_value' => 1, 'duration_unit' => 'month'],
        ]);

        return $order;
    }

    private function payload(Order $order, array $override = []): array
    {
        static $transactionId = 5000;

        return ['type' => 'TRANSACTION', 'obj' => array_merge([
            'id' => ++$transactionId,
            'order' => ['id' => $order->paymob_order_id],
            'amount_cents' => 30000,
            'currency' => 'EGP',
            'integration_id' => 111,
            'success' => true,
            'pending' => false,
            'error_occured' => false,
            'is_refunded' => false,
            'is_voided' => false,
        ], $override)];
    }
}
