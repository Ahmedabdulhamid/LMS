<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:end-active-subscription')]
#[Description('Expire subscriptions that reached their end date')]
class EndActiveSubscription extends Command
{
    public function handle()
    {
        Subscription::where('ends_at', '<=', now())
            ->where('status', '!=', SubscriptionStatus::Expired)
            ->chunkById(100, function ($subscriptions) {

                foreach ($subscriptions as $subscription) {
                    $subscription->update([
                        'status' => SubscriptionStatus::Expired
                    ]);
                }

            });

        $this->info('Expired subscriptions updated successfully.');
    }
}
