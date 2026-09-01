<?php

namespace App\Services;

use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class HlsVideoTranscoder
{
    public function qualitiesFor(int $sourceHeight): array
    {
        return array_values(array_filter(
            array_map('intval', array_keys(config('video.renditions', []))),
            fn (int $height): bool => $height <= $sourceHeight,
        ));
    }

    public function transcode(
        string $input,
        string $outputDirectory,
        int $sourceWidth,
        int $sourceHeight,
        int $duration = 0,
        ?callable $onProgress = null,
    ): array
    {
        $qualities = $this->qualitiesFor($sourceHeight);

        if ($qualities === []) {
            throw new RuntimeException('The source video is smaller than the lowest configured rendition.');
        }

        File::ensureDirectoryExists($outputDirectory);
        $variants = [];
        $command = [
            (string) config('video.ffmpeg', 'ffmpeg'),
            '-hide_banner', '-y', '-progress', 'pipe:2', '-nostats', '-i', $input,
        ];
        $filterInputs = [];
        $filterScales = [];

        foreach (array_values($qualities) as $index => $height) {
            $settings = config("video.renditions.$height");
            $directory = $outputDirectory.DIRECTORY_SEPARATOR.$height.'p';
            File::ensureDirectoryExists($directory);
            $segmentSeconds = max(2, (int) config('video.segment_seconds', 6));
            $gop = $segmentSeconds * 30;

            $filterInputs[] = '[v'.$index.']';
            $filterScales[] = '[v'.$index.']scale=-2:'.$height.'[v'.$index.'out]';
            $command = array_merge($command, [
                '-map', '[v'.$index.'out]', '-map', '0:a:0?',
                '-c:v', (string) config('video.video_encoder', 'libx264'),
                '-preset', (string) config('video.preset', 'veryfast'), '-profile:v', 'main', '-pix_fmt', 'yuv420p',
                '-threads', (string) max(0, (int) config('video.threads', 0)),
                '-b:v', $settings['video_bitrate'].'k', '-maxrate', $settings['maxrate'].'k',
                '-bufsize', $settings['bufsize'].'k', '-g', (string) $gop, '-keyint_min', (string) $gop,
                '-sc_threshold', '0', '-force_key_frames', "expr:gte(t,n_forced*$segmentSeconds)",
                '-c:a', 'aac', '-b:a', config('video.audio_bitrate', 128).'k', '-ar', '48000', '-ac', '2',
                '-f', 'hls', '-hls_time', (string) $segmentSeconds, '-hls_playlist_type', 'vod',
                '-hls_flags', 'independent_segments', '-hls_segment_filename', $directory.DIRECTORY_SEPARATOR.'segment_%05d.ts',
                $directory.DIRECTORY_SEPARATOR.'index.m3u8',
            ]);

            $variants[] = [
                'height' => $height,
                'width' => max(2, (int) (round(($sourceWidth * $height / $sourceHeight) / 2) * 2)),
                'bandwidth' => ((int) $settings['maxrate'] + (int) config('video.audio_bitrate', 128)) * 1000,
            ];
        }

        array_splice($command, 8, 0, [
            '-filter_complex',
            '[0:v:0]split='.count($qualities).implode('', $filterInputs).';'.implode(';', $filterScales),
        ]);

        $progressBuffer = '';
        $lastProgress = -1;
        $result = Process::timeout((int) config('video.timeout', 21600))->run(
            $command,
            function (string $type, string $output) use ($duration, $onProgress, &$progressBuffer, &$lastProgress): void {
                if ($onProgress === null || $type !== 'err' || $duration <= 0) {
                    return;
                }

                $progressBuffer .= $output;
                $lines = preg_split('/\r?\n/', $progressBuffer);
                $progressBuffer = array_pop($lines) ?? '';

                foreach ($lines as $line) {
                    if (! str_starts_with($line, 'out_time_us=')) {
                        continue;
                    }

                    $microseconds = (int) substr($line, strlen('out_time_us='));
                    $progress = min(100, max(0, (int) floor($microseconds / ($duration * 10000))));
                    if ($progress !== $lastProgress) {
                        $lastProgress = $progress;
                        $onProgress($progress);
                    }
                }
            },
        );

        if ($result->failed()) {
            throw new ProcessFailedException($result);
        }

        $master = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-INDEPENDENT-SEGMENTS\n";
        foreach ($variants as $variant) {
            $master .= sprintf(
                "#EXT-X-STREAM-INF:BANDWIDTH=%d,RESOLUTION=%dx%d\n%dp/index.m3u8\n",
                $variant['bandwidth'], $variant['width'], $variant['height'], $variant['height'],
            );
        }
        File::put($outputDirectory.DIRECTORY_SEPARATOR.'master.m3u8', $master);

        return $qualities;
    }
}
