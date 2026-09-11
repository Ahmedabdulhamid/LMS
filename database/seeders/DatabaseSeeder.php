<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingSeeder::class);

        Model::withoutEvents(fn () => $this->call([
            AdminSeeder::class,
            InstructorSeeder::class,
            UserSeeder::class,
            CouponSeeder::class,
            SubscriptionPlanSeeder::class,
        ]));
    }
}
