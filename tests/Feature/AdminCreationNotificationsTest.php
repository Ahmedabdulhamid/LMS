<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\ContactCreated;
use App\Events\SubscriptionCreated;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\ContactBroadcastNotification;
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

        OrderCreated::dispatch($order);

        Notification::assertSentTo($admins, OrderCreatedNotification::class);
        Notification::assertCount($admins->count());
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

        SubscriptionCreated::dispatch($subscription);

        Notification::assertSentTo($admins, SubscriptionCreatedNotification::class);
        Notification::assertCount($admins->count());
        $payload = (new SubscriptionCreatedNotification($subscription))->toDatabase($admins->first());
        $this->assertSame('filament', $payload['format']);
        $this->assertStringContainsString('Subscribed Student', $payload['body']);
        $this->assertStringContainsString('Gold Plan', $payload['body']);
    }

    public function test_contact_created_sends_exactly_one_notification_per_admin(): void
    {
        Notification::fake();
        $admins = $this->admins();
        $contact = new Contact([
            'name' => 'Test Contact',
            'email' => 'contact@example.test',
            'subject' => 'Test message',
            'message' => 'Please help with my course.',
        ]);

        ContactCreated::dispatch($contact);

        foreach ($admins as $admin) {
            Notification::assertSentToTimes($admin, ContactBroadcastNotification::class, 1);
        }
        Notification::assertCount($admins->count());
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
