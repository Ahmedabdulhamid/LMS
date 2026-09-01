<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Cache;

class LookUpService
{
    private const ADMINS_KEY = 'lookups.admins';

    /**
     * @return array<int, string>
     */
    public function admins(): array
    {
        return Cache::memo()->remember(
            self::ADMINS_KEY,
            now()->addHour(),
            fn (): array => Admin::query()
                ->orderBy('id', 'desc')
                ->pluck('name', 'id')
                ->all()
        );
    }

    public function clearAdmins(): void
    {
        Cache::forget(self::ADMINS_KEY);
    }
}
