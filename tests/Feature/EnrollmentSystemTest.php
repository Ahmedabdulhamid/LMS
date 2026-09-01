<?php

namespace Tests\Feature;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Enums\SubscriptionDurationUnit;
use App\Enums\SubscriptionStatus;
use App\Livewire\ShowCourse;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CourseAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollmentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_current_active_enrollments_grant_access(): void
    {
        $course = $this->course();
        $student = User::factory()->create();
        $service = app(CourseAccessService::class);

        $active = $this->enrollment($student, $course);
        $this->assertTrue($service->canAccessCourse($student, $course));

        $active->update(['status' => EnrollmentStatus::Revoked]);
        $this->assertFalse($service->canAccessCourse($student, $course));

        $future = $this->enrollment($student, $course, ['starts_at' => now()->addDay()]);
        $this->assertFalse($service->canAccessCourse($student, $course));

        $future->update(['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);
        $this->assertFalse($service->canAccessCourse($student, $course));

        $future->update(['ends_at' => null, 'status' => EnrollmentStatus::Expired]);
        $this->assertFalse($service->canAccessCourse($student, $course));
    }

    public function test_free_course_enroll_is_idempotent(): void
    {
        $course = $this->course(0);
        $student = User::factory()->create();
        $this->actingAs($student, 'student');

        Livewire::test(ShowCourse::class, ['course' => $course])->call('enroll');
        Livewire::test(ShowCourse::class, ['course' => $course])->call('enroll');

        $this->assertSame(1, Enrollment::query()
            ->forUser($student)
            ->forCourse($course)
            ->active()
            ->count());
        $this->assertSame(EnrollmentSourceType::Free, Enrollment::query()->firstOrFail()->source_type);
    }

    public function test_legacy_completed_purchase_still_grants_access_without_enrollment(): void
    {
        $course = $this->course();
        $student = User::factory()->create();

        DB::table('course_purchases')->insert([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'price' => 20,
            'payment_status' => 'completed',
            'purchased_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(Enrollment::query()->exists());
        $this->assertTrue(app(CourseAccessService::class)->canAccessCourse($student, $course));
    }

    public function test_expired_subscription_does_not_grant_access_through_its_enrollment(): void
    {
        $course = $this->course();
        $student = User::factory()->create();
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Expired plan', 'description' => 'Expired', 'price' => 100,
            'currency' => 'EGP', 'duration_value' => 1,
            'duration_unit' => SubscriptionDurationUnit::Month, 'is_active' => true,
        ]);
        $subscription = Subscription::query()->create([
            'user_id' => $student->id, 'subscription_plan_id' => $plan->id,
            'starts_at' => now()->subMonth(), 'ends_at' => now()->addMonth(),
            'status' => SubscriptionStatus::Expired,
        ]);
        $student->enrollments()->create([
            'course_id' => $course->id, 'source_type' => EnrollmentSourceType::Subscription,
            'source_id' => $subscription->id, 'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(), 'status' => EnrollmentStatus::Active,
        ]);

        $this->assertFalse(app(CourseAccessService::class)->canAccessCourse($student, $course));
        $this->assertCount(0, $student->enrolledCourses()->get());
    }

    private function enrollment(User $user, Course $course, array $overrides = []): Enrollment
    {
        return Enrollment::query()->create(array_merge([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'source_type' => EnrollmentSourceType::Admin,
            'source_id' => null,
            'starts_at' => now()->subMinute(),
            'ends_at' => null,
            'status' => EnrollmentStatus::Active,
        ], $overrides));
    }

    private function course(float $price = 20): Course
    {
        $now = now();
        $suffix = str()->random(8);
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'teacher-'.$suffix, 'email' => "teacher-$suffix@example.test",
            'password' => bcrypt('password'), 'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]',
            'skills' => '[]', 'experiences' => '[]', 'specialization' => '[]', 'achivements' => '[]',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category', 'slug' => 'category-'.$suffix, 'small_description' => 'Courses',
            'icon' => 'code', 'created_at' => $now, 'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Course '.$suffix,
            'description' => 'Description', 'price' => $price, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
    }
}
