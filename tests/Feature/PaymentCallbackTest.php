<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_gateway_redirect_stays_processing_until_webhook_finishes(): void
    {
        [$student, $order] = $this->makeOrder('pending');

        $this->actingAs($student, 'student')
            ->get(route('payment.callback', ['order' => $order->paymob_order_id, 'success' => 'true', 'id' => 'txn-1']))
            ->assertOk()
            ->assertViewHas('paymentState', 'processing')
            ->assertSee(__('payment-callback.states.processing.title'));
    }

    public function test_failed_gateway_redirect_displays_failure_page(): void
    {
        [$student, $order] = $this->makeOrder('pending');

        $this->actingAs($student, 'student')
            ->get(route('payment.callback', ['order' => $order->paymob_order_id, 'success' => 'false']))
            ->assertOk()
            ->assertViewHas('paymentState', 'failed')
            ->assertSee(__('payment-callback.states.failed.title'));
    }

    public function test_status_endpoint_uses_authoritative_order_status_and_enforces_ownership(): void
    {
        [$student, $order] = $this->makeOrder('pending');

        $this->actingAs($student, 'student')->getJson(route('payment.callback.status', $order))
            ->assertOk()->assertJson(['state' => 'processing']);

        $order->update(['payment_status' => 'paid', 'status' => OrderStatus::Paid]);
        $this->getJson(route('payment.callback.status', $order))
            ->assertOk()->assertJson(['state' => 'success']);

        $otherStudent = User::factory()->create();
        $this->actingAs($otherStudent, 'student')->getJson(route('payment.callback.status', $order))
            ->assertForbidden();
    }

    private function makeOrder(string $paymentStatus): array
    {
        $student = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $student->id,
            'currency' => 'EGP',
            'subtotal' => 100,
            'discount_total' => 0,
            'total' => 100,
            'status' => OrderStatus::Pending,
            'payment_status' => $paymentStatus,
            'paymob_order_id' => (string) fake()->unique()->numberBetween(100000, 999999),
        ]);

        return [$student, $order];
    }
}
