<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\Admin::updateOrCreate(
            [

                'email' => 'admin@example.com',

            ],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'phone' => '1234567890',
            ]
        );
    }
}
