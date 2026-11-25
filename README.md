# fernandovasquez.tech

Professional site of Fernando Vásquez (Luisfer), in Spanish and English.
Laravel 13 + Inertia v3 + Vue 3 (TypeScript, server-side rendered), Tailwind CSS v4,
and a Filament 5 panel at `/admin` to manage content, leads and integrations.

## Set up locally

```sh
composer install && npm install
cp .env.example .env && php artisan key:generate   # first time only
php artisan migrate --seed                          # content, CVs, logos and the panel user
php artisan storage:link
php artisan dev                                     # Laravel + Vite (with SSR) on http://127.0.0.1:8000
```

The seeder prints the panel login. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`
beforehand to choose them. Running the seeder again only adds what is missing; it never
overwrites what was edited in the panel.

Checks: `php artisan test` (backend) and `npm run lint` (Vue/TypeScript types).

## What the panel manages

| Section | Module |
| --- | --- |
| Contacto | Leads (search, filters, status, internal notes, retry failed emails), email templates (ES/EN, preview, test send), reCAPTCHA and SMTP (with a test email) |
| Contenido | Experience and education, services (icon, ES/EN copy, order, visibility), technologies (logo, category, order, visibility) |
| Sitio | Profile photo, public email, both CV PDFs, SEO titles/descriptions per language, OG image, every public text (ES/EN), navigation and links |

Pending translations are flagged in each table and summed up on the dashboard. A
service, experience or menu item missing a required translation is hidden on that
language's page, so each page stays in one language.

Secrets (SMTP password, reCAPTCHA secret) are stored encrypted with `APP_KEY` and are
never shown again or sent to the browser.

## URLs

- `/es`, `/en` — the site. `/` redirects to the visitor's last language, then the browser's, then Spanish.
- `/assets/cv/cv_fvasquez_es.pdf`, `/assets/cv/cv_fvasquez_en.pdf` — the CVs, same URLs as the previous site.
- `/servicios`, `/contacto` — 301 to `/es#servicios` and `/es#contacto`.
- `/sitemap.xml`, `/robots.txt`, `/{locale}/opengraph-image`.
- `/admin` — panel (`noindex`, disallowed in robots.txt).

## Production

```sh
npm run build                  # client bundle + SSR bundle (bootstrap/ssr)
php artisan inertia:start-ssr  # keep running (Supervisor/systemd); Laravel calls it on port 13714
php artisan optimize
```

Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and `SITE_URL`. Configure SMTP
and reCAPTCHA from the panel (or leave panel SMTP off to use `MAIL_*` from `.env`).

## Where things live

| What | Where |
| --- | --- |
| Page and sections | `resources/js/pages/Home.vue`, `resources/js/components/` |
| Design tokens | `resources/css/app.css` — see `docs/design-system.md` |
| Motion | `docs/motion.md` |
| Initial content (ES/EN) | `database/seeders/ContentSeeder.php` |
| Contact flow | `app/Http/Controllers/ContactController.php`, `app/Support/LeadMailer.php`, `app/Support/Recaptcha.php`, `app/Support/SiteMailer.php` |
| Panel | `app/Filament/` |
