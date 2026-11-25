<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class SiteSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Perfil, CV y SEO';

    protected static ?string $title = 'Perfil, CV y SEO';

    protected static string|\UnitEnum|null $navigationGroup = 'Sitio';

    protected static ?int $navigationSort = 1;

    protected function keys(): array
    {
        return [
            'profile.name', 'profile.available', 'profile.memoji', 'profile.photo', 'contact.email',
            'cv.es', 'cv.en',
            'seo.title.es', 'seo.title.en', 'seo.description.es', 'seo.description.en', 'seo.og_image',
        ];
    }

    /** Files kept on each disk; a replaced file is deleted so old versions do not pile up. */
    private const FILES = ['cv.es' => 'local', 'cv.en' => 'local', 'profile.photo' => 'public', 'profile.memoji' => 'public', 'seo.og_image' => 'public'];

    protected function beforeSaving(string $key, mixed $value): void
    {
        $previous = Setting::get($key);
        if (! isset(self::FILES[$key]) || $value === $previous) {
            return;
        }
        // Record when each CV was replaced; the date is shown next to the download.
        if (str_starts_with($key, 'cv.')) {
            Setting::put("{$key}_updated_at", now('America/Guatemala')->toDateString());
        }
        if ($previous) {
            Storage::disk(self::FILES[$key])->delete($previous);
        }
    }

    public function form(Schema $schema): Schema
    {
        $f = fn (string $key) => self::field($key);

        return $schema->statePath('data')->components([
            Tabs::make('Ajustes')->persistTabInQueryString()->tabs([
                Tab::make('Perfil y medios')->icon(Heroicon::OutlinedUser)->schema([
                    TextInput::make($f('profile.name'))->label('Nombre visible')->required()->maxLength(80),
                    Toggle::make($f('profile.available'))->label('Mostrar «Disponible para trabajar» en la portada'),
                    TextInput::make($f('contact.email'))->label('Correo público')->email()->required()
                        ->helperText('Se muestra en la sección de contacto y en los datos estructurados.'),
                    FileUpload::make($f('profile.memoji'))->label('Memoji')
                        ->disk('public')->directory('media')->image()
                        ->maxSize(2048)
                        ->helperText('PNG con fondo transparente. Va en la cápsula del saludo y en la barra de navegación al hacer scroll.'),
                    FileUpload::make($f('profile.photo'))->label('Foto de perfil')
                        ->disk('public')->directory('media')->image()->imageEditor()
                        ->maxSize(4096)
                        ->helperText('PNG con fondo transparente o JPG, al menos 900 px. Se usa en la tarjeta de perfil y en la imagen para redes.'),
                ]),
                Tab::make('CV')->icon(Heroicon::OutlinedDocumentText)->schema([
                    Section::make('Español')
                        ->description('Se descarga en /assets/cv/cv_fvasquez_es.pdf, en las dos versiones del sitio.')
                        ->schema([
                            FileUpload::make($f('cv.es'))->label('PDF del CV en español')
                                ->disk('local')->directory('cv')->acceptedFileTypes(['application/pdf'])
                                ->maxSize(20480)->downloadable()->required(),
                        ]),
                    Section::make('English')
                        ->description('Se descarga en /assets/cv/cv_fvasquez_en.pdf, en las dos versiones del sitio.')
                        ->schema([
                            FileUpload::make($f('cv.en'))->label('PDF del CV en inglés')
                                ->disk('local')->directory('cv')->acceptedFileTypes(['application/pdf'])
                                ->maxSize(20480)->downloadable()->required(),
                        ]),
                ]),
                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    Section::make('Español · /es')->schema([
                        TextInput::make($f('seo.title.es'))->label('Título')->required()->maxLength(70)
                            ->helperText('Hasta unos 60 caracteres para que no se corte en buscadores.'),
                        Textarea::make($f('seo.description.es'))->label('Descripción')->required()->rows(3)->maxLength(300)
                            ->helperText('Google suele mostrar unos 155 caracteres.'),
                    ]),
                    Section::make('English · /en')->schema([
                        TextInput::make($f('seo.title.en'))->label('Title')->required()->maxLength(70),
                        Textarea::make($f('seo.description.en'))->label('Description')->required()->rows(3)->maxLength(300)
                            ->helperText('Google usually shows about 155 characters.'),
                    ]),
                    FileUpload::make($f('seo.og_image'))->label('Imagen para redes (opcional)')
                        ->disk('public')->directory('media')->image()->maxSize(4096)
                        ->helperText('1200 × 630 px. Si se deja vacía, se genera una por idioma con el titular del sitio.'),
                ]),
            ]),
        ]);
    }
}
