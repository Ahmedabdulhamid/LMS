<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validateCoupon(string $code, User $user, ?Course $course = null): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon) {
            $this->fail('The coupon code is invalid.');
        }

        if (! $coupon->is_active) {
            $this->fail('This coupon is inactive.');
        }

        if ($coupon->start_date?->isFuture()) {
            $this->fail('This coupon is not active yet.');
        }

        if ($coupon->end_date?->isPast()) {
            $this->fail('This coupon has expired.');
        }

        if ($coupon->used_count >= $coupon->max_uses) {
            $this->fail('This coupon has reached its usage limit.');
        }

        $previouslyUsed = $coupon->usages()
            ->where('user_id', $user->id)
            ->when($course, fn ($query) => $query->where('course_id', $course->id))
            ->exists();

        if ($previouslyUsed) {
            $this->fail('You have already used this coupon.');
        }

        $value = (float) $coupon->discount_value;

        if ($value <= 0 || ($coupon->discount_type === 'percentage' && $value > 100)) {
            $this->fail('The coupon discount is invalid.');
        }

        return $coupon;
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $subtotal = max(0, $subtotal);
        $value = max(0, (float) $coupon->discount_value);

        $discount = $coupon->discount_type === 'percentage'
            ? $subtotal * min($value, 100) / 100
            : $value;

        return round(min($discount, $subtotal), 2);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['coupon' => $message]);
    }
}
