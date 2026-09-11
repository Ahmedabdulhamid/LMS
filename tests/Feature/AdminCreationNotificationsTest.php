<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\SubscriptionCreated;
use App\Listeners\SendOrderNotification;
use App\Listeners\SendSubscriptionNotification;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\SubscriptionCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminCreationNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_created_notification_is_sent_to_every_admin(): void
    {
        Notification::fake();
        $admins = $this->admins();
        $student = User::factory()->create(['name' => 'Test Student']);
        $order = new Order([
            'user_id' => $student->id,
            'currency' => 'EGP',
            'total' => '125.00',
        ]);
        $order->number = 'ORD-TEST';
        $order->setRelation('user', $student);

        app(SendOrderNotification::class)->handle(new OrderCreated($order));

        Notification::assertSentTo($admins, OrderCreatedNotification::class);
        $payload = (new OrderCreatedNotification($order))->toDatabase($admins->first());
        $this->assertSame('filament', $payload['format']);
        $this->assertStringContainsString('ORD-TEST', $payload['title']);
        $this->assertStringContainsString('Test Student', $payload['body']);
    }

    public function test_subscription_created_notification_is_sent_to_every_admin(): void
    {
        Notification::fake();
        $admins = $this->admins();
        $student = User::factory()->create(['name' => 'Subscribed Student']);
        $plan = new SubscriptionPlan(['name' => 'Gold Plan']);
        $subscription = new Subscription;
        $subscription->setRelation('user', $student);
        $subscription->setRelation('plan', $plan);

        app(SendSubscriptionNotification::class)->handle(new SubscriptionCreated($subscription));

        Notification::assertSentTo($admins, SubscriptionCreatedNotification::class);
        $payload = (new SubscriptionCreatedNotification($subscription))->toDatabase($admins->first());
        $this->assertSame('filament', $payload['format']);
        $this->assertStringContainsString('Subscribed Student', $payload['body']);
        $this->assertStringContainsString('Gold Plan', $payload['body']);
    }

    private function admins()
    {
        return collect([
            Admin::query()->create([
                'name' => 'First Admin',
                'email' => 'first-admin@example.test',
                'password' => 'password',
            ]),
            Admin::query()->create([
                'name' => 'Second Admin',
                'email' => 'second-admin@example.test',
                'password' => 'password',
            ]),
        ]);
    }
}
