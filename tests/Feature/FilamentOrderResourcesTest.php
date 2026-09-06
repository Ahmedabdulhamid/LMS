<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Filament\Instructors\Resources\OrderArchives\OrderArchiveResource as InstructorOrderArchiveResource;
use App\Filament\Instructors\Resources\Orders\OrderResource as InstructorOrderResource;
use App\Filament\Resources\OrderArchives\OrderArchiveResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Order;
use App\Models\OrderArchive;
use App\Models\OrderItem;
use App\Models\User;
use App\Policies\OrderArchivePolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FilamentOrderResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_resources_are_read_only_and_localized(): void
    {
        app()->setLocale('ar');

        $this->assertSame('الطلبات', OrderResource::getNavigationLabel());
        $this->assertSame('الطلبات المؤرشفة', OrderArchiveResource::getNavigationLabel());
        $this->assertFalse(OrderResource::canCreate());
        $this->assertFalse(OrderArchiveResource::canCreate());
    }

    public function test_students_cannot_access_order_resources(): void
    {
        $student = User::factory()->create();
        $order = $this->orderFor($student, $this->instructor('owner@example.test'));
        $archive = OrderArchive::query()->create($this->archiveData($order, []));

        $this->assertFalse((new OrderPolicy)->viewAny($student));
        $this->assertFalse((new OrderPolicy)->view($student, $order));
        $this->assertFalse((new OrderArchivePolicy)->viewAny($student));
        $this->assertFalse((new OrderArchivePolicy)->view($student, $archive));
    }

    public function test_instructor_queries_are_scoped_to_owned_courses(): void
    {
        $student = User::factory()->create();
        $owner = $this->instructor('owner@example.test');
        $other = $this->instructor('other@example.test');
        $order = $this->orderFor($student, $owner);

        OrderArchive::query()->create($this->archiveData($order, [$owner->id]));

        $this->actingAs($owner, 'instructor');
        $this->assertSame(1, InstructorOrderResource::getEloquentQuery()->count());
        $this->assertSame(1, InstructorOrderArchiveResource::getEloquentQuery()->count());

        auth('instructor')->logout();
        $this->actingAs($other, 'instructor');
        $this->assertSame(0, InstructorOrderResource::getEloquentQuery()->count());
        $this->assertSame(0, InstructorOrderArchiveResource::getEloquentQuery()->count());
    }

    public function test_admin_can_render_order_list_pages(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Admin', 'email' => 'orders-admin@example.test', 'password' => 'password',
        ]);
        $student = User::factory()->create();
        $order = $this->orderFor($student, $this->instructor('view-owner@example.test'));
        $archive = OrderArchive::query()->create(array_merge($this->archiveData($order, []), [
            'items' => [['title' => 'Course', 'unit_price' => '100.00', 'metadata' => ['quantity' => 1]]],
            'payment_transactions' => [['provider' => 'paymob', 'provider_transaction_id' => 'TX-1', 'amount_cents' => 10000, 'status' => 'success', 'created_at' => now()->toISOString()]],
            'payment_webhook_events' => [['event_type' => 'processed', 'status' => 'processed', 'received_at' => now()->toISOString()]],
        ]));

        $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.resources.orders.index'))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.resources.order-archives.index'))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.resources.orders.view', $order))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.resources.order-archives.view', $archive))
            ->assertOk();
    }

    private function instructor(string $email): Instructor
    {
        return Instructor::query()->create([
            'name' => 'Instructor', 'slug' => str($email)->before('@')->slug().'-'.str()->random(5),
            'email' => $email, 'password' => 'password', 'bio' => 'Bio',
            'educations' => [], 'certifications' => [], 'skills' => [], 'experiences' => [],
            'specialization' => [], 'achivements' => [], 'is_active' => true,
        ]);
    }

    private function orderFor(User $student, Instructor $instructor): Order
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category', 'slug' => 'orders-category-'.str()->random(5),
            'small_description' => 'Description', 'icon' => 'book-open',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id, 'category_id' => $categoryId,
            'title' => 'Course', 'slug' => 'orders-course-'.str()->random(5), 'description' => 'Description',
            'price' => 100, 'lang' => 'en', 'level' => 'beginner', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $order = Order::query()->forceCreate([
            'user_id' => $student->id, 'number' => 'ORD-RESOURCE-'.str()->random(8),
            'currency' => 'EGP', 'subtotal' => 100, 'discount_total' => 0,
            'total' => 100, 'status' => OrderStatus::Paid, 'payment_status' => 'completed',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id, 'purchasable_type' => 'App\\Models\\Course',
            'purchasable_id' => $courseId, 'title' => 'Course', 'unit_price' => 100,
            'discount_amount' => 0, 'total' => 100,
        ]);

        return $order;
    }

    private function archiveData(Order $order, array $instructorIds): array
    {
        return [
            'original_order_id' => $order->id, 'user_id' => $order->user_id,
            'user_name' => $order->user->name, 'user_email' => $order->user->email,
            'number' => $order->number, 'currency' => 'EGP', 'subtotal' => 100,
            'discount_total' => 0, 'total' => 100, 'status' => 'paid',
            'payment_status' => 'completed', 'items' => [], 'payment_transactions' => [],
            'payment_webhook_events' => [], 'instructor_ids' => $instructorIds,
            'original_created_at' => now(), 'archived_at' => now(),
        ];
    }
}
