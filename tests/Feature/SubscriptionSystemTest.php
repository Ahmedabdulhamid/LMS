<?php

namespace Tests\Feature;

use App\Enums\SubscriptionDurationUnit;
use App\Enums\SubscriptionStatus;
use App\Exceptions\InactiveSubscriptionPlanException;
use App\Models\Course;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class SubscriptionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_plans_and_courses_have_many_to_many_relationships(): void
    {
        $firstCourse = $this->course();
        $secondCourse = $this->course();
        $firstPlan = $this->plan();
        $secondPlan = $this->plan();

        $firstPlan->courses()->attach([$firstCourse->id, $secondCourse->id]);
        $secondPlan->courses()->attach($firstCourse->id);

        $this->assertCount(2, $firstPlan->courses);
        $this->assertCount(2, $firstCourse->subscriptionPlans);
        $this->assertDatabaseCount('plan_course', 3);
    }

    public function test_subscription_belongs_to_user_plan_and_optional_order(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan(durationValue: 2, durationUnit: SubscriptionDurationUnit::Month);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'currency' => 'EGP',
            'subtotal' => 300,
            'discount_total' => 0,
            'total' => 300,
        ]);

        $subscription = app(SubscriptionService::class)->createSubscription($user, $plan, $order);

        $this->assertTrue($subscription->user->is($user));
        $this->assertTrue($subscription->plan->is($plan));
        $this->assertTrue($subscription->order->is($order));
        $this->assertTrue($user->subscriptions->contains($subscription));
        $this->assertSame(SubscriptionStatus::Pending, $subscription->status);
        $this->assertSame(SubscriptionStatus::Pending, $subscription->fresh()->status);
        $this->assertSame(2.0, $subscription->starts_at->diffInMonths($subscription->ends_at));
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_inactive_plan_cannot_be_selected_for_future_purchase(): void
    {
        $this->expectException(InactiveSubscriptionPlanException::class);

        app(SubscriptionService::class)->createSubscription(
            User::factory()->create(),
            $this->plan(active: false),
        );
    }

    public function test_duration_validation_rejects_zero(): void
    {
        $plan = $this->plan();
        DB::table('subscription_plans')->where('id', $plan->id)->update(['duration_value' => 0]);

        $this->expectException(InvalidArgumentException::class);
        app(SubscriptionService::class)->createSubscription(User::factory()->create(), $plan->fresh());
    }

    public function test_price_validation_rejects_zero(): void
    {
        $plan = $this->plan();
        DB::table('subscription_plans')->where('id', $plan->id)->update(['price' => 0]);

        $this->expectException(InvalidArgumentException::class);
        app(SubscriptionService::class)->createSubscription(User::factory()->create(), $plan->fresh());
    }

    public function test_subscription_plan_can_remain_an_order_item_purchasable(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'currency' => 'EGP',
            'subtotal' => $plan->price,
            'discount_total' => 0,
            'total' => $plan->price,
        ]);

        $item = $order->items()->create([
            'purchasable_type' => $plan->getMorphClass(),
            'purchasable_id' => $plan->id,
            'title' => $plan->name,
            'unit_price' => $plan->price,
            'discount_amount' => 0,
            'total' => $plan->price,
        ]);

        $this->assertTrue($item->purchasable->is($plan));
        $this->assertTrue($plan->orderItems->contains($item));
    }

    private function plan(
        int $durationValue = 1,
        SubscriptionDurationUnit $durationUnit = SubscriptionDurationUnit::Month,
        bool $active = true,
    ): SubscriptionPlan {
        return SubscriptionPlan::query()->create([
            'name' => 'Plan '.str()->random(8),
            'description' => 'Plan description',
            'price' => 300,
            'currency' => 'EGP',
            'duration_value' => $durationValue,
            'duration_unit' => $durationUnit,
            'is_active' => $active,
        ]);
    }

    private function course(): Course
    {
        $now = now();
        $suffix = str()->random(8);
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher',
            'slug' => 'subscription-teacher-'.$suffix,
            'email' => "subscription-$suffix@example.test",
            'password' => bcrypt('password'),
            'bio' => 'Bio',
            'educations' => '[]',
            'certifications' => '[]',
            'skills' => '[]',
            'experiences' => '[]',
            'specialization' => '[]',
            'achivements' => '[]',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Subscription category '.$suffix,
            'slug' => 'subscription-category-'.$suffix,
            'small_description' => 'Courses',
            'icon' => 'code',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId,
            'category_id' => $categoryId,
            'title' => 'Subscription course '.$suffix,
            'description' => 'Description',
            'price' => 100,
            'lang' => 'en',
            'level' => 'beginner',
            'is_published' => true,
        ]);
    }
}
