<?php

$availableRenditions = [
    1080 => ['width' => 1920, 'video_bitrate' => 5000, 'maxrate' => 5350, 'bufsize' => 7500],
    720 => ['width' => 1280, 'video_bitrate' => 2800, 'maxrate' => 2996, 'bufsize' => 4200],
    480 => ['width' => 854, 'video_bitrate' => 1400, 'maxrate' => 1498, 'bufsize' => 2100],
    360 => ['width' => 640, 'video_bitrate' => 800, 'maxrate' => 856, 'bufsize' => 1200],
];

$enabledRenditions = array_filter(array_map(
    'intval',
    explode(',', (string) env('VIDEO_RENDITIONS', '1080,720,480,360')),
));

return [
    'ffmpeg' => env('FFMPEG_PATH', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_PATH', 'ffprobe'),
    'timeout' => (int) env('FFMPEG_TIMEOUT', 7200),
    'threads' => (int) env('FFMPEG_THREADS', 4),
    'preset' => env('FFMPEG_PRESET', 'veryfast'),
    'video_encoder' => env('FFMPEG_VIDEO_ENCODER', 'libx264'),
    'queue' => env('VIDEO_PROCESSING_QUEUE', 'video-processing'),
    'segment_seconds' => (int) env('HLS_SEGMENT_SECONDS', 6),
    'signed_url_minutes' => (int) env('HLS_SIGNED_URL_MINUTES', 10),
    'renditions' => array_intersect_key($availableRenditions, array_flip($enabledRenditions)),
    'audio_bitrate' => (int) env('HLS_AUDIO_BITRATE', 128),
    'upload_concurrency' => (int) env('HLS_UPLOAD_CONCURRENCY', 12),
];
