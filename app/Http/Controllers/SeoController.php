<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SiteText;
use App\Support\OgImage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $sitemap = config('site.url').'/sitemap.xml';

        return response("User-Agent: *\nAllow: /\nDisallow: /admin\nDisallow: /livewire\n\nSitemap: {$sitemap}\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $url = config('site.url');
        $locales = config('site.locales');
        $default = config('site.default_locale');
        $now = now()->toAtomString();

        $links = collect($locales)
            ->map(fn ($l) => "<xhtml:link rel=\"alternate\" hreflang=\"{$l}\" href=\"{$url}/{$l}\" />")
            ->push("<xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$url}/\" />")
            ->implode("\n");

        $entries = collect($locales)->map(fn ($l) => implode("\n", [
            '<url>',
            "<loc>{$url}/{$l}</loc>",
            $links,
            "<lastmod>{$now}</lastmod>",
            '<changefreq>monthly</changefreq>',
            '<priority>'.($l === $default ? '1' : '0.9').'</priority>',
            '</url>',
        ]))->implode("\n");

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n"
            .$entries."\n</urlset>\n";

        return response($xml)->header('Content-Type', 'application/xml');
    }

    /** Rendered once per copy change, then served from storage. */
    public function ogImage(string $locale): Response
    {
        $t = SiteText::forLocale($locale);
        $name = Setting::get('profile.name', 'Fernando Vásquez');
        $statement = Str::ucfirst("{$t['hero.expert']} {$t['hero.skill_a']} {$t['hero.and']} {$t['hero.skill_b']}.");
        $caption = "{$t['hero.role']} · fernandovasquez.tech";
        $photo = Setting::get('profile.photo');
        $photoPath = $photo ? Storage::disk('public')->path($photo) : null;

        $key = md5(implode('|', [$name, $statement, $caption, $photo, filemtime(app_path('Support/OgImage.php'))]));
        $path = storage_path("app/og/{$locale}-{$key}.png");

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::delete(File::glob(storage_path("app/og/{$locale}-*.png")));
            $png = (new OgImage(resource_path('fonts/Geist-SemiBold.ttf'), resource_path('fonts/Geist-Regular.ttf')))
                ->render($name, $statement, $caption, $photoPath);
            File::put($path, $png);
        }

        return response(File::get($path))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
