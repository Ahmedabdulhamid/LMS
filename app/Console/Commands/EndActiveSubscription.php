<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:end-active-subscription {--warning-days=3 : Send an expiry warning this many days in advance}')]
#[Description('Warn students about subscriptions nearing expiry and expire ended subscriptions')]
class EndActiveSubscription extends Command
{
    public function handle(): int
    {
        $warningDays = max(1, (int) $this->option('warning-days'));
        $now = now();
        $warningDeadline = $now->copy()->addDays($warningDays);
        $warningsSent = 0;
        $expired = 0;

        Subscription::query()
            ->with(['user:id,name,email', 'plan:id,name'])
            ->where('status', SubscriptionStatus::Active)
            ->where('ends_at', '>', $now)
            ->where('ends_at', '<=', $warningDeadline)
            ->whereNotNull('user_id')
            ->where(function ($query): void {
                $query->whereNull('expiry_warning_for')
                    ->orWhereColumn('expiry_warning_for', '!=', 'ends_at');
            })
            ->chunkById(100, function ($subscriptions) use (&$warningsSent): void {
                foreach ($subscriptions as $subscription) {
                    if (! $subscription->user || blank($subscription->user->email)) {
                        continue;
                    }

                    $subscription->user->notify(new SubscriptionExpiringSoon(
                        subscription: $subscription,
                        daysRemaining: max(1, (int) ceil(now()->diffInSeconds($subscription->ends_at) / 86400)),
                        notificationLocale: app()->getLocale(),
                    ));

                    $subscription->forceFill([
                        'expiry_warning_for' => $subscription->ends_at,
                    ])->saveQuietly();

                    $warningsSent++;
                }
            });

        Subscription::query()
            ->where('ends_at', '<=', $now)
            ->where('status', '!=', SubscriptionStatus::Expired)
            ->chunkById(100, function ($subscriptions) use (&$expired): void {

                foreach ($subscriptions as $subscription) {
                    $subscription->update([
                        'status' => SubscriptionStatus::Expired,
                    ]);
                    $expired++;
                }
            });

        $this->info("Queued {$warningsSent} expiry warning email(s); expired {$expired} subscription(s).");

        return self::SUCCESS;
    }
}
