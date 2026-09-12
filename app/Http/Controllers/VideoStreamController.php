<?php

namespace App\Http\Controllers;

use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Services\CourseVideoAccessService;
use App\Services\MuxVideoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoStreamController extends Controller
{
    public function __construct(
        private readonly CourseVideoAccessService $access,
    ) {}

    public function playback(Request $request, Course $course, CourseVideo $video, MuxVideoService $mux): Response
    {
        $this->access->authorizeViewing($request, $course, $video);
        abort_unless($video->status === VideoStatus::Ready && $video->mux_playback_id, 404);

        return response()->json([
            'hls' => $mux->getPlaybackUrl($video->mux_playback_id),
            'thumbnail' => $mux->getThumbnailUrl($video->mux_playback_id),
            'expires_in' => $mux->tokenTtl(),
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function master(Request $request, Course $course, CourseVideo $video, MuxVideoService $mux): Response
    {
        $this->access->authorizeViewing($request, $course, $video);
        abort_unless($video->status === VideoStatus::Ready && $video->mux_playback_id, 404);

        return redirect()->away($mux->getPlaybackUrl($video->mux_playback_id))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
