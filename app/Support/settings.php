<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null, ?string $group = null): mixed
    {
        $group ??= match ($key) {
            'app_name' => 'general',
            'website_logo', 'website_icon', 'favicon' => 'appearance',
            default => null,
        };

        return app(SettingService::class)->get($key, $default, $group);
    }
}

if (! function_exists('setting_image_url')) {
    function setting_image_url(string $key, ?string $default = null): ?string
    {
        return app(SettingService::class)->imageUrl($key, $default);
    }
}
