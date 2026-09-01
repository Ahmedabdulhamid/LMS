<?php

namespace Tests\Feature;

use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Courses\RelationManagers\SubscriptionPlansRelationManager;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Admin;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubscriptionFilamentResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_subscription_plan_resource(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/subscription-plans')
            ->assertOk();
    }

    public function test_subscription_resources_are_not_registered_in_instructor_panel(): void
    {
        $resources = Filament::getPanel('instructors')->getResources();

        $this->assertNotContains(SubscriptionPlanResource::class, $resources);
        $this->assertNotContains(SubscriptionResource::class, $resources);
    }

    public function test_admin_can_attach_and_detach_a_course_from_a_plan(): void
    {
        $plan = $this->plan();
        $course = $this->course();

        $plan->courses()->attach($course);

        $this->assertTrue($plan->fresh()->courses->contains($course));
        $this->assertTrue($course->fresh()->subscriptionPlans->contains($plan));

        $plan->courses()->detach($course);

        $this->assertFalse($plan->fresh()->courses->contains($course));
        $this->assertFalse($course->fresh()->subscriptionPlans->contains($plan));
    }

    public function test_admin_course_resource_exposes_subscription_plans_relation_manager(): void
    {
        $this->assertContains(
            SubscriptionPlansRelationManager::class,
            CourseResource::getRelations(),
        );
    }

    public function test_subscription_resource_is_view_only(): void
    {
        $subscription = new Subscription;

        $this->assertFalse(SubscriptionResource::canCreate());
        $this->assertFalse(SubscriptionResource::canEdit($subscription));
        $this->assertFalse(SubscriptionResource::canDelete($subscription));
        $this->assertSame(['index', 'view'], array_keys(SubscriptionResource::getPages()));
    }

    private function plan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create([
            'name' => 'Monthly plan',
            'price' => 300,
            'currency' => 'EGP',
            'duration_value' => 1,
            'duration_unit' => 'month',
            'is_active' => true,
        ]);
    }

    private function course(): Course
    {
        $now = now();
        $suffix = str()->random(8);
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher',
            'slug' => 'filament-teacher-'.$suffix,
            'email' => "filament-$suffix@example.test",
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
            'name' => 'Filament category '.$suffix,
            'slug' => 'filament-category-'.$suffix,
            'small_description' => 'Courses',
            'icon' => 'code',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId,
            'category_id' => $categoryId,
            'title' => 'Filament course '.$suffix,
            'description' => 'Description',
            'price' => 100,
            'lang' => 'en',
            'level' => 'beginner',
            'is_published' => true,
        ]);
    }
}
