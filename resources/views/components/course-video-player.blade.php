@props(['video', 'course', 'poster' => null, 'watermark' => null, 'retryable' => false, 'autoplay' => false, 'resumeAt' => 0, 'trackProgress' => false])

@once
    @vite('resources/js/video-player.js')
@endonce

@if ($video->status === \App\Enums\VideoStatus::Ready && $video->hls_path)
    <div wire:ignore dir="ltr" class="course-video-player" data-course-video-player data-video-id="{{ $video->id }}" data-resume-at="{{ (int) $resumeAt }}" data-manifest="{{ route('course-videos.stream.master', [$course, $video]) }}" data-autoplay="{{ $autoplay ? 'true' : 'false' }}" data-track-progress="{{ $trackProgress ? 'true' : 'false' }}">
        <video dir="ltr" playsinline preload="metadata" @if($poster) poster="{{ $poster }}" @endif></video>
        @if ($watermark)
            <div class="course-video-watermark">{{ $watermark }}</div>
        @endif
    </div>
@elseif ($video->status === \App\Enums\VideoStatus::Failed)
    <div class="course-video-state course-video-state-error">
        <strong>Video processing failed.</strong>
        <span>{{ $video->processing_error ?: 'An administrator can review the logs and retry processing.' }}</span>
        @if ($retryable)
            <button type="button" wire:click="retryVideo({{ $video->id }})" wire:loading.attr="disabled">Retry processing</button>
        @endif
    </div>
@elseif ($video->url)
    @php
        $stageLabels = [
            'queued' => 'Waiting for the video worker',
            'downloading' => 'Downloading the source video',
            'probing' => 'Inspecting the video',
            'transcoding' => 'Creating streaming qualities',
            'uploading' => 'Uploading streaming files',
        ];
        $progress = min(100, max(0, (int) $video->processing_progress));
    @endphp
    <div class="course-video-state" wire:poll.5s>
        <span class="course-video-spinner"></span>
        <strong>{{ $stageLabels[$video->processing_stage] ?? 'Video is processing' }}</strong>
        <div class="course-video-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" style="width: min(24rem, 80%); height: .55rem; overflow: hidden; border-radius: 999px; background: #374151">
            <span style="display: block; width: {{ $progress }}%; height: 100%; border-radius: inherit; background: #f59e0b; transition: width .3s ease"></span>
        </div>
        <small>{{ $progress }}%</small>
    </div>
@else
    <div class="course-video-state course-video-state-error"><strong>Video has not been uploaded.</strong></div>
@endif

@once
    <style>
        .course-video-player{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;border-radius:1rem;background:#050505;direction:ltr;text-align:left;--shaka-primary-color:#f59e0b;--shaka-secondary-color:#fff}.course-video-player video{display:block;width:100%;height:100%;object-fit:contain;direction:ltr}.course-video-player .shaka-controls-container{font-family:inherit;direction:ltr;text-align:left}.course-video-player .shaka-overflow-menu,.course-video-player .shaka-settings-menu{border-radius:.8rem;background:rgba(17,24,39,.97);color:#fff;direction:ltr;text-align:left}.course-video-player .shaka-overflow-button-label,.course-video-player .shaka-current-selection-span{color:#fbbf24}.course-video-watermark{position:absolute;z-index:2;top:1rem;right:1rem;max-width:30%;pointer-events:none;color:#fff;font-weight:700;opacity:.35;text-shadow:0 1px 4px #000}.course-video-playback-error{position:absolute;z-index:4;right:1rem;bottom:4.5rem;left:1rem;padding:.7rem 1rem;border-radius:.7rem;background:rgba(127,29,29,.92);pointer-events:none;text-align:center;color:#fff;font-size:.82rem;font-weight:700}[dir=rtl] .course-video-playback-error{direction:rtl}.course-video-state{display:flex;width:100%;aspect-ratio:16/9;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;border-radius:1rem;background:#111827;padding:2rem;text-align:center;color:#e5e7eb}.course-video-state span{color:#9ca3af}.course-video-state-error strong{color:#fca5a5}.course-video-state button{border-radius:.65rem;background:#f59e0b;padding:.65rem 1rem;font-weight:800;color:#111827}.course-video-spinner{width:2rem;height:2rem;border:3px solid #ffffff2e;border-top-color:#f59e0b;border-radius:50%;animation:course-video-spin .8s linear infinite}@keyframes course-video-spin{to{transform:rotate(360deg)}}
    </style>
@endonce
