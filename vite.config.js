import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/home.css',
                'resources/css/catalog.css',
                'resources/css/course-learn.css',
                'resources/css/course-show.css',
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/instructors/theme.css',
                'resources/css/filament/students/theme.css',
                'resources/js/app.js',
                'resources/js/course-show.js',
                'resources/js/video-player.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        hmr: process.env.VITE_HMR_HOST
            ? {
                  host: process.env.VITE_HMR_HOST,
                  port: process.env.VITE_HMR_PORT || 443,
                  protocol: process.env.VITE_HMR_PROTOCOL || 'https',
              }
            : true,
    },
});
