<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class R2VideoUploadService
{
    private const DEFAULT_PART_SIZE = 25 * 1024 * 1024;

    private const MIN_PART_SIZE = 5 * 1024 * 1024;

    private const MAX_PART_SIZE = 5 * 1024 * 1024 * 1024;

    private const URL_EXPIRY_MINUTES = 30;

    public function initiate(int $instructorId, int $courseId, array $data): array
    {
        return $this->initiateFile($instructorId, $courseId, $data, 'videos');
    }

    public function initiateAttachment(int $instructorId, int $courseId, array $data): array
    {
        return $this->initiateFile($instructorId, $courseId, $data, 'attachments');
    }

    private function initiateFile(int $instructorId, int $courseId, array $data, string $directory): array
    {
        $extension = strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION));
        $extension = $extension !== '' ? '.'.$extension : '';
        $key = sprintf(
            'instructors/%d/courses/%d/%s/%s%s',
            $instructorId,
            $courseId,
            $directory,
            Str::uuid(),
            $extension,
        );

        $partSize = $this->partSizeFor($data['file_size']);
        $result = $this->client($key)->createMultipartUpload([
            'Bucket' => $this->bucket($key),
            'Key' => $key,
            'ContentType' => $data['content_type'],
            'Metadata' => [
                'original-name' => $data['file_name'],
                'uploader-id' => (string) $instructorId,
                'expected-size' => (string) $data['file_size'],
            ],
        ]);

        return [
            'upload_id' => $result->get('UploadId'),
            'key' => $key,
            'part_size' => $partSize,
            'parts_count' => (int) ceil($data['file_size'] / $partSize),
        ];
    }

    public function signPart(
        int $instructorId,
        int $courseId,
        string $key,
        string $uploadId,
        int $partNumber,
    ): array {
        $this->ensureOwnedKey($key, $instructorId, $courseId);

        $command = $this->client($key)->getCommand('UploadPart', [
            'Bucket' => $this->bucket($key),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $request = $this->client($key)->createPresignedRequest(
            $command,
            sprintf('+%d minutes', self::URL_EXPIRY_MINUTES),
        );

        return [
            'upload_url' => (string) $request->getUri(),
            'part_number' => $partNumber,
            'expires_in' => self::URL_EXPIRY_MINUTES * 60,
        ];
    }

    public function complete(
        int $instructorId,
        int $courseId,
        string $key,
        string $uploadId,
        array $parts,
    ): array {
        $this->ensureOwnedKey($key, $instructorId, $courseId);

        usort($parts, fn (array $left, array $right) => $left['part_number'] <=> $right['part_number']);

        $result = $this->client($key)->completeMultipartUpload([
            'Bucket' => $this->bucket($key),
            'Key' => $key,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => array_map(fn (array $part) => [
                    'ETag' => $part['etag'],
                    'PartNumber' => $part['part_number'],
                ], $parts),
            ],
        ]);

        return [
            'key' => $key,
            'etag' => $result->get('ETag'),
        ];
    }

    public function abort(
        int $instructorId,
        int $courseId,
        string $key,
        string $uploadId,
    ): void {
        $this->ensureOwnedKey($key, $instructorId, $courseId);

        $this->client($key)->abortMultipartUpload([
            'Bucket' => $this->bucket($key),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);
    }

    public function delete(string $key): void
    {
        if (! Str::startsWith($key, 'instructors/')) {
            return;
        }

        Storage::disk($this->disk($key))->delete($key);
    }

    private function ensureOwnedKey(string $key, int $instructorId, int $courseId): void
    {
        $expectedPrefix = sprintf('instructors/%d/courses/%d/', $instructorId, $courseId);

        if (! str_starts_with($key, $expectedPrefix)) {
            throw new AuthorizationException('The video upload key does not belong to this course.');
        }
    }

    private function partSizeFor(int $fileSize): int
    {
        $requiredSize = (int) ceil($fileSize / 10000);
        $roundedSize = (int) (ceil($requiredSize / self::MIN_PART_SIZE) * self::MIN_PART_SIZE);
        $partSize = max(self::DEFAULT_PART_SIZE, $roundedSize);

        if ($partSize > self::MAX_PART_SIZE) {
            throw new InvalidArgumentException('The video is larger than the supported R2 object size.');
        }

        return $partSize;
    }

    private function client(string $key): S3Client
    {
        /** @var S3Client $client */
        $client = Storage::disk($this->disk($key))->getClient();

        return $client;
    }

    private function bucket(string $key): string
    {
        $bucket = config(sprintf('filesystems.disks.%s.bucket', $this->disk($key)));

        if (! is_string($bucket) || $bucket === '') {
            throw new RuntimeException('The R2 bucket is not configured.');
        }

        return $bucket;
    }

    private function disk(string $key): string
    {
        return str_contains($key, '/attachments/')
            ? config('lms-upload.disk')
            : config('filesystems.uploads', 'r2_private');
    }
}
