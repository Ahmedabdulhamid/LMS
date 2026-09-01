<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Seed reusable coupon scenarios for development and testing.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME20',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_uses' => 500,
                'used_count' => 0,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'LEARN10',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'max_uses' => 250,
                'used_count' => 0,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'STUDENT50',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'max_uses' => 100,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'is_active' => true,
            ],
            [
                'code' => 'UNLIMITED15',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_uses' => 100000,
                'used_count' => 0,
                'start_date' => null,
                'end_date' => null,
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED25',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'max_uses' => 50,
                'used_count' => 12,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonth(),
                'is_active' => true,
            ],
            [
                'code' => 'DISABLED30',
                'discount_type' => 'fixed',
                'discount_value' => 30,
                'max_uses' => 50,
                'used_count' => 0,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                $coupon,
            );
        }
    }
}
