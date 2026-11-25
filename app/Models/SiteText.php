<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Every piece of public copy that is not a list item: headings, intros, labels, button text. */
class SiteText extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'group', 'label', 'value', 'multiline', 'sort'];

    protected array $translatable = ['value'];

    protected $casts = ['multiline' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_texts'));
        static::deleted(fn () => Cache::forget('site_texts'));
    }

    /**
     * All texts for a locale. A missing translation falls back to the other
     * language so the layout never breaks; the panel flags it as pending.
     *
     * @return array<string, string>
     */
    public static function forLocale(string $locale): array
    {
        $all = Cache::rememberForever('site_texts', fn () => static::query()->get(['key', 'value'])
            ->mapWithKeys(fn (SiteText $t) => [$t->key => $t->value])->all());

        $other = collect(config('site.locales'))->first(fn ($l) => $l !== $locale);

        return collect($all)->map(fn ($v) => filled($v[$locale] ?? null) ? $v[$locale] : ($v[$other] ?? ''))->all();
    }
}
