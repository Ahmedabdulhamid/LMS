<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class SettingService
{
    private const CACHE_KEY = 'application.settings';

    public function get(string $key, mixed $default = null, ?string $group = null): mixed
    {
        $setting = $this->settings()->first(fn (Setting $setting): bool =>
            $setting->key === $key && ($group === null || $setting->group === $group));

        return $setting ? $this->typedValue($setting) : $default;
    }

    public function public(): array
    {
        return $this->settings()->where('is_public', true)->where('is_encrypted', false)
            ->groupBy('group')
            ->map(fn (Collection $settings): array => $settings
                ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $this->typedValue($setting)])->all())
            ->all();
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function settings(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->get());
    }

    private function typedValue(Setting $setting): mixed
    {
        $value = $this->decryptValue($setting);

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true) ?? [],
            default => $value,
        };
    }

    public function encryptValue(
        mixed $value,
        bool $encrypted
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return $encrypted
            ? Crypt::encryptString($value)
            : $value;
    }

    public function decryptValue(Setting $setting): mixed
    {
        if ($setting->value === null) {
            return null;
        }

        if (! $setting->is_encrypted) {
            return $setting->value;
        }

        return Crypt::decryptString($setting->value);
    }
}
