<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        Instructor::updateOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name' => 'Instructor',
                'slug' => Str::slug('Instructor'),
                'password' => Hash::make('password'),

                'bio' => 'This is a sample bio for the instructor.',
                'small_description' => 'Senior Laravel Instructor',

                'phone' => '1234567890',
                'gender' => 'male',
                'birthday' => '1995-01-01',

                'educations' => [
                    [
                        'degree' => 'Bachelor of Computer Science',
                        'institution' => 'Cairo University',
                        'year' => '2018',
                    ],
                ],

                'certifications' => [
                    'Laravel Certified Developer',
                    'AWS Cloud Practitioner',
                ],

                'skills' => [
                    'Laravel',
                    'PHP',
                    'MySQL',
                    'JavaScript',
                    'REST API',
                ],

                'experiences' => [
                    [
                        'company' => 'OpenAI Academy',
                        'position' => 'Senior Instructor',
                        'years' => '5',
                    ],
                ],

                'specialization' => [
                    'Laravel',
                    'Backend Development',
                ],

                'achivements' => [
                    'Published 20+ courses',
                    'Trained over 5000 students',
                ],

                'years_of_experience' => 5,

                'linkedin_url' => 'https://linkedin.com/in/instructor',
                'facebook_url' => 'https://facebook.com/instructor',
                'twitter_url' => 'https://twitter.com/instructor',
                'youtube_url' => 'https://youtube.com/@instructor',

                'is_active' => true,
                'email_verified_at' => now(),

                'rank_id' => null,
            ]
        );
    }
}
