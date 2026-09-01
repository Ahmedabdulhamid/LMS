<?php

namespace Database\Seeders;

use App\Enums\SubscriptionDurationUnit;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Seed the default subscription plans.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Monthly Plan',
                'description' => 'Access to the courses included in this plan for one month.',
                'price' => 299,
                'currency' => 'EGP',
                'duration_value' => 1,
                'duration_unit' => SubscriptionDurationUnit::Month,
                'is_active' => true,
            ],
            [
                'name' => 'Quarterly Plan',
                'description' => 'Access to the courses included in this plan for three months.',
                'price' => 799,
                'currency' => 'EGP',
                'duration_value' => 3,
                'duration_unit' => SubscriptionDurationUnit::Month,
                'is_active' => true,
            ],
            [
                'name' => 'Annual Plan',
                'description' => 'Access to the courses included in this plan for one year.',
                'price' => 2499,
                'currency' => 'EGP',
                'duration_value' => 1,
                'duration_unit' => SubscriptionDurationUnit::Year,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['name' => $plan['name']],
                $plan,
            );
        }
    }
}
