<?php

namespace App\Http\Controllers;

use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Services\CourseVideoAccessService;
use App\Services\R2FileService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoStreamController extends Controller
{
    public function __construct(
        private readonly CourseVideoAccessService $access,
        private readonly R2FileService $storage,
    ) {}

    public function master(Request $request, Course $course, CourseVideo $video): Response
    {
        $this->access->authorizeViewing($request, $course, $video);
        abort_unless($video->status === VideoStatus::Ready && filled($video->hls_path), 404);

        $playlist = $this->storage->get($video->hls_path);
        $rewritten = preg_replace_callback(
            '/^(\d+)p\/index\.m3u8$/m',
            fn (array $match): string => route('course-videos.stream.variant', [$course, $video, $match[1]]),
            $playlist,
        );

        return $this->playlistResponse($rewritten ?? $playlist);
    }

    public function variant(Request $request, Course $course, CourseVideo $video, string $quality): Response
    {
        $this->access->authorizeViewing($request, $course, $video);
        abort_unless($video->status === VideoStatus::Ready && preg_match('/^(360|480|720|1080)$/D', $quality), 404);
        $base = sprintf('courses/%d/videos/%d/hls', $course->id, $video->id);
        abort_unless($video->hls_path === $base.'/master.m3u8', 404);

        $playlist = $this->storage->get($base.'/'.$quality.'p/index.m3u8');
        $rewritten = preg_replace_callback(
            '/^(segment_[0-9]{5}\.(?:ts|m4s))$/m',
            fn (array $match): string => $this->storage->hlsTemporaryUrl($base.'/'.$quality.'p/'.$match[1]),
            $playlist,
        );

        return $this->playlistResponse($rewritten ?? $playlist);
    }

    private function playlistResponse(string $playlist): Response
    {
        return response($playlist, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
