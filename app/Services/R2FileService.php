<?php

namespace App\Services;

use Aws\CommandInterface;
use Aws\S3\Transfer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

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

    /** @return resource */
    public function readStream(string $key)
    {
        $this->ensureValidKey($key);
        $stream = Storage::disk($this->disk())->readStream($key);
        if (! is_resource($stream)) {
            throw new RuntimeException('The private video object could not be opened.');
        }

        return $stream;
    }

    /** @param resource $stream */
    public function writeStream(string $key, $stream, string $contentType): void
    {
        $this->ensureValidHlsKey($key);
        Storage::disk($this->disk())->writeStream($key, $stream, [
            'ContentType' => $contentType,
        ]);
    }

    public function uploadHlsDirectory(string $directory, string $prefix): void
    {
        $this->ensureValidHlsKey($prefix.'/master.m3u8');

        $transfer = new Transfer(
            Storage::disk($this->disk())->getClient(),
            $directory,
            sprintf('s3://%s/%s', $this->bucket(), trim($prefix, '/')),
            [
                'concurrency' => max(1, (int) config('video.upload_concurrency', 12)),
                'before' => function (CommandInterface $command): void {
                    if ($command->getName() !== 'PutObject') {
                        return;
                    }

                    $command['ContentType'] = $this->contentType((string) $command['Key']);
                },
            ],
        );
        $transfer->transfer();
    }

    public function get(string $key): string
    {
        $this->ensureValidHlsKey($key);

        return Storage::disk($this->disk())->get($key);
    }

    public function hlsTemporaryUrl(string $key): string
    {
        $this->ensureValidHlsKey($key);

        return Storage::disk($this->disk())->temporaryUrl(
            $key,
            now()->addMinutes((int) config('video.signed_url_minutes', 10)),
            ['ResponseContentType' => $this->contentType($key)],
        );
    }

    private function ensureValidHlsKey(string $key): void
    {
        if (! preg_match('#^courses/\d+/videos/\d+/hls/[A-Za-z0-9_./-]+$#D', $key) || Str::contains($key, '..')) {
            throw new InvalidArgumentException('Invalid private HLS object key.');
        }
    }

    private function contentType(string $key): string
    {
        return match (strtolower(pathinfo($key, PATHINFO_EXTENSION))) {
            'm3u8' => 'application/vnd.apple.mpegurl',
            'ts' => 'video/mp2t',
            'm4s' => 'video/iso.segment',
            default => 'application/octet-stream',
        };
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

    private function bucket(): string
    {
        $bucket = config('filesystems.disks.'.$this->disk().'.bucket');
        if (! is_string($bucket) || $bucket === '') {
            throw new RuntimeException('The R2 bucket is not configured.');
        }

        return $bucket;
    }
}
