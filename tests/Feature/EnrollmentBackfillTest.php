<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnrollmentBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_completed_purchases_are_backfilled_idempotently(): void
    {
        Schema::drop('enrollments');
        [$course, $otherCourse] = $this->courses();
        $student = User::factory()->create();

        DB::table('course_purchases')->insert([
            [
                'user_id' => $student->id, 'course_id' => $course->id, 'price' => 20,
                'payment_status' => 'completed', 'purchased_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => $student->id, 'course_id' => $otherCourse->id, 'price' => 20,
                'payment_status' => 'pending', 'purchased_at' => null, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $migration = require database_path('migrations/2026_08_23_000004_create_enrollments_table.php');
        $migration->up();

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id, 'course_id' => $course->id,
            'source_type' => 'one_time', 'status' => 'active',
        ]);
        $this->assertDatabaseMissing('enrollments', ['course_id' => $otherCourse->id]);
    }

    private function courses(): array
    {
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'backfill-teacher', 'email' => 'backfill@example.test',
            'password' => bcrypt('password'), 'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]',
            'skills' => '[]', 'experiences' => '[]', 'specialization' => '[]', 'achivements' => '[]',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category', 'slug' => 'backfill-category', 'small_description' => 'Courses',
            'icon' => 'code', 'created_at' => $now, 'updated_at' => $now,
        ]);

        return [
            Course::query()->create(['instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Paid one', 'description' => 'Description', 'price' => 20, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true]),
            Course::query()->create(['instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Pending one', 'description' => 'Description', 'price' => 20, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true]),
        ];
    }
}
