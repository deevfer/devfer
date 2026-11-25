<?php

namespace App\Filament\Pages;

use App\Mail\TemplateMail;
use App\Support\LeadMailer;
use App\Support\SiteMailer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Integrations extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'reCAPTCHA y correo';

    protected static ?string $title = 'reCAPTCHA y correo (SMTP)';

    protected static string|\UnitEnum|null $navigationGroup = 'Contacto';

    protected static ?int $navigationSort = 3;

    protected function keys(): array
    {
        return [
            'recaptcha.enabled', 'recaptcha.version', 'recaptcha.site_key', 'recaptcha.secret', 'recaptcha.threshold', 'recaptcha.action',
            'mail.enabled', 'mail.host', 'mail.port', 'mail.encryption', 'mail.username', 'mail.password',
            'mail.from_address', 'mail.from_name', 'mail.notify_to',
        ];
    }

    protected function secrets(): array
    {
        return ['recaptcha.secret', 'mail.password'];
    }

    public function form(Schema $schema): Schema
    {
        $f = fn (string $key) => self::field($key);
        $stored = fn (string $key) => $this->hasStoredSecret($key)
            ? 'Hay un valor guardado (cifrado). Déjalo vacío para conservarlo.'
            : 'Aún no hay un valor guardado.';

        return $schema->statePath('data')->components([
            Section::make('Google reCAPTCHA')
                ->description('Protege el formulario de contacto. Las claves se crean en google.com/recaptcha/admin para el dominio del sitio.')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->schema([
                    Toggle::make($f('recaptcha.enabled'))->label('Activar reCAPTCHA en el formulario')->live(),
                    Grid::make(2)->schema([
                        Select::make($f('recaptcha.version'))->label('Versión')->native(false)->live()
                            ->options(['v3' => 'v3 (invisible, con puntuación)', 'v2' => 'v2 (casilla «No soy un robot»)'])->required(),
                        TextInput::make($f('recaptcha.site_key'))->label('Clave del sitio (pública)')->maxLength(100),
                        TextInput::make($f('recaptcha.secret'))->label('Clave secreta')->password()->revealable(false)
                            ->autocomplete('new-password')->helperText($stored('recaptcha.secret')),
                        TextInput::make($f('recaptcha.threshold'))->label('Puntuación mínima (v3)')->numeric()
                            ->minValue(0)->maxValue(1)->step(0.05)
                            ->helperText('De 0 a 1. Google recomienda empezar en 0.5.')
                            ->visible(fn (Get $get) => $get($f('recaptcha.version')) === 'v3'),
                        TextInput::make($f('recaptcha.action'))->label('Acción esperada (v3)')->alphaDash()->maxLength(40)
                            ->helperText('Debe coincidir con la acción que envía el formulario.')
                            ->visible(fn (Get $get) => $get($f('recaptcha.version')) === 'v3'),
                    ]),
                ]),
            Section::make('Correo saliente (SMTP)')
                ->description('Con SMTP desactivado se usa el correo configurado en el servidor (.env). La contraseña se guarda cifrada y nunca se muestra.')
                ->icon(Heroicon::OutlinedEnvelope)
                ->schema([
                    Toggle::make($f('mail.enabled'))->label('Usar este servidor SMTP'),
                    Grid::make(3)->schema([
                        TextInput::make($f('mail.host'))->label('Servidor')->placeholder('smtp.ejemplo.com')->maxLength(190),
                        TextInput::make($f('mail.port'))->label('Puerto')->numeric()->minValue(1)->maxValue(65535),
                        Select::make($f('mail.encryption'))->label('Cifrado')->native(false)
                            ->options(['tls' => 'STARTTLS (587)', 'ssl' => 'SSL/TLS (465)', 'none' => 'Ninguno']),
                        TextInput::make($f('mail.username'))->label('Usuario')->autocomplete('off')->maxLength(190),
                        TextInput::make($f('mail.password'))->label('Contraseña')->password()->revealable(false)
                            ->autocomplete('new-password')->helperText($stored('mail.password')),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make($f('mail.from_address'))->label('Remitente (correo)')->email()->required(),
                        TextInput::make($f('mail.from_name'))->label('Remitente (nombre)')->required()->maxLength(80),
                        TextInput::make($f('mail.notify_to'))->label('Destinatario interno')->email()->required()
                            ->helperText('Recibe la notificación de cada lead.'),
                    ]),
                    Actions::make([
                        Action::make('testMail')
                            ->label('Enviar correo de prueba')
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->color('gray')
                            ->schema([
                                TextInput::make('to')->label('Enviar a')->email()->required()
                                    ->default(fn () => SiteMailer::notifyTo()),
                            ])
                            ->modalDescription('Usa la configuración guardada. Guarda los cambios antes de probar.')
                            ->action(function (array $data) {
                                try {
                                    SiteMailer::mailer()->to($data['to'])->send(new TemplateMail(
                                        'Prueba de correo · fernandovasquez.tech',
                                        "Este es un correo de prueba enviado desde el panel.\n\nSi lo recibiste, la configuración funciona.",
                                        SiteMailer::from(),
                                    ));
                                    Notification::make()->title('Correo de prueba enviado')
                                        ->body(SiteMailer::usesPanelSmtp() ? 'Enviado por el SMTP del panel.' : 'Enviado con el correo del servidor (.env), porque el SMTP del panel está desactivado.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('No se pudo enviar')->body(LeadMailer::redact($e->getMessage()))->danger()->persistent()->send();
                                }
                            }),
                    ]),
                ]),
        ]);
    }
}
