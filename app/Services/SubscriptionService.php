<?php

namespace App\Services;

use App\Enums\SubscriptionDurationUnit;
use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionCreated;
use App\Exceptions\InactiveSubscriptionPlanException;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    public function createSubscription(User $user, SubscriptionPlan $plan, ?Order $order = null): Subscription
    {
        if (! $plan->is_active) {
            throw new InactiveSubscriptionPlanException;
        }

        if ((float) $plan->price <= 0) {
            throw new InvalidArgumentException('Subscription plan price must be greater than zero.');
        }

        if ($plan->duration_value < 1) {
            throw new InvalidArgumentException('Subscription duration must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $plan, $order): Subscription {
            $startsAt = now();

            $subscription = Subscription::query()->create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'order_id' => $order?->id,
                'starts_at' => $startsAt,
                'ends_at' => $this->calculateEndsAt($startsAt, $plan),
                'status' => SubscriptionStatus::Pending,
            ]);

            DB::afterCommit(fn () => SubscriptionCreated::dispatch($subscription));

            return $subscription;
        });
    }

    private function calculateEndsAt(Carbon $startsAt, SubscriptionPlan $plan): Carbon
    {
        return match ($plan->duration_unit) {
            SubscriptionDurationUnit::Day => $startsAt->copy()->addDays($plan->duration_value),
            SubscriptionDurationUnit::Month => $startsAt->copy()->addMonthsNoOverflow($plan->duration_value),
            SubscriptionDurationUnit::Year => $startsAt->copy()->addYearsNoOverflow($plan->duration_value),
        };
    }

    // TODO: Payment success -> create Subscription -> create Enrollment.
}
