<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EndActiveSubscriptionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_warns_once_and_expires_ended_subscriptions(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Gold',
            'price' => 100,
            'currency' => 'EGP',
            'duration_value' => 1,
            'duration_unit' => 'month',
            'is_active' => true,
        ]);

        $expiring = Subscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(2),
            'status' => SubscriptionStatus::Active,
        ]);

        $ended = Subscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMinute(),
            'status' => SubscriptionStatus::Active,
        ]);

        $this->artisan('app:end-active-subscription')->assertSuccessful();
        $this->artisan('app:end-active-subscription')->assertSuccessful();

        Notification::assertSentToTimes($user, SubscriptionExpiringSoon::class, 1);
        $this->assertNotNull($expiring->fresh()->expiry_warning_for);
        $this->assertSame(SubscriptionStatus::Expired, $ended->fresh()->status);
    }
}
