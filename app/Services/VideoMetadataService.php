<?php

namespace App\Services;

use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Log;
use Throwable;

class VideoMetadataService
{
    public function probe(string $url): array
    {
        try {
            $ffprobe = FFProbe::create([
                'ffmpeg.binaries' => config('video.ffmpeg'),
                'ffprobe.binaries' => config('video.ffprobe'),
                'timeout' => config('video.timeout'),
                'ffmpeg.threads' => config('video.threads'),
            ]);

            $format = $ffprobe->format($url);
            $videoStream = $ffprobe->streams($url)->videos()->first();
            $duration = $format->get('duration');

            return [
                'duration' => is_numeric($duration) ? (int) ceil((float) $duration) : null,
                'width' => $this->integerValue($videoStream?->get('width')),
                'height' => $this->integerValue($videoStream?->get('height')),
                'codec' => $videoStream?->get('codec_name'),
                'bit_rate' => $this->integerValue($format->get('bit_rate')),
                'metadata_status' => 'ready',
            ];
        } catch (Throwable $exception) {
            Log::warning('FFprobe could not inspect an uploaded course video.', [
                'exception' => $exception,
            ]);

            return [
                'duration' => null,
                'width' => null,
                'height' => null,
                'codec' => null,
                'bit_rate' => null,
                'metadata_status' => 'ffprobe_unavailable',
            ];
        }
    }

    private function integerValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
