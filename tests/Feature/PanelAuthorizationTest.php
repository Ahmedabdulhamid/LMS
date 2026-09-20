<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Order;
use App\Models\User;
use App\Policies\CoursePolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Filament\Facades\Filament;
use Tests\TestCase;

class PanelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_and_password_reset_links_only_appear_for_students_and_instructors(): void
    {
        $this->get(route('filament.students.auth.login'))
            ->assertOk()
            ->assertSee(route('filament.students.auth.register'), false)
            ->assertSee(route('filament.students.auth.password-reset.request'), false);

        $this->get(route('filament.instructors.auth.login'))
            ->assertOk()
            ->assertSee(route('filament.instructors.auth.register'), false)
            ->assertSee(route('filament.instructors.auth.password-reset.request'), false);

        $this->get(route('filament.admin.auth.login'))
            ->assertOk()
            ->assertDontSee(__('auth-links.create_account'))
            ->assertDontSee(__('auth-links.forgot_password'));
    }

    public function test_authenticated_users_are_redirected_from_other_panels_to_their_own_dashboard(): void
    {
        $student = User::factory()->create(['is_active' => true]);
        $this->actingAs($student, 'student');
        $this->get('/admin')->assertRedirect(route('filament.students.pages.dashboard'));
        $this->get('/instructors')->assertRedirect(route('filament.students.pages.dashboard'));

        auth('student')->logout();
        $instructor = $this->makeInstructor();
        $this->actingAs($instructor, 'instructor');
        $this->get('/admin')->assertRedirect(route('filament.instructors.pages.dashboard'));
        $this->get('/students')->assertRedirect(route('filament.instructors.pages.dashboard'));

        auth('instructor')->logout();
        $admin = Admin::query()->create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'Password123!',
        ]);
        $this->actingAs($admin, 'admin');
        $this->get('/students')->assertRedirect(route('filament.admin.pages.dashboard'));
        $this->get('/instructors')->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_instructor_verification_link_uses_instructor_authentication_and_verifies_account(): void
    {
        $instructor = $this->makeInstructor(['email_verified_at' => null]);
        Filament::setCurrentPanel(Filament::getPanel('instructors'));
        $this->assertStringContainsString(
            '/instructors/email-verification/verify/',
            Filament::getVerifyEmailUrl($instructor),
        );
        $verificationUrl = URL::temporarySignedRoute(
            'filament.instructors.auth.email-verification.verify',
            now()->addMinutes(60),
            ['id' => $instructor->getKey(), 'hash' => sha1($instructor->getEmailForVerification())],
        );

        $this->get($verificationUrl)
            ->assertRedirect(route('filament.instructors.auth.login'));

        $this->actingAs($instructor, 'instructor')
            ->get($verificationUrl)
            ->assertRedirect();

        $this->assertNotNull($instructor->fresh()->email_verified_at);
    }

    public function test_record_policies_enforce_ownership_between_instructors_and_students(): void
    {
        $owner = $this->makeInstructor();
        $other = $this->makeInstructor();
        $course = new Course(['instructor_id' => $owner->id]);

        $coursePolicy = app(CoursePolicy::class);
        $this->assertTrue($coursePolicy->update($owner, $course));
        $this->assertFalse($coursePolicy->update($other, $course));

        $student = User::factory()->create(['is_active' => true]);
        $otherStudent = User::factory()->create(['is_active' => true]);
        $order = new Order(['user_id' => $student->id]);
        $orderPolicy = app(OrderPolicy::class);

        $this->assertTrue($orderPolicy->view($student, $order));
        $this->assertFalse($orderPolicy->view($otherStudent, $order));
    }

    private function makeInstructor(array $attributes = []): Instructor
    {
        return Instructor::query()->create(array_merge([
            'name' => 'Instructor', 'email' => fake()->unique()->safeEmail(),
            'password' => 'Password123!', 'bio' => 'Instructor biography',
            'phone' => fake()->unique()->numerify('010########'),
            'educations' => [], 'certifications' => [], 'skills' => [],
            'experiences' => [], 'specialization' => [], 'achivements' => [],
            'is_active' => true, 'email_verified_at' => now(),
        ], $attributes));
    }
}
