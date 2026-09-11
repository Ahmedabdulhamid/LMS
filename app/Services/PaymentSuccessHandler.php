<?php

namespace App\Services;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\SubscriptionDurationUnit;
use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionCreated;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PaymentSuccessHandler
{
    /**
     * Handle successful payment
     * Activates purchased courses and subscription plans
     * Prevents duplicate enrollment if webhook is called multiple times
     */
    public function handle(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            try {
                $order = Order::query()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->payment_status === 'paid') {
                    Log::info('Payment already processed', ['order_id' => $order->id]);

                    return;
                }

                // Mark order as paid
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('Processing successful payment', [
                    'order_id' => $order->id,
                    'paymob_order_id' => $order->paymob_order_id,
                ]);

                // Process each order item
                $order->loadMissing('items.purchasable');

                foreach ($order->items as $item) {
                    $this->processOrderItem($item, $order);
                }

                Log::info('Payment successfully processed', [
                    'order_id' => $order->id,
                    'items_count' => $order->items->count(),
                ]);
            } catch (Throwable $e) {
                Log::error('Payment success handler failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    public function refund(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($order->status === OrderStatus::Refunded) {
                return;
            }

            $order->update([
                'payment_status' => 'refunded',
                'status' => OrderStatus::Refunded,
            ]);

            $order->loadMissing('items.purchasable');
            foreach ($order->items as $item) {
                if ($item->purchasable instanceof Course) {
                    $purchase = CoursePurchase::query()
                        ->where('user_id', $order->user_id)
                        ->where('course_id', $item->purchasable_id)
                        ->first();

                    if ($purchase) {
                        $purchase->update(['payment_status' => 'failed']);
                        Enrollment::query()
                            ->where('source_type', EnrollmentSourceType::OneTime->value)
                            ->where('source_id', $purchase->id)
                            ->update(['status' => EnrollmentStatus::Revoked->value]);
                    }
                } elseif ($item->purchasable instanceof SubscriptionPlan) {
                    $subscriptions = Subscription::query()->where('order_id', $order->id)->get();
                    foreach ($subscriptions as $subscription) {
                        $subscription->update(['status' => SubscriptionStatus::Cancelled]);
                        Enrollment::query()
                            ->where('source_type', EnrollmentSourceType::Subscription->value)
                            ->where('source_id', $subscription->id)
                            ->update(['status' => EnrollmentStatus::Revoked->value]);
                    }
                }
            }
        });
    }

    /**
     * Process individual order item
     * Handles course enrollment or subscription activation
     */
    private function processOrderItem(OrderItem $item, Order $order): void
    {
        $purchasable = $item->purchasable;

        if ($purchasable instanceof Course) {
            $this->createCoursePurchaseRecord(
                $item,
                $order,
                $purchasable
            );
        } elseif ($purchasable instanceof SubscriptionPlan) {
            $this->activateSubscription(
                $order->user_id,
                $purchasable->id,
                $order->id
            );

        }
    }

    private function createCoursePurchaseRecord(OrderItem $item, Order $order, Course $course): void
    {
        try {
            CoursePurchase::query()->updateOrCreate(
                [
                    'user_id' => $order->user_id,
                    'course_id' => $course->id,
                ],
                [
                    'price' => $item->total,
                    'coupon_code' => $order->coupon_code,
                    'coupon_discount' => $item->discount_amount,
                    'purchased_at' => $order->paid_at,
                    'payment_status' => 'completed',
                ],
            );

            Log::info('Course purchase record created', [
                'user_id' => $order->user_id,
                'course_id' => $course->id,
                'order_id' => $order->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to create course purchase record', [
                'user_id' => $order->user_id,
                'course_id' => $course->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Activate subscription for student
     * Prevents duplicate subscription activation
     */
    private function activateSubscription(int $userId, int $subscriptionPlanId, int $orderId): void
    {
        try {
            $plan = SubscriptionPlan::find($subscriptionPlanId);

            if (! $plan) {
                throw new RuntimeException("Subscription plan not found: {$subscriptionPlanId}");
            }

            // Check if subscription already exists for this order
            $existingSubscription = Subscription::query()
                ->where('user_id', $userId)
                ->where('subscription_plan_id', $subscriptionPlanId)
                ->where('order_id', $orderId)
                ->first();

            if ($existingSubscription) {
                Log::info('Subscription already activated for order', [
                    'user_id' => $userId,
                    'subscription_plan_id' => $subscriptionPlanId,
                    'order_id' => $orderId,
                ]);

                return;
            }

            // Calculate subscription dates
            $startsAt = now();
            $endsAt = $this->calculateSubscriptionEndDateFromSnapshot($startsAt, $plan, $orderId);

            $subscription = Subscription::create([
                'user_id' => $userId,
                'subscription_plan_id' => $subscriptionPlanId,
                'order_id' => $orderId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => SubscriptionStatus::Active,
            ]);

            DB::afterCommit(fn () => SubscriptionCreated::dispatch($subscription));

            foreach ($plan->courses as $course) {
                Enrollment::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'course_id' => $course->id,
                        'source_type' => EnrollmentSourceType::Subscription,
                        'source_id' => $subscription->id,
                    ],
                    [
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'status' => EnrollmentStatus::Active,
                    ],
                );
            }

            Log::info('Subscription activated', [
                'user_id' => $userId,
                'subscription_plan_id' => $subscriptionPlanId,
                'order_id' => $orderId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to activate subscription', [
                'user_id' => $userId,
                'subscription_plan_id' => $subscriptionPlanId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate subscription end date based on plan duration
     */
    private function calculateSubscriptionEndDate(
        CarbonInterface $startsAt,
        SubscriptionPlan $plan
    ): CarbonInterface {
        $duration = $plan->duration_value;
        $unit = $plan->duration_unit;

        return match ($unit) {
            SubscriptionDurationUnit::Day => $startsAt->copy()->addDays($duration),
            SubscriptionDurationUnit::Month => $startsAt->copy()->addMonthsNoOverflow($duration),
            SubscriptionDurationUnit::Year => $startsAt->copy()->addYearsNoOverflow($duration),
        };
    }

    private function calculateSubscriptionEndDateFromSnapshot(
        CarbonInterface $startsAt,
        SubscriptionPlan $plan,
        int $orderId,
    ): CarbonInterface {
        $item = OrderItem::query()
            ->where('order_id', $orderId)
            ->where('purchasable_type', $plan->getMorphClass())
            ->where('purchasable_id', $plan->id)
            ->first();
        $duration = (int) data_get($item?->metadata, 'duration_value', $plan->duration_value);
        $unit = SubscriptionDurationUnit::tryFrom((string) data_get($item?->metadata, 'duration_unit'))
            ?? $plan->duration_unit;

        if ($duration <= 0) {
            throw new RuntimeException('The purchased subscription duration is invalid.');
        }

        return match ($unit) {
            SubscriptionDurationUnit::Day => $startsAt->copy()->addDays($duration),
            SubscriptionDurationUnit::Month => $startsAt->copy()->addMonthsNoOverflow($duration),
            SubscriptionDurationUnit::Year => $startsAt->copy()->addYearsNoOverflow($duration),
        };
    }
}
