<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class R2FileService
{
    public function publicUrl(?string $key): ?string
    {
        if (blank($key)) {
            return null;
        }

        if (filter_var($key, FILTER_VALIDATE_URL)) {
            return $key;
        }

        if (Str::contains($key, '..')) {
            throw new InvalidArgumentException('Invalid R2 file key.');
        }

        $baseUrl = config('filesystems.disks.'.config('lms-upload.disk').'.url');

        return filled($baseUrl)
            ? rtrim($baseUrl, '/').'/'.ltrim($key, '/')
            : Storage::disk(config('lms-upload.disk'))->url($key);
    }

    public function temporaryUrl(string $key, int $minutes = 30): string
    {
        $this->ensureValidKey($key);

        return Storage::disk($this->disk())->temporaryUrl(
            $key,
            now()->addMinutes($minutes),
        );
    }

    public function delete(string $key): bool
    {
        $this->ensureValidKey($key);

        return Storage::disk($this->disk())->delete($key);
    }

    private function ensureValidKey(string $key): void
    {
        if (! Str::startsWith($key, 'instructors/') || Str::contains($key, '..')) {
            throw new InvalidArgumentException('Invalid R2 course file key.');
        }
    }

    private function disk(): string
    {
        return config('filesystems.uploads', 'r2_private');
    }
}
