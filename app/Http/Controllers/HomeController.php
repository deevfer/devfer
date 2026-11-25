<?php

namespace App\Http\Controllers;

use App\Support\Messages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /** `/` goes to the visitor's last language, else their browser's, else English. */
    public function redirect(Request $request): RedirectResponse
    {
        $locales = config('site.locales');
        $saved = $request->cookie('locale');
        $locale = in_array($saved, $locales, true)
            ? $saved
            : ($request->getPreferredLanguage($locales) ?? config('site.default_locale'));

        return redirect("/{$locale}", 307);
    }

    public function show(string $locale): Response
    {
        cookie()->queue('locale', $locale, 60 * 24 * 365);

        $url = config('site.url');
        $email = config('site.email');
        $t = fn (string $key) => Messages::get($locale, $key);

        $person = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => 'Fernando Vásquez',
            'alternateName' => ['Luisfer', 'Devfer'],
            'jobTitle' => $locale === 'es'
                ? 'Desarrollador full-stack y diseñador UX/UI'
                : 'Full-stack developer and UX/UI designer',
            'description' => $t('meta.description'),
            'email' => "mailto:{$email}",
            'url' => "{$url}/{$locale}",
            'knowsAbout' => [
                'Web development', 'UX/UI design', 'PHP', 'Laravel', 'CodeIgniter', 'JavaScript',
                'Vue.js', 'React', 'Node.js', 'MySQL', 'Oracle', 'AWS', 'Figma',
            ],
        ];

        return Inertia::render('Home', [
            'locale' => $locale,
            'messages' => Messages::all($locale),
            'email' => $email,
        ])->withViewData([
            'meta' => [
                'title' => $t('meta.title'),
                'description' => $t('meta.description'),
                'url' => "{$url}/{$locale}",
                'alternates' => collect(config('site.locales'))
                    ->mapWithKeys(fn ($l) => [$l => "{$url}/{$l}"])
                    ->put('x-default', "{$url}/".config('site.default_locale'))
                    ->all(),
                'ogLocale' => $locale === 'es' ? 'es_ES' : 'en_US',
                'ogAlternate' => $locale === 'es' ? 'en_US' : 'es_ES',
                'image' => "{$url}/{$locale}/opengraph-image",
                'imageAlt' => $t('meta.ogAlt'),
                'person' => $person,
            ],
        ]);
    }
}
