<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\Instructor;
use App\Models\User;
use App\Services\CourseAccessService;
use App\Services\EnrollmentService;
use App\Services\StudentProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_date_is_only_set_when_payment_completes(): void
    {
        [$course] = $this->courseVideo();
        $student = User::factory()->create();
        $purchase = $course->purchases()->create([
            'user_id' => $student->id,
            'price' => 20,
            'payment_status' => 'pending',
        ]);

        $this->assertNull($purchase->purchased_at);

        $purchase->update(['payment_status' => 'completed']);

        $this->assertNotNull($purchase->fresh()->purchased_at);
    }

    public function test_course_access_rules_and_relationships(): void
    {
        [$course, $video, $instructorId] = $this->courseVideo();
        $service = app(CourseAccessService::class);
        $student = User::factory()->create();

        $this->assertFalse($service->canAccessVideo(null, $video));
        $this->assertFalse($service->canAccessVideo($student, $video));

        $course->purchases()->create([
            'user_id' => $student->id,
            'price' => 20,
            'payment_status' => 'completed',
            'purchased_at' => now(),
        ]);

        $this->assertTrue($service->canAccessCourse($student, $course));
        $this->assertTrue($service->canAccessVideo($student, $video));

        app(EnrollmentService::class)->grantFromPurchase($course->purchases()->firstOrFail());
        $this->assertCount(1, $course->students()->get());
        $this->assertCount(1, $student->enrolledCourses()->get());

        $coupon = Coupon::query()->create([
            'code' => 'RELATIONS', 'discount_type' => 'percentage', 'discount_value' => 10,
            'max_uses' => 10, 'used_count' => 1, 'is_active' => true,
        ]);
        CouponUsage::query()->create([
            'coupon_id' => $coupon->id, 'user_id' => $student->id, 'course_id' => $course->id,
        ]);

        $this->assertCount(1, $course->couponUsages()->get());
        $this->assertCount(1, $student->couponUsages()->get());
        $this->assertCount(1, $coupon->usages()->get());

        $owner = new Instructor;
        $owner->id = $instructorId;
        $admin = new Admin;
        $admin->id = 1;

        $this->assertTrue($service->canManageCourse(null, $owner, $course));
        $this->assertTrue($service->canManageCourse($admin, null, $course));

        $video->forceFill(['is_free' => true])->saveQuietly();
        $this->assertTrue($service->canAccessVideo(null, $video->refresh()));
    }

    public function test_video_playback_updates_lesson_and_course_progress(): void
    {
        [$course, $firstVideo] = $this->courseVideo();
        $student = User::factory()->create();
        $secondVideo = new CourseVideo([
            'title' => 'Second lesson', 'url' => 'courses/second.mp4', 'duration' => 60,
            'order' => 2, 'is_free' => false, 'is_published' => true,
        ]);
        $secondVideo->section_id = $firstVideo->section_id;
        $secondVideo->saveQuietly();
        $service = app(StudentProgressService::class);

        $partial = $service->record($student, $firstVideo, 30, 60);
        $this->assertSame(50, $partial->progress);
        $this->assertFalse($partial->is_completed);
        $this->assertSame(0, $student->courseProgress()->where('course_id', $course->id)->value('progress'));

        $completed = $service->record($student, $firstVideo, 54, 60);
        $this->assertTrue($completed->is_completed);
        $this->assertSame(100, $completed->progress);
        $this->assertSame(50, $student->courseProgress()->where('course_id', $course->id)->value('progress'));

        $service->record($student, $secondVideo->load('section.course'), 60, 60, ended: true);
        $courseProgress = $student->courseProgress()->where('course_id', $course->id)->firstOrFail();
        $this->assertSame(100, $courseProgress->progress);
        $this->assertNotNull($courseProgress->completed_at);
    }

    private function courseVideo(): array
    {
        $now = now();
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Owner', 'slug' => 'owner', 'email' => 'owner@example.test', 'password' => bcrypt('password'),
            'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]', 'skills' => '[]', 'experiences' => '[]',
            'specialization' => '[]', 'achivements' => '[]', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Development', 'slug' => 'development-access', 'small_description' => 'Courses', 'icon' => 'code',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $course = Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Access course',
            'description' => 'Description', 'price' => 20, 'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'course_id' => $course->id, 'title' => 'Section', 'order' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $video = new CourseVideo([
            'title' => 'Paid lesson', 'url' => 'courses/source.mp4', 'duration' => 60,
            'order' => 1, 'is_free' => false, 'is_published' => true,
        ]);
        $video->section_id = $sectionId;
        $video->saveQuietly();

        return [$course, $video->load('section.course'), $instructorId];
    }
}
