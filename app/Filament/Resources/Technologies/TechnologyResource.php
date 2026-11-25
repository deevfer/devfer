<?php

namespace App\Filament\Resources\Technologies;

use App\Filament\Resources\Technologies\Pages\CreateTechnology;
use App\Filament\Resources\Technologies\Pages\EditTechnology;
use App\Filament\Resources\Technologies\Pages\ListTechnologies;
use App\Models\Technology;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TechnologyResource extends Resource
{
    protected static ?string $model = Technology::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Tecnologías';

    protected static ?string $modelLabel = 'tecnología';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public const CATEGORIES = [
        'design' => 'Diseño', 'frontend' => 'Frontend', 'backend' => 'Backend', 'data' => 'Datos',
        'mobile' => 'Aplicaciones móviles', 'infrastructure' => 'Infraestructura y flujo de trabajo', 'automation' => 'Automatización e IA',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('name')->label('Nombre visible')->required()->maxLength(60),
                Select::make('category')->label('Categoría')->options(self::CATEGORIES)->required()->native(false),
                Toggle::make('is_active')->label('Visible')->default(true)->inline(false),
                FileUpload::make('logo_path')->label('Logo')
                    ->disk('public')->directory('logos')
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])->maxSize(512)
                    ->helperText('Logo oficial a color, cuadrado, SVG o PNG con fondo transparente. Se muestra a 30 × 30 px y el nombre aparece al pasar el cursor.')
                    ->columnSpan(2),
                ColorPicker::make('brand_color')->label('Color de la marca')->helperText('Referencia; no se muestra en el sitio.'),
                TextInput::make('url')->label('Sitio oficial')->url()->maxLength(190)->columnSpan(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('logo_path')->label('Logo')->disk('public')->imageHeight(24),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('category')->label('Categoría')->badge()->formatStateUsing(fn ($state) => self::CATEGORIES[$state] ?? $state),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                SelectFilter::make('category')->label('Categoría')->options(self::CATEGORIES),
                TernaryFilter::make('logo_path')->label('Logo')->nullable()->trueLabel('Con logo')->falseLabel('Sin logo (símbolo genérico)'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnologies::route('/'),
            'create' => CreateTechnology::route('/create'),
            'edit' => EditTechnology::route('/{record}/edit'),
        ];
    }
}
