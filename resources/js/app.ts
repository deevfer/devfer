import { createInertiaApp } from '@inertiajs/vue3';

// One entry for the browser and for SSR: @inertiajs/vite wraps it in a render
// server for `vite build --ssr` and serves it from the dev server in development.
createInertiaApp({
    pages: './pages',
    progress: false,
});
