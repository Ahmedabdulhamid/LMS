<?php

namespace Tests\Feature;

use App\Enums\SubscriptionDurationUnit;
use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Courses\RelationManagers\SubscriptionPlansRelationManager;
use App\Filament\Resources\SubscriptionPlans\Pages\EditSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\RelationManagers\CoursesRelationManager;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Admin;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentSubscriptionResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_subscription_plan_resource(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(SubscriptionPlanResource::getUrl('index', panel: 'admin'))
            ->assertOk();
    }

    public function test_instructor_cannot_access_admin_subscription_resources(): void
    {
        $instructor = $this->instructor();

        $this->actingAs($instructor, 'instructor')
            ->get(SubscriptionPlanResource::getUrl('index', panel: 'admin'))
            ->assertRedirect();

        $instructorRoutes = collect(app('router')->getRoutes()->getRoutes())
            ->pluck('name')
            ->filter(fn (?string $name): bool => str_starts_with((string) $name, 'filament.instructors.resources.'));

        $this->assertFalse($instructorRoutes->contains(fn (string $name): bool => str_contains($name, 'subscription')));
    }

    public function test_admin_can_attach_and_detach_course_from_plan(): void
    {
        $this->actingAs($this->admin(), 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $plan = $this->plan();
        $course = $this->course();

        Livewire::test(CoursesRelationManager::class, [
            'ownerRecord' => $plan,
            'pageClass' => EditSubscriptionPlan::class,
        ])->callTableAction('attach', data: ['recordId' => $course->id]);

        $this->assertTrue($plan->courses()->whereKey($course->id)->exists());

        Livewire::test(CoursesRelationManager::class, [
            'ownerRecord' => $plan,
            'pageClass' => EditSubscriptionPlan::class,
        ])->callTableAction('detach', $course);

        $this->assertFalse($plan->courses()->whereKey($course->id)->exists());
    }

    public function test_course_resource_displays_subscription_plans_relation(): void
    {
        $course = $this->course();
        $plan = $this->plan();
        $course->subscriptionPlans()->attach($plan);

        $this->assertContains(SubscriptionPlansRelationManager::class, CourseResource::getRelations());
        $this->assertTrue($course->subscriptionPlans->contains($plan));
    }

    public function test_subscription_resource_is_view_only(): void
    {
        $this->assertFalse(SubscriptionResource::canCreate());
        $this->assertArrayNotHasKey('create', SubscriptionResource::getPages());
        $this->assertArrayNotHasKey('edit', SubscriptionResource::getPages());
        $this->assertArrayHasKey('view', SubscriptionResource::getPages());

        $subscription = new Subscription;
        $this->assertFalse(SubscriptionResource::canEdit($subscription));
        $this->assertFalse(SubscriptionResource::canDelete($subscription));
    }

    private function admin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Admin',
            'email' => str()->random(8).'@example.test',
            'password' => 'password',
        ]);
    }

    private function plan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create([
            'name' => 'Plan '.str()->random(8),
            'price' => 300,
            'currency' => 'EGP',
            'duration_value' => 1,
            'duration_unit' => SubscriptionDurationUnit::Month,
            'is_active' => true,
        ]);
    }

    private function instructor(): Instructor
    {
        return Instructor::query()->create([
            'name' => 'Instructor',
            'email' => str()->random(8).'@example.test',
            'password' => 'password',
            'bio' => 'Bio',
            'educations' => [],
            'certifications' => [],
            'skills' => [],
            'experiences' => [],
            'specialization' => [],
            'achivements' => [],
            'is_active' => true,
        ]);
    }

    private function course(): Course
    {
        $now = now();
        $suffix = str()->random(8);
        $instructor = $this->instructor();
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Filament category '.$suffix,
            'slug' => 'filament-category-'.$suffix,
            'small_description' => 'Courses',
            'icon' => 'code',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructor->id,
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
