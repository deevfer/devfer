<?php

namespace App\Filament\Resources\MailTemplates;

use App\Filament\Resources\MailTemplates\Pages\EditMailTemplate;
use App\Filament\Resources\MailTemplates\Pages\ListMailTemplates;
use App\Filament\Support\Translatable;
use App\Mail\TemplateMail;
use App\Models\MailTemplate;
use App\Support\LeadMailer;
use App\Support\SiteMailer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Plantillas de correo';

    protected static ?string $modelLabel = 'plantilla';

    protected static string|\UnitEnum|null $navigationGroup = 'Contacto';

    protected static ?int $navigationSort = 2;

    /** Sample values used by the preview and the test email. */
    public static function sample(string $locale): array
    {
        return [
            'name' => 'Ana López',
            'email' => 'ana@ejemplo.com',
            'company' => 'Ejemplo S.A.',
            'phone' => '+502 5555 0000',
            'service' => $locale === 'es' ? 'Desarrollo full-stack' : 'Full-stack development',
            'message' => $locale === 'es'
                ? 'Necesitamos rediseñar nuestro sitio y conectarlo con el CRM.'
                : 'We need to redesign our website and connect it to our CRM.',
            'date' => now('America/Guatemala')->format('Y-m-d H:i'),
        ];
    }

    public static function variablesHelp(): string
    {
        return 'Variables disponibles: '.collect(MailTemplate::VARIABLES)->map(fn ($v) => '{'.$v.'}')->implode(' ').'. El texto se envía tal cual, con saltos de línea.';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Text::make(self::variablesHelp())->columnSpanFull(),
            Translatable::tabs(fn (string $locale) => [
                TextInput::make("subject.{$locale}")->label('Asunto')->required()->maxLength(150),
                Textarea::make("body.{$locale}")->label('Cuerpo')->required()->rows(14)->maxLength(5000),
            ]),
        ]);
    }

    public static function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Vista previa')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalHeading(fn (MailTemplate $record) => "Vista previa · {$record->name}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalContent(fn (MailTemplate $record) => view('filament.mail-preview', [
                'previews' => collect(config('site.locales'))->mapWithKeys(fn ($l) => [$l => $record->render($l, self::sample($l))]),
            ]));
    }

    public static function testAction(): Action
    {
        return Action::make('test')
            ->label('Enviar prueba')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('gray')
            ->schema([
                Select::make('locale')->label('Idioma')->options(Translatable::NAMES)->default('es')->required()->native(false),
                TextInput::make('to')->label('Enviar a')->email()->required()->default(fn () => SiteMailer::notifyTo()),
            ])
            ->action(function (MailTemplate $record, array $data) {
                $mail = $record->render($data['locale'], self::sample($data['locale']));
                try {
                    SiteMailer::mailer()->to($data['to'])->send(new TemplateMail('[Prueba] '.$mail['subject'], $mail['body'], SiteMailer::from(), null, $data['locale']));
                    Notification::make()->title('Prueba enviada')->body("A {$data['to']}, con datos de ejemplo.")->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('No se pudo enviar')->body(LeadMailer::redact($e->getMessage()))->danger()->persistent()->send();
                }
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Plantilla')->description(fn (MailTemplate $r) => $r->key === MailTemplate::VISITOR_ACK ? 'Se envía a quien escribe, en el idioma del formulario.' : 'Se envía al destinatario interno.'),
                TextColumn::make('subject.es')->label('Asunto (ES)'),
                TextColumn::make('subject.en')->label('Subject (EN)'),
                Translatable::statusColumn(),
            ])
            ->paginated(false)
            ->recordActions([self::previewAction(), self::testAction(), EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailTemplates::route('/'),
            'edit' => EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
