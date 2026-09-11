<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['app_name', 'website_logo', 'website_icon', 'favicon'] as $key) {
            Setting::firstOrCreate(
                ['group' => $key === 'app_name' ? 'general' : 'appearance', 'key' => $key],
                ['value' => $key === 'app_name' ? config('app.name', 'Laravel') : null,
                    'type' => $key === 'app_name' ? 'string' : 'file',
                    'is_public' => true, 'is_encrypted' => false],
            );
        }
    }
}
