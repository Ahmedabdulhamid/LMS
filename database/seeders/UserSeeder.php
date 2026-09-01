<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student',
                'phone' => '01000000000',
                'password' => Hash::make('password'),
                'bio' => 'This is a sample student account.',
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
    }
}
