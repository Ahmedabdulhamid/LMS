import shaka from 'shaka-player/dist/shaka-player.ui.js';
import 'shaka-player/dist/controls.css';

shaka.polyfill.installAll();

const players = new WeakMap();

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

    try {
        await player.attach(video);
        const overlay = new shaka.ui.Overlay(player, container, video);
        players.set(container, { player, overlay });
        overlay.configure({
            addBigPlayButton: true,
            controlPanelElements: ['play_pause', 'time_and_duration', 'spacer', 'mute', 'volume', 'overflow_menu', 'picture_in_picture', 'fullscreen'],
            overflowMenuButtons: ['quality', 'playback_rate', 'picture_in_picture'],
            seekBarColors: { base: 'rgba(255,255,255,.25)', buffered: 'rgba(255,255,255,.5)', played: '#f59e0b' },
            volumeBarColors: { base: 'rgba(255,255,255,.25)', level: '#f59e0b' },
        });
        player.addEventListener('error', (event) => {
            container.dataset.error = String(event.detail?.code || 'playback');
        });
        await player.load(container.dataset.manifest);

        if (container.dataset.autoplay === 'true') {
            try {
                await video.play();
            } catch {
                showPlaybackError(container);
            }
        }
    } catch (error) {
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
