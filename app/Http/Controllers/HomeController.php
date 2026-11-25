<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Setting;
use App\Models\Technology;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /** `/` goes to the visitor's last language, else their browser's, else Spanish. */
    public function redirect(Request $request): RedirectResponse
    {
        $locales = config('site.locales');
        $saved = $request->cookie('locale');
        $locale = in_array($saved, $locales, true)
            ? $saved
            : ($request->getPreferredLanguage($locales) ?? config('site.default_locale'));

        return redirect("/{$locale}", 302);
    }

    public function show(string $locale): Response
    {
        app()->setLocale($locale);
        cookie()->queue('locale', $locale, 60 * 24 * 365);

        $url = config('site.url');
        $props = SiteContent::props($locale);
        $title = Setting::get("seo.title.{$locale}", $props['profile']['name']);
        $description = Setting::get("seo.description.{$locale}", '');
        $ogImage = Setting::get('seo.og_image');

        return Inertia::render('Home', $props)->withViewData([
            'meta' => [
                'title' => $title,
                'description' => $description,
                'url' => "{$url}/{$locale}",
                'alternates' => collect(config('site.locales'))
                    ->mapWithKeys(fn ($l) => [$l => "{$url}/{$l}"])
                    ->put('x-default', "{$url}/")
                    ->all(),
                'ogLocale' => $locale === 'es' ? 'es_GT' : 'en_US',
                'ogAlternate' => $locale === 'es' ? 'en_US' : 'es_GT',
                'image' => $ogImage ? $url.Storage::disk('public')->url($ogImage) : "{$url}/{$locale}/opengraph-image",
                'imageAlt' => $title,
                'person' => $this->person($locale, $props, $description),
            ],
        ]);
    }

    /** schema.org Person built only from verified facts (CV and the panel). */
    private function person(string $locale, array $props, string $description): array
    {
        $url = config('site.url');
        $photo = $props['profile']['photo'];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $props['profile']['name'],
            'alternateName' => ['Luisfer', 'Devfer'],
            'jobTitle' => $locale === 'es' ? 'Desarrollador full-stack y diseñador UX/UI' : 'Full-stack developer and UX/UI designer',
            'description' => $description,
            'url' => "{$url}/{$locale}",
            'email' => 'mailto:'.$props['profile']['email'],
            'image' => $photo ? (str_starts_with($photo, 'http') ? $photo : $url.$photo) : null,
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Guatemala City', 'addressCountry' => 'GT'],
            'alumniOf' => ['@type' => 'CollegeOrUniversity', 'name' => 'Universidad Mariano Gálvez de Guatemala'],
            'knowsAbout' => [
                $locale === 'es' ? 'Desarrollo web full-stack' : 'Full-stack web development',
                $locale === 'es' ? 'Diseño UX/UI' : 'UX/UI design',
                ...Technology::query()->visible()->pluck('name')->all(),
            ],
            'sameAs' => Link::query()->visible()->where('url', 'like', 'http%')->pluck('url')->all() ?: null,
        ]);
    }
}
