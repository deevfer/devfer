<?php

namespace App\Filament\Resources\NavItems;

use App\Filament\Resources\NavItems\Pages\CreateNavItem;
use App\Filament\Resources\NavItems\Pages\EditNavItem;
use App\Filament\Resources\NavItems\Pages\ListNavItems;
use App\Filament\Support\Translatable;
use App\Models\NavItem;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NavItemResource extends Resource
{
    protected static ?string $model = NavItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static ?string $navigationLabel = 'Navegación';

    protected static ?string $modelLabel = 'enlace del menú';

    protected static ?string $pluralModelLabel = 'navegación';

    protected static string|\UnitEnum|null $navigationGroup = 'Sitio';

    protected static ?int $navigationSort = 3;

    public const SECTIONS = ['profile' => 'Perfil y trayectoria', 'services' => 'Servicios', 'technologies' => 'Tecnologías', 'cv' => 'CV', 'contact' => 'Contacto'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('target')->label('Destino')->options(self::SECTIONS)->required()->native(false)
                    ->helperText('La sección se enlaza con su ancla en cada idioma (#servicios, #services…).'),
                Toggle::make('is_active')->label('Visible')->default(true)->inline(false),
            ])->columnSpanFull(),
            Translatable::tabs(fn (string $locale) => [TextInput::make("label.{$locale}")->label('Texto del enlace')->maxLength(40)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label.es')->label('Español'),
                TextColumn::make('label.en')->label('English'),
                TextColumn::make('target')->label('Destino')->formatStateUsing(fn ($state) => self::SECTIONS[$state] ?? $state),
                Translatable::statusColumn(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavItems::route('/'),
            'create' => CreateNavItem::route('/create'),
            'edit' => EditNavItem::route('/{record}/edit'),
        ];
    }
}
