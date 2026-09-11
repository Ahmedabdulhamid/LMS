<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public const CACHE_KEY = 'application.settings';

    public function get(string $key, mixed $default = null, ?string $group = null): mixed
    {
        $setting = $this->settings()->first(fn (Setting $setting): bool => $setting->key === $key && ($group === null || $setting->group === $group));

        return $setting ? ($this->typedValue($setting) ?? $default) : $default;
    }

    public function all(): array
    {
        return $this->settings()->groupBy('group')->map(fn (Collection $settings): array => $settings
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $this->typedValue($setting)])->all())->all();
    }

    public function forget(): void
    {
        $this->flushCache();
    }

    public function name(): string
    {
        $name = $this->get('app_name', null, 'general');

        return is_string($name) && trim($name) !== '' ? $name : (string) config('app.name', 'Laravel');
    }

    public function imageUrl(string $key, ?string $default = null): ?string
    {
        $path = $this->get($key, null, 'appearance');
        if (! is_string($path) || trim($path) === '') {
            return $default;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return in_array(strtolower((string) parse_url($path, PHP_URL_SCHEME)), ['http', 'https'], true) ? $path : $default;
        }
        if (str_contains($path, '..') || str_contains($path, ':') || str_starts_with($path, '//')) {
            return $default;
        }

        return Storage::disk(config('lms-upload.disk'))->url($path);
    }

    public function logoUrl(): string
    {
        return $this->imageUrl('website_logo', asset('images/learning-platform-logo.png'));
    }

    public function faviconUrl(): ?string
    {
        return $this->imageUrl('favicon', $this->imageUrl('website_icon'));
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
        try {
            // Uncommitted settings must never leak into the shared cache.
            if ((new Setting)->getConnection()->transactionLevel() > 0) {
                return Setting::query()->orderBy('id')->get();
            }

            return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->orderBy('id')->get());
        } catch (QueryException) {
            // Installation and maintenance may run before the settings table exists.
            // Do not cache this fallback: a later request must retry the database.
            return collect();
        }
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
