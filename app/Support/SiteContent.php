<?php

namespace App\Support;

use App\Models\Link;
use App\Models\NavItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SiteText;
use App\Models\Technology;
use Illuminate\Support\Facades\Storage;

/**
 * Everything the public page needs for one locale. Items missing a required
 * translation in that locale are left out, so a page never mixes languages.
 */
final class SiteContent
{
    /** Localized anchors for each section. */
    public const ANCHORS = [
        'es' => ['top' => 'inicio', 'profile' => 'perfil', 'services' => 'servicios', 'technologies' => 'tecnologias', 'cv' => 'cv', 'contact' => 'contacto'],
        'en' => ['top' => 'top', 'profile' => 'profile', 'services' => 'services', 'technologies' => 'technologies', 'cv' => 'cv', 'contact' => 'contact'],
    ];

    /** @return array<string, mixed> */
    public static function props(string $locale): array
    {
        $photo = Setting::get('profile.photo');
        $memoji = Setting::get('profile.memoji');

        return [
            'locale' => $locale,
            'anchors' => self::ANCHORS,
            'texts' => SiteText::forLocale($locale),
            'profile' => [
                'name' => Setting::get('profile.name', 'Fernando Vásquez'),
                'photo' => $photo ? Storage::disk('public')->url($photo) : null,
                'memoji' => $memoji ? Storage::disk('public')->url($memoji) : null,
                'available' => (bool) Setting::get('profile.available', false),
                'email' => Setting::get('contact.email', config('site.email')),
            ],
            'nav' => NavItem::query()->visible()->get()
                ->filter(fn (NavItem $i) => $i->isTranslatedIn($locale))
                ->map(fn (NavItem $i) => ['label' => $i->tr('label', $locale), 'target' => $i->target])
                ->values(),
            'services' => Service::query()->visible()->get()
                ->filter(fn (Service $s) => $s->isTranslatedIn($locale))
                ->map(fn (Service $s) => [
                    'id' => $s->id,
                    'slug' => $s->slug,
                    'icon' => $s->icon,
                    'title' => $s->tr('title', $locale),
                    'summary' => $s->tr('summary', $locale),
                    'items' => array_values(array_filter((array) $s->tr('items', $locale))),
                ])->values(),
            'technologies' => collect(Technology::CATEGORIES)->map(fn (string $category) => [
                'category' => $category,
                'items' => Technology::query()->visible()->where('category', $category)->get()
                    ->map(fn (Technology $t) => [
                        'name' => $t->name,
                        'logo' => $t->logoUrl(),
                        'color' => $t->brand_color,
                        'url' => $t->url,
                    ])->values(),
            ])->filter(fn ($group) => $group['items']->isNotEmpty())->values(),
            'links' => Link::query()->visible()->get()
                ->filter(fn (Link $l) => $l->isTranslatedIn($locale))
                ->map(fn (Link $l) => ['label' => $l->tr('label', $locale), 'url' => $l->url, 'icon' => $l->icon])
                ->values(),
            'cv' => collect(config('site.locales'))->map(fn (string $l) => CvFiles::describe($l))->filter()->values(),
            'recaptcha' => Recaptcha::publicConfig(),
        ];
    }
}
