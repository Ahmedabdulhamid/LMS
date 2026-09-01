<?php

namespace Tests\Feature;

use App\Enums\EnrollmentSourceType;
use App\Enums\EnrollmentStatus;
use App\Exceptions\AlreadyEnrolledException;
use App\Exceptions\CourseNotPurchasableException;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Services\CourseAccessService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_course_order_uses_server_pricing_and_keeps_item_snapshot(): void
    {
        $course = $this->course(100, 75);
        $student = User::factory()->create();

        $order = app(OrderService::class)->createCourseOrder($student, $course);
        $item = $order->items->sole();

        $this->assertSame('100.00', $order->subtotal);
        $this->assertSame('25.00', $order->discount_total);
        $this->assertSame('75.00', $order->total);
        $this->assertSame((float) $order->subtotal, (float) $order->items->sum('unit_price'));
        $this->assertSame((float) $order->total, (float) $order->subtotal - (float) $order->discount_total);
        $this->assertGreaterThanOrEqual(0, (float) $order->total);
        $this->assertSame('100.00', $item->unit_price);
        $this->assertSame('25.00', $item->discount_amount);
        $this->assertSame('75.00', $item->total);
        $this->assertSame($course->title, $item->title);
        $this->assertTrue($item->purchasable->is($course));
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{10}$/', $order->number);

        $snapshot = [$item->title, $item->unit_price, $item->discount_amount, $item->total];
        $course->update(['title' => 'Changed title', 'price' => 500, 'price_after_discount' => null]);
        $item->refresh();

        $this->assertSame($snapshot, [$item->title, $item->unit_price, $item->discount_amount, $item->total]);
        $this->assertDatabaseCount('course_purchases', 0);
        $this->assertDatabaseCount('enrollments', 0);
        $this->assertFalse(app(CourseAccessService::class)->canAccessCourse($student, $course));
    }

    public function test_browser_price_is_ignored_and_pending_order_is_reused(): void
    {
        $course = $this->course(90, 60);
        $student = User::factory()->create();
        request()->merge(['price' => 1, 'total' => 1]);

        $first = app(OrderService::class)->createCourseOrder($student, $course);
        $second = app(OrderService::class)->createCourseOrder($student, $course);

        $this->assertTrue($first->is($second));
        $this->assertSame('60.00', $first->total);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_free_course_does_not_create_order(): void
    {
        $this->expectException(CourseNotPurchasableException::class);

        try {
            app(OrderService::class)->createCourseOrder(User::factory()->create(), $this->course(0));
        } finally {
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_active_enrollment_prevents_duplicate_purchase_order(): void
    {
        $course = $this->course();
        $student = User::factory()->create();
        Enrollment::query()->create([
            'user_id' => $student->id, 'course_id' => $course->id,
            'source_type' => EnrollmentSourceType::Admin, 'starts_at' => now(),
            'status' => EnrollmentStatus::Active,
        ]);

        $this->expectException(AlreadyEnrolledException::class);

        try {
            app(OrderService::class)->createCourseOrder($student, $course);
        } finally {
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_order_numbers_are_unique(): void
    {
        $student = User::factory()->create();
        $first = app(OrderService::class)->createCourseOrder($student, $this->course(20));
        $second = app(OrderService::class)->createCourseOrder($student, $this->course(30));

        $this->assertNotSame($first->number, $second->number);
        $this->assertSame(2, Order::query()->distinct()->count('number'));
    }

    private function course(float $price = 20, ?float $discountPrice = null): Course
    {
        $now = now();
        $suffix = str()->random(8);
        $instructorId = DB::table('instructors')->insertGetId([
            'name' => 'Teacher', 'slug' => 'order-teacher-'.$suffix, 'email' => "order-$suffix@example.test",
            'password' => bcrypt('password'), 'bio' => 'Bio', 'educations' => '[]', 'certifications' => '[]',
            'skills' => '[]', 'experiences' => '[]', 'specialization' => '[]', 'achivements' => '[]',
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category '.$suffix, 'slug' => 'order-category-'.$suffix, 'small_description' => 'Courses',
            'icon' => 'code', 'created_at' => $now, 'updated_at' => $now,
        ]);

        return Course::query()->create([
            'instructor_id' => $instructorId, 'category_id' => $categoryId, 'title' => 'Order course '.$suffix,
            'description' => 'Description', 'price' => $price, 'price_after_discount' => $discountPrice,
            'lang' => 'en', 'level' => 'beginner', 'is_published' => true,
        ]);
    }
}
