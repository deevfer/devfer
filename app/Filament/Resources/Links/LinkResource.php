<?php

namespace App\Filament\Resources\Links;

use App\Filament\Resources\Links\Pages\CreateLink;
use App\Filament\Resources\Links\Pages\EditLink;
use App\Filament\Resources\Links\Pages\ListLinks;
use App\Filament\Support\Translatable;
use App\Models\Link;
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

class LinkResource extends Resource
{
    protected static ?string $model = Link::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Enlaces';

    protected static ?string $modelLabel = 'enlace';

    protected static string|\UnitEnum|null $navigationGroup = 'Sitio';

    protected static ?int $navigationSort = 4;

    public const ICONS = ['mail' => 'Correo', 'linkedin' => 'LinkedIn', 'github' => 'GitHub', 'gitlab' => 'GitLab', 'instagram' => 'Instagram', 'x' => 'X', 'whatsapp' => 'WhatsApp', 'link' => 'Enlace'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('url')->label('URL')->required()->maxLength(190)
                    ->rule('regex:/^(https?:\/\/|mailto:)/i')->helperText('https://… o mailto:…')->columnSpan(2),
                Select::make('icon')->label('Ícono')->options(self::ICONS)->required()->native(false)->default('link'),
                Toggle::make('is_active')->label('Visible')->default(true),
            ])->columnSpanFull(),
            Translatable::tabs(fn (string $locale) => [TextInput::make("label.{$locale}")->label('Texto visible')->maxLength(40)]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label.es')->label('Texto')->description(fn (Link $r) => $r->url),
                TextColumn::make('icon')->label('Ícono')->formatStateUsing(fn ($state) => self::ICONS[$state] ?? $state),
                Translatable::statusColumn(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLinks::route('/'),
            'create' => CreateLink::route('/create'),
            'edit' => EditLink::route('/{record}/edit'),
        ];
    }
}
