<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * A panel page backed by the key/value settings table. Form field names map
 * to setting keys with "." replaced by "__". Secret fields are encrypted and
 * are only overwritten when a new value is typed.
 */
abstract class SettingsPage extends Page
{
    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @return list<string> setting keys handled by this page */
    abstract protected function keys(): array;

    /** @return list<string> keys stored encrypted and never shown back */
    protected function secrets(): array
    {
        return [];
    }

    public static function field(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    public function mount(): void
    {
        $values = [];
        foreach ($this->keys() as $key) {
            $values[self::field($key)] = in_array($key, $this->secrets(), true) ? null : Setting::get($key);
        }
        $this->form->fill($values);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Guardar cambios')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach ($this->keys() as $key) {
            $value = $state[self::field($key)] ?? null;
            $secret = in_array($key, $this->secrets(), true);
            if ($secret && blank($value)) {
                continue; // keep the stored secret
            }
            $this->beforeSaving($key, $value);
            Setting::put($key, $value, $secret);
        }
        Notification::make()->title('Cambios guardados')->success()->send();
        $this->mount();
    }

    protected function beforeSaving(string $key, mixed $value): void {}

    protected function hasStoredSecret(string $key): bool
    {
        return filled(Setting::get($key));
    }
}
