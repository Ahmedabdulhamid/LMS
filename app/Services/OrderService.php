<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Exceptions\AlreadyEnrolledException;
use App\Exceptions\AlreadySubscribedException;
use App\Exceptions\CourseNotPurchasableException;
use App\Exceptions\InactiveSubscriptionPlanException;
use App\Models\Course;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const PENDING_LIFETIME_MINUTES = 30;

    public function __construct(private readonly CourseAccessService $courseAccess) {}

    public function createCourseOrder(User $user, Course $course): Order
    {
        return DB::transaction(function () use ($user, $course): Order {
            $course = Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();

            if (! $course->is_published || $course->isFree()) {
                throw new CourseNotPurchasableException('This course is not available for a paid order.');
            }

            if ($this->courseAccess->canAccessCourse($user, $course)) {
                throw new AlreadyEnrolledException('The student already has access to this course.');
            }

            $pendingOrder = Order::query()
                ->where('user_id', $user->id)
                ->where('status', OrderStatus::Pending->value)
                ->whereHas('items', fn ($query) => $query
                    ->where('purchasable_type', $course->getMorphClass())
                    ->where('purchasable_id', $course->id))
                ->latest('id')
                ->first();

            if ($pendingOrder?->created_at->gte(now()->subMinutes(self::PENDING_LIFETIME_MINUTES))) {
                return $pendingOrder->loadMissing('items');
            }

            $pendingOrder?->update(['status' => OrderStatus::Cancelled]);

            $unitPrice = $this->toCents((string) $course->price);
            $itemTotal = $this->toCents($course->effectivePrice());
            $discount = max(0, $unitPrice - $itemTotal);
            $subtotal = $unitPrice;
            $discountTotal = min($discount, $subtotal);
            $total = max(0, $subtotal - $discountTotal);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'currency' => 'EGP',
                'subtotal' => $this->fromCents($subtotal),
                'discount_total' => $this->fromCents($discountTotal),
                'total' => $this->fromCents($total),
                'status' => OrderStatus::Pending,
            ]);

            $order->items()->create([
                'purchasable_type' => $course->getMorphClass(),
                'purchasable_id' => $course->id,
                'title' => $course->title,
                'unit_price' => $this->fromCents($unitPrice),
                'discount_amount' => $this->fromCents($discountTotal),
                'total' => $this->fromCents($total),
                'metadata' => [
                    'course_slug' => $course->slug,
                    'instructor_id' => $course->instructor_id,
                    'locale' => app()->getLocale(),
                ],
            ]);

            DB::afterCommit(fn () => OrderCreated::dispatch($order));

            return $order->loadMissing('items');
        });
    }

    public function createSubscriptionPlanOrder(User $user, SubscriptionPlan $plan): Order
    {
        return DB::transaction(function () use ($user, $plan): Order {
            $plan = SubscriptionPlan::query()->whereKey($plan->id)->lockForUpdate()->firstOrFail();

            if (! $plan->is_active) {
                throw new InactiveSubscriptionPlanException;
            }

            $hasActiveSubscription = Subscription::query()
                ->where('user_id', $user->id)
                ->where('subscription_plan_id', $plan->id)
                ->where('status', 'active')
                ->where('starts_at', '<=', now())
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->exists();

            if ($hasActiveSubscription) {
                throw new AlreadySubscribedException('The student already has an active subscription to this plan.');
            }

            $pendingOrder = Order::query()
                ->where('user_id', $user->id)
                ->where('status', OrderStatus::Pending->value)
                ->whereHas('items', fn ($query) => $query
                    ->where('purchasable_type', $plan->getMorphClass())
                    ->where('purchasable_id', $plan->id))
                ->latest('id')
                ->first();

            if ($pendingOrder?->created_at->gte(now()->subMinutes(self::PENDING_LIFETIME_MINUTES))) {
                return $pendingOrder->loadMissing('items');
            }

            $pendingOrder?->update(['status' => OrderStatus::Cancelled]);

            $total = $this->toCents((string) $plan->price);
            $order = Order::query()->create([
                'user_id' => $user->id,
                'currency' => $plan->currency,
                'subtotal' => $this->fromCents($total),
                'discount_total' => '0.00',
                'total' => $this->fromCents($total),
                'status' => OrderStatus::Pending,
            ]);

            $order->items()->create([
                'purchasable_type' => $plan->getMorphClass(),
                'purchasable_id' => $plan->id,
                'title' => $plan->name,
                'unit_price' => $this->fromCents($total),
                'discount_amount' => '0.00',
                'total' => $this->fromCents($total),
                'metadata' => [
                    'duration_value' => $plan->duration_value,
                    'duration_unit' => $plan->duration_unit->value,
                    'courses_count' => $plan->courses()->count(),
                    'locale' => app()->getLocale(),
                ],
            ]);

            DB::afterCommit(fn () => OrderCreated::dispatch($order));

            return $order->loadMissing('items');
        });
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}
