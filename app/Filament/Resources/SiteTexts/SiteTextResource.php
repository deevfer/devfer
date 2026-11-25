<?php

namespace App\Filament\Resources\SiteTexts;

use App\Filament\Resources\SiteTexts\Pages\EditSiteText;
use App\Filament\Resources\SiteTexts\Pages\ListSiteTexts;
use App\Filament\Support\Translatable;
use App\Models\SiteText;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class SiteTextResource extends Resource
{
    protected static ?string $model = SiteText::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $navigationLabel = 'Textos del sitio';

    protected static ?string $modelLabel = 'texto';

    protected static ?string $pluralModelLabel = 'textos del sitio';

    protected static string|\UnitEnum|null $navigationGroup = 'Sitio';

    protected static ?int $navigationSort = 2;

    public const GROUPS = [
        'navigation' => 'Navegación', 'hero' => 'Portada', 'profile' => 'Perfil y trayectoria', 'services' => 'Servicios',
        'technologies' => 'Tecnologías', 'cv' => 'CV', 'contact' => 'Contacto y formulario', 'footer' => 'Pie de página',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Text::make(fn (?SiteText $record) => $record ? "{$record->label} · clave {$record->key}" : '')->columnSpanFull(),
            Translatable::tabs(fn (string $locale) => [
                Textarea::make("value.{$locale}")->label(Translatable::NAMES[$locale])->rows(4)->maxLength(2000)
                    ->visible(fn (?SiteText $record) => (bool) $record?->multiline),
                TextInput::make("value.{$locale}")->label(Translatable::NAMES[$locale])->maxLength(300)
                    ->visible(fn (?SiteText $record) => ! $record?->multiline),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->defaultGroup(Group::make('group')->label('Sección')->getTitleFromRecordUsing(fn (SiteText $r) => self::GROUPS[$r->group] ?? $r->group)->collapsible())
            ->paginated([50, 100, 'all'])
            ->columns([
                TextColumn::make('label')->label('Texto')->searchable()->description(fn (SiteText $r) => $r->key),
                TextColumn::make('value.es')->label('Español')->limit(60)->wrap()->searchable(),
                TextColumn::make('value.en')->label('English')->limit(60)->wrap()->searchable(),
                Translatable::statusColumn(),
            ])
            ->filters([
                SelectFilter::make('group')->label('Sección')->options(self::GROUPS),
                Translatable::pendingFilter(['value']),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteTexts::route('/'),
            'edit' => EditSiteText::route('/{record}/edit'),
        ];
    }
}
