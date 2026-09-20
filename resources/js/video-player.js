import shaka from 'shaka-player/dist/shaka-player.ui.js';
import 'shaka-player/dist/controls.css';

shaka.polyfill.installAll();

const players = new WeakMap();

function livewireComponent(container) {
    const root = container.closest('[wire\\:id]');

    return root ? Livewire.find(root.getAttribute('wire:id')) : null;
}

function trackProgress(container, video) {
    let lastSavedPosition = Number(container.dataset.resumeAt || 0);
    let saving = false;

    const save = async (ended = false) => {
        const position = Math.floor(video.currentTime || 0);
        const duration = Math.floor(video.duration || 0);

        if (!duration || saving || (!ended && Math.abs(position - lastSavedPosition) < 10)) return;

        const component = livewireComponent(container);
        if (!component) return;

        saving = true;

        try {
            await component.call('saveVideoProgress', Number(container.dataset.videoId), position, duration, ended);
            lastSavedPosition = position;
        } finally {
            saving = false;
        }
    };

    const onTimeUpdate = () => save(false);
    const onEnded = () => save(true);
    const onPause = () => save(false);

    video.addEventListener('timeupdate', onTimeUpdate);
    video.addEventListener('ended', onEnded);
    video.addEventListener('pause', onPause);

    return {
        flush: () => save(false),
        destroy: () => {
            video.removeEventListener('timeupdate', onTimeUpdate);
            video.removeEventListener('ended', onEnded);
            video.removeEventListener('pause', onPause);
        },
    };
}

function showPlaybackError(container) {
    if (container.querySelector('[data-playback-error]')) return;

    const message = document.createElement('div');
    message.dataset.playbackError = 'true';
    message.className = 'course-video-playback-error';
    message.textContent = document.documentElement.lang === 'ar'
        ? 'تعذر تشغيل الفيديو. اضغط زر التشغيل مرة أخرى.'
        : 'The video could not start. Press play again.';
    container.append(message);
}

async function initialize(container) {
    if (container.dataset.initialized || !shaka.Player.isBrowserSupported()) return;

    container.dataset.initialized = 'true';
    const video = container.querySelector('video');
    const player = new shaka.Player();
    let refreshTimer;
    let disposed = false;
    const disposePlayback = () => { disposed = true; clearTimeout(refreshTimer); };

    try {
        await player.attach(video);
        const overlay = new shaka.ui.Overlay(player, container, video);
        players.set(container, { player, overlay });
        overlay.configure({
            bigButtons: ['play_pause_buffering'],
            controlPanelElements: ['play_pause', 'time_and_duration', 'spacer', 'mute', 'volume', 'overflow_menu', 'picture_in_picture', 'fullscreen'],
            overflowMenuButtons: ['quality', 'playback_rate', 'picture_in_picture'],
            seekBarColors: { base: 'rgba(255,255,255,.25)', buffered: 'rgba(255,255,255,.5)', played: '#f59e0b' },
            volumeBarColors: { base: 'rgba(255,255,255,.25)', level: '#f59e0b' },
        });
        player.addEventListener('error', (event) => {
            container.dataset.error = String(event.detail?.code || 'playback');
        });
        const loadStream = async (refresh = false) => {
            let source = container.dataset.manifest;
            let expiresIn;
            if (container.dataset.playback) {
                const response = await fetch(container.dataset.playback, { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!response.ok) throw new Error('Playback unavailable');
                const data = await response.json();
                source = data.hls;
                expiresIn = Number(data.expires_in);
                video.poster = data.thumbnail;
            }
            if (disposed) return;
            const position = refresh ? video.currentTime : undefined;
            const wasPlaying = !video.paused;
            await player.load(source, position);
            if (refresh && wasPlaying) await video.play();
            if (expiresIn && !disposed) {
                refreshTimer = setTimeout(async () => {
                    try { await loadStream(true); }
                    catch {
                        // Stop playback if authorization has expired or cannot be renewed.
                        await player.unload();
                        showPlaybackError(container);
                    }
                }, Math.max(10, expiresIn - 60) * 1000);
            }
        };
        await loadStream();

        const resumeAt = Number(container.dataset.resumeAt || 0);
        if (resumeAt > 0 && resumeAt < video.duration - 5) {
            video.currentTime = resumeAt;
        }

        const progressTracker = container.dataset.trackProgress === 'true'
            ? trackProgress(container, video)
            : null;
        players.set(container, { player, overlay, progressTracker, disposePlayback });

        if (container.dataset.autoplay === 'true') {
            try {
                await video.play();
            } catch {
                showPlaybackError(container);
            }
        }
    } catch (error) {
        disposePlayback();
        container.dataset.error = String(error?.code || 'load');
        showPlaybackError(container);
        delete container.dataset.initialized;
        await player.destroy();
    }
}

function initializeAll(root = document) {
    if (root instanceof Element && root.matches('[data-course-video-player]')) {
        initialize(root);
    }

    root.querySelectorAll?.('[data-course-video-player]').forEach(initialize);
}

const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        mutation.addedNodes.forEach((node) => {
            if (node instanceof Element) initializeAll(node);
        });

        mutation.removedNodes.forEach((node) => {
            if (!(node instanceof Element)) return;

            const containers = node.matches('[data-course-video-player]')
                ? [node]
                : node.querySelectorAll('[data-course-video-player]');

            containers.forEach((container) => {
                const instance = players.get(container);
                instance?.disposePlayback?.();
                instance?.progressTracker?.flush();
                instance?.progressTracker?.destroy();
                instance?.overlay.destroy();
                instance?.player.destroy();
                players.delete(container);
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', () => initializeAll());
document.addEventListener('livewire:navigated', () => initializeAll());
document.addEventListener('course-video-changed', () => requestAnimationFrame(initializeAll));
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => requestAnimationFrame(initializeAll));
});
observer.observe(document.documentElement, { childList: true, subtree: true });
initializeAll();
