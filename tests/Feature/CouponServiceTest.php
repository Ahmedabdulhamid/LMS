<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_cannot_exceed_one_hundred(): void
    {
        $this->assertRejected(['discount_type' => 'percentage', 'discount_value' => 101]);
    }

    public function test_coupon_cannot_be_used_before_start_date(): void
    {
        $this->assertRejected(['start_date' => now()->addDay()]);
    }

    public function test_coupon_cannot_be_used_after_end_date(): void
    {
        $this->assertRejected(['end_date' => now()->subDay()]);
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        $this->assertRejected(['is_active' => false]);
    }

    public function test_maximum_uses_are_enforced(): void
    {
        $this->assertRejected(['max_uses' => 3, 'used_count' => 3]);
    }

    public function test_fixed_discount_cannot_make_total_negative(): void
    {
        $coupon = $this->coupon(['discount_type' => 'fixed', 'discount_value' => 500]);

        $this->assertSame(20.0, app(CouponService::class)->calculateDiscount($coupon, 20));
        $this->assertSame(0.0, 20 - app(CouponService::class)->calculateDiscount($coupon, 20));
    }

    private function assertRejected(array $overrides): void
    {
        $coupon = $this->coupon($overrides);

        try {
            app(CouponService::class)->validateCoupon($coupon->code, User::factory()->create());
            $this->fail('The invalid coupon was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coupon', $exception->errors());
        }
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'TEST'.str()->random(8),
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'max_uses' => 10,
            'used_count' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ], $overrides));
    }
}
