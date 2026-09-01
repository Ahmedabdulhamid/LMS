<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_checkout_and_another_student_cannot_view_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $owner->id, 'currency' => 'EGP', 'subtotal' => 20,
            'discount_total' => 0, 'total' => 20, 'status' => OrderStatus::Pending,
        ]);

        $this->get(route('checkout.orders.show', $order))->assertRedirect();
        $this->actingAs($other, 'student')->get(route('checkout.orders.show', $order))->assertNotFound();
    }
}
