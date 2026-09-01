<?php

namespace Tests\Feature;

use App\Livewire\ShowCourse;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login_with_course_as_intended_url(): void
    {
        $course = $this->course();

        Livewire::test(ShowCourse::class, ['course' => $course])
            ->call('enroll')
            ->assertRedirect(route('filament.students.auth.login'));

        $this->assertSame(route('courses.show', $course->slug), session('url.intended'));
    }

    public function test_purchased_student_can_continue_without_creating_another_purchase(): void
    {
        $course = $this->course();
        $student = User::factory()->create();
        $course->purchases()->create([
            'user_id' => $student->id, 'price' => 20, 'payment_status' => 'completed', 'purchased_at' => now(),
        ]);

        $this->actingAs($student, 'student');

        Livewire::test(ShowCourse::class, ['course' => $course])->call('enroll');

        $this->assertSame(1, $course->purchases()->count());
    }

    public function test_unpaid_student_gets_pending_order_without_access_or_legacy_purchase(): void
    {
        $course = $this->course();
        $student = User::factory()->create();
        $this->actingAs($student, 'student');

        Livewire::test(ShowCourse::class, ['course' => $course])->call('enroll');

        $this->assertFalse($course->purchases()->where('user_id', $student->id)->exists());
        $this->assertFalse($course->enrollments()->where('user_id', $student->id)->exists());
        $this->assertDatabaseHas('orders', ['user_id' => $student->id, 'status' => 'pending']);
    }

    private function course(): Course
    {
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'enroll-teacher-'.str()->random(5), 'email' => str()->random(5).'@example.test',
            'password' => bcrypt('password'), 'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]',
            'skills' => '[]', 'experiences' => '[]', 'specialization' => '[]', 'achivements' => '[]',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category', 'slug' => 'enroll-category-'.str()->random(5), 'small_description' => 'Courses',
            'icon' => 'code', 'created_at' => $now, 'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId,
            'title' => 'Enroll course '.str()->random(5), 'description' => 'Description',
            'price' => 20, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
    }
}
