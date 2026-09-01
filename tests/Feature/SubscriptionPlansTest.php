<?php

namespace Tests\Feature;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\SubscriptionDurationUnit;
use App\Enums\SubscriptionStatus;
use App\Livewire\SubscriptionPlans;
use App\Models\Course;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'paymob.api_key' => 'test-api-key',
            'paymob.iframe_id' => '123',
            'paymob.paymob_integration_card_id' => '111',
        ]);
        Http::fake([
            '*/auth/tokens' => Http::response(['token' => 'auth-token']),
            '*/ecommerce/orders' => Http::response(['id' => 987654]),
            '*/acceptance/payment_keys' => Http::response(['token' => 'payment-token']),
        ]);
    }

    public function test_active_plans_appear_and_inactive_plans_are_hidden(): void
    {
        $active = $this->plan('Active plan');
        $inactive = $this->plan('Inactive plan', false);

        $this->get(route('subscription-plans.index'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_courses_are_eager_loaded_and_displayed_with_plan(): void
    {
        $plan = $this->plan('Physics plan');
        $plan->courses()->attach($this->course()->id);

        Livewire::test(SubscriptionPlans::class)
            ->assertSee('Subscription course')
            ->assertSee('1 course');
    }

    public function test_guest_is_redirected_to_login_with_intended_url(): void
    {
        Livewire::test(SubscriptionPlans::class)
            ->call('subscribe', $this->plan()->id)
            ->assertRedirect(route('filament.students.auth.login'));

        $this->assertSame(route('subscription-plans.index'), session('url.intended'));
    }

    public function test_student_creates_pending_subscription_plan_order_and_redirects_to_checkout(): void
    {
        $student = User::factory()->create();
        $plan = $this->plan();
        $this->actingAs($student, 'student');

        Livewire::test(SubscriptionPlans::class)
            ->call('subscribe', $plan->id)
            ->assertRedirect(route('checkout.orders.show', Order::query()->first()));

        $order = Order::query()->firstOrFail();
        $item = $order->items->sole();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame($plan->getMorphClass(), $item->purchasable_type);
        $this->assertSame($plan->id, $item->purchasable_id);
        $this->assertSame(['duration_value' => 1, 'duration_unit' => 'month', 'courses_count' => 0], $item->metadata);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_active_subscription_redirects_student_to_my_courses(): void
    {
        $student = User::factory()->create();
        $plan = $this->plan();
        Subscription::query()->create([
            'user_id' => $student->id, 'subscription_plan_id' => $plan->id,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(),
            'status' => SubscriptionStatus::Active,
        ]);
        $this->actingAs($student, 'student');

        Livewire::test(SubscriptionPlans::class)
            ->call('subscribe', $plan->id)
            ->assertRedirect(route('my-courses.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_my_courses_shows_courses_from_an_active_enrollment(): void
    {
        $student = User::factory()->create();
        $course = $this->course();
        $this->plan()->courses()->attach($course);

        $student->enrollments()->create([
            'course_id' => $course->id,
            'source_type' => EnrollmentSourceType::Admin,
            'source_id' => null,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => EnrollmentStatus::Active,
        ]);

        $this->actingAs($student, 'student')
            ->get(route('my-courses.index'))
            ->assertOk()
            ->assertSee($course->title);
    }

    private function plan(string $name = 'Plan', bool $active = true): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create([
            'name' => $name, 'description' => 'Plan description', 'price' => 300,
            'currency' => 'EGP', 'duration_value' => 1,
            'duration_unit' => SubscriptionDurationUnit::Month, 'is_active' => $active,
        ]);
    }

    private function course(): Course
    {
        $suffix = str()->random(8);
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'plans-teacher-'.$suffix, 'email' => 'plans-'.$suffix.'@example.test',
            'password' => bcrypt('password'), 'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]',
            'skills' => '[]', 'experiences' => '[]', 'specialization' => '[]', 'achivements' => '[]',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Plans category '.$suffix, 'slug' => 'plans-category-'.$suffix, 'small_description' => 'Courses',
            'icon' => 'code', 'created_at' => $now, 'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId,
            'title' => 'Subscription course '.$suffix, 'description' => 'Description', 'price' => 100,
            'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
    }
}
