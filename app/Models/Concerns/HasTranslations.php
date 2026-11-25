<?php

namespace App\Models\Concerns;

/**
 * Translatable columns are JSON objects keyed by locale. A model lists them in
 * `$translatable`; the first ones in `$requiredTranslations` must be filled in
 * a locale for the record to be shown on that locale's page.
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(array_fill_keys($this->translatable, 'array'));
    }

    public function tr(string $attribute, ?string $locale = null): mixed
    {
        $value = $this->getAttribute($attribute) ?? [];

        return $value[$locale ?? app()->getLocale()] ?? null;
    }

    /** @return list<string> locales where a required field is empty */
    public function missingLocales(): array
    {
        $required = $this->requiredTranslations ?? $this->translatable;

        return array_values(array_filter(
            config('site.locales'),
            fn (string $locale) => collect($required)->contains(fn ($field) => blank($this->tr($field, $locale))),
        ));
    }

    public function isTranslatedIn(string $locale): bool
    {
        return ! in_array($locale, $this->missingLocales(), true);
    }
}
