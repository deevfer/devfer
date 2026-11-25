import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const subsets = ['latin', 'latin-ext'];

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                google('Geist', { variable: '--font-geist', weights: [400, 500, 600, 700], subsets }),
                google('Geist Mono', { variable: '--font-geist-mono', weights: [400, 500], subsets }),
            ],
        }),
        inertia(),
        vue({
            template: { transformAssetUrls: { base: null, includeAbsolute: false } },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: { '@': '/resources/js' },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
