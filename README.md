# fernandovasquez.tech

Personal site of Fernando Vásquez. Laravel 13 + Inertia v3 + Vue 3 (TypeScript), server-side rendered, Tailwind CSS v4, GSAP and Lenis.

## Develop

```sh
composer install && npm install
cp .env.example .env && php artisan key:generate   # first time only
php artisan dev                                     # Laravel + Vite (SSR included) on http://localhost:8000
```

`npm run lint` type-checks the Vue and TypeScript code.

## Production

```sh
npm run build                  # client bundle + SSR bundle (bootstrap/ssr)
php artisan inertia:start-ssr  # keep running (Supervisor/systemd), Laravel calls it on port 13714
```

Set `APP_ENV=production`, `APP_DEBUG=false` and `SITE_URL` in `.env`.

## Where things live

| What | Where |
|---|---|
| Copy (EN/ES) | `resources/messages/en.json`, `resources/messages/es.json` |
| Evolution stages | `resources/js/content/evolution.ts` |
| Page and sections | `resources/js/pages/Home.vue`, `resources/js/components/` |
| Design tokens | `resources/css/app.css` |
| Routes, meta, SEO | `routes/web.php`, `resources/views/app.blade.php`, `app/Http/Controllers/` |
| OG image | `app/Support/OgImage.php` (fonts in `resources/fonts/`, OFL) |
| Design system | `docs/design-system.md` |
