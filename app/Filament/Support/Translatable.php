<?php

namespace App\Filament\Support;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** ES/EN tabs for translatable JSON fields, plus a status column and filter for pending translations. */
final class Translatable
{
    public const NAMES = ['es' => 'Español', 'en' => 'English'];

    /** @param Closure(string $locale): array $fields */
    public static function tabs(Closure $fields, string $label = 'Traducciones'): Tabs
    {
        return Tabs::make($label)
            ->tabs(collect(config('site.locales'))->map(
                fn (string $locale) => Tab::make(self::NAMES[$locale])->schema($fields($locale))
            )->all())
            ->columnSpanFull();
    }

    public static function statusColumn(): TextColumn
    {
        return TextColumn::make('translation_status')
            ->label('Traducción')
            ->state(function (Model $record) {
                $missing = $record->missingLocales();

                return $missing ? 'Falta '.implode(' y ', array_map('strtoupper', $missing)) : 'Completa';
            })
            ->badge()
            ->color(fn (string $state) => $state === 'Completa' ? 'success' : 'warning');
    }

    /** @param list<string> $fields JSON columns that must be filled in every locale */
    public static function pendingFilter(array $fields): Filter
    {
        return Filter::make('pending_translation')
            ->label('Con traducción pendiente')
            ->query(fn (Builder $query) => $query->where(function (Builder $q) use ($fields) {
                foreach ($fields as $field) {
                    foreach (config('site.locales') as $locale) {
                        $q->orWhereNull("{$field}->{$locale}")->orWhere("{$field}->{$locale}", '');
                    }
                }
            }));
    }
}
