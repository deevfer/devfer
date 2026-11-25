<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use App\Models\Service;
use App\Support\LeadMailer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'lead';

    protected static string|\UnitEnum|null $navigationGroup = 'Contacto';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public const STATUSES = [
        'new' => 'Nuevo', 'contacted' => 'Contactado', 'in_progress' => 'En conversación',
        'won' => 'Ganado', 'closed' => 'Cerrado', 'spam' => 'Spam',
    ];

    public const STATUS_COLORS = ['new' => 'info', 'contacted' => 'warning', 'in_progress' => 'warning', 'won' => 'success', 'closed' => 'gray', 'spam' => 'danger'];

    public const MAIL_STATUSES = ['pending' => 'Pendiente', 'sent' => 'Enviado', 'failed' => 'Falló'];

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::query()->where('status', 'new')->count();

        return $count ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        $mailEntry = fn (string $kind, string $label) => TextEntry::make("{$kind}_status")->label($label)
            ->formatStateUsing(fn ($state) => self::MAIL_STATUSES[$state] ?? $state)
            ->badge()->color(fn ($state) => ['sent' => 'success', 'failed' => 'danger'][$state] ?? 'gray')
            ->helperText(fn (Lead $record) => $record->{"{$kind}_status"} === 'failed'
                ? $record->{"{$kind}_error"}
                : ($record->{"{$kind}_sent_at"}?->timezone('America/Guatemala')->format('d/m/Y H:i')));

        return $schema->components([
            Section::make('Mensaje')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('email')->label('Correo')->copyable()->url(fn (Lead $r) => "mailto:{$r->email}"),
                    TextEntry::make('service_label')->label('Servicio'),
                    TextEntry::make('company')->label('Empresa')->placeholder('—'),
                    TextEntry::make('phone')->label('Teléfono')->placeholder('—'),
                    TextEntry::make('created_at')->label('Recibido')->dateTime('d/m/Y H:i', 'America/Guatemala')
                        ->helperText(fn (Lead $r) => 'Idioma: '.strtoupper($r->locale).($r->recaptcha_score !== null ? " · reCAPTCHA {$r->recaptcha_score}" : '')),
                ]),
                TextEntry::make('message')->label('Mensaje')->columnSpanFull()->extraAttributes(['style' => 'white-space: pre-line']),
            ])->columnSpanFull(),
            Section::make('Seguimiento')->schema([
                Select::make('status')->label('Estado')->options(self::STATUSES)->required()->native(false),
                Textarea::make('notes')->label('Notas internas')->rows(5)->maxLength(5000)
                    ->helperText('Solo visibles en el panel.'),
            ]),
            Section::make('Correos')->schema([
                $mailEntry('ack', 'Acuse al visitante'),
                $mailEntry('notify', 'Notificación interna'),
            ]),
        ])->columns(2);
    }

    public static function retryAction(): Action
    {
        return Action::make('retryEmails')
            ->label('Reintentar envíos')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->visible(fn (Lead $record) => $record->hasFailedEmails())
            ->requiresConfirmation()
            ->modalDescription('Solo se reenvían los correos que fallaron; los que ya se enviaron no se repiten.')
            ->action(function (Lead $record) {
                LeadMailer::deliver($record);
                $record->refresh();
                $record->hasFailedEmails()
                    ? Notification::make()->title('Algún envío volvió a fallar')->body('Revisa la configuración SMTP en «reCAPTCHA y correo».')->danger()->send()
                    : Notification::make()->title('Correos enviados')->success()->send();
            });
    }

    public static function table(Table $table): Table
    {
        $mailColumn = fn (string $kind, string $label) => TextColumn::make("{$kind}_status")->label($label)
            ->badge()->formatStateUsing(fn ($state) => self::MAIL_STATUSES[$state] ?? $state)
            ->color(fn ($state) => ['sent' => 'success', 'failed' => 'danger'][$state] ?? 'gray')
            ->toggleable();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i', 'America/Guatemala')->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable()->description(fn (Lead $r) => $r->email),
                TextColumn::make('email')->searchable()->hidden(),
                TextColumn::make('company')->label('Empresa')->searchable()->placeholder('—')->toggleable(),
                TextColumn::make('service_label')->label('Servicio')->toggleable(),
                TextColumn::make('message')->label('Mensaje')->searchable()->limit(50)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('locale')->label('Idioma')->formatStateUsing(fn ($s) => strtoupper($s))->toggleable(),
                TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn ($s) => self::STATUSES[$s] ?? $s)->color(fn ($s) => self::STATUS_COLORS[$s] ?? 'gray'),
                $mailColumn('ack', 'Acuse'),
                $mailColumn('notify', 'Notificación'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(self::STATUSES)->multiple(),
                SelectFilter::make('service_id')->label('Servicio')
                    ->options(fn () => Service::query()->orderBy('sort')->get()->mapWithKeys(fn (Service $s) => [$s->id => $s->tr('title', 'es')])),
                SelectFilter::make('locale')->label('Idioma')->options(['es' => 'Español', 'en' => 'English']),
                Filter::make('failed')->label('Con envíos fallidos')
                    ->query(fn (Builder $q) => $q->where(fn ($w) => $w->where('ack_status', 'failed')->orWhere('notify_status', 'failed'))),
                Filter::make('created_at')->label('Fecha')
                    ->schema([DatePicker::make('from')->label('Desde'), DatePicker::make('until')->label('Hasta')])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([self::retryAction(), EditAction::make()->label('Abrir')])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markStatus')->label('Cambiar estado')->icon(Heroicon::OutlinedTag)
                        ->schema([Select::make('status')->label('Estado')->options(self::STATUSES)->required()->native(false)])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
