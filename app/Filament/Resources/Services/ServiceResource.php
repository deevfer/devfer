<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Support\Translatable;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'servicio';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 2;

    /** Icons available on the site (Lucide names). */
    public const ICONS = [
        'code-xml' => 'Código', 'pen-tool' => 'Pluma de diseño', 'smartphone' => 'Teléfono', 'monitor-smartphone' => 'Pantallas',
        'server' => 'Servidor', 'cloud' => 'Nube', 'workflow' => 'Flujo', 'bot' => 'Bot', 'sparkles' => 'Destellos',
        'shield-check' => 'Escudo', 'layout-template' => 'Plantilla', 'palette' => 'Paleta', 'database' => 'Base de datos',
        'gauge' => 'Rendimiento', 'wrench' => 'Mantenimiento', 'plug' => 'Integración',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('slug')->label('Identificador')->required()->alphaDash()->maxLength(40)
                    ->unique(ignoreRecord: true)->helperText('Se usa en el formulario de contacto. Evita cambiarlo.'),
                Select::make('icon')->label('Ícono')->options(self::ICONS)->required()->native(false)->searchable(),
                Toggle::make('is_active')->label('Visible en el sitio')->default(true)->inline(false),
            ])->columnSpanFull(),
            Translatable::tabs(fn (string $locale) => [
                TextInput::make("title.{$locale}")->label('Título')->maxLength(80),
                Textarea::make("summary.{$locale}")->label('Descripción')->rows(3)->maxLength(400),
                TagsInput::make("items.{$locale}")->label('Qué incluye')->reorderable()
                    ->placeholder('Escribe y pulsa Enter')->helperText('Entre 3 y 5 puntos funcionan mejor.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title.es')->label('Servicio')->description(fn (Service $r) => $r->tr('title', 'en'))->searchable(),
                TextColumn::make('icon')->label('Ícono')->formatStateUsing(fn ($state) => self::ICONS[$state] ?? $state),
                Translatable::statusColumn(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([Translatable::pendingFilter(['title', 'summary'])])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
