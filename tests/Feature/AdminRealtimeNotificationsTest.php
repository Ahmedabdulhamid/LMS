<?php

namespace Tests\Feature;

use App\Events\AdminDatabaseNotificationsSent;
use App\Models\Admin;
use App\Services\ContactService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminRealtimeNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_is_saved_and_broadcast_without_a_queue_worker(): void
    {
        config(['queue.default' => 'database']);
        Event::fake([AdminDatabaseNotificationsSent::class]);
        $admin = Admin::query()->create([
            'name' => 'Admin', 'email' => 'realtime@example.test', 'password' => 'password',
        ]);

        app(ContactService::class)->storeContact([
            'name' => 'Sender', 'email' => 'sender@example.test',
            'subject' => 'Question', 'message' => 'Please help.',
        ]);

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertDatabaseCount('jobs', 0);
        Event::assertDispatchedTimes(AdminDatabaseNotificationsSent::class, 1);
        Event::assertDispatched(AdminDatabaseNotificationsSent::class, fn ($event) =>
            (string) $event->broadcastOn() === 'private-App.Models.Admin.'.$admin->id
            && $event->broadcastAs() === 'database-notifications.sent');
        $this->assertNull(Filament::getPanel('admin')->getDatabaseNotificationsPollingInterval());
    }

    public function test_admin_can_authorize_only_their_own_channel(): void
    {
        config(['broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => '123']);
        $admin = Admin::query()->create([
            'name' => 'Admin', 'email' => 'auth@example.test', 'password' => 'password',
        ]);
        require base_path('routes/channels.php');
        $this->actingAs($admin, 'admin');
        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456', 'channel_name' => 'private-App.Models.Admin.'.$admin->id,
        ])->assertOk()->assertJsonStructure(['auth']);
        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456', 'channel_name' => 'private-App.Models.Admin.'.($admin->id + 1),
        ])->assertForbidden();
    }
}
