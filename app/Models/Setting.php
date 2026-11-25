<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Key/value settings edited from Filament. Secrets (SMTP password, reCAPTCHA
 * secret) are stored encrypted with APP_KEY and never sent to the browser.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    private const CACHE_KEY = 'settings.all';

    /** @return array<string, mixed> */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->get()->mapWithKeys(function (Setting $s) {
                $value = $s->value;
                if ($s->is_encrypted && filled($value)) {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (\Throwable) {
                        $value = null;
                    }
                }

                if ($value === null || $value === 'null') {
                    return [$s->key => null];
                }

                return [$s->key => json_decode((string) $value, true) ?? $value];
            })->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allValues()[$key] ?? null;

        return blank($value) ? $default : $value;
    }

    public static function put(string $key, mixed $value, bool $encrypted = false): void
    {
        $stored = match (true) {
            $value === null => null,
            is_string($value) => $value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };
        if ($encrypted && filled($stored)) {
            $stored = Crypt::encryptString($stored);
        }
        static::query()->updateOrCreate(['key' => $key], ['value' => $stored, 'is_encrypted' => $encrypted]);
        Cache::forget(self::CACHE_KEY);
    }

    /** @param array<string, mixed> $values */
    public static function putMany(array $values, array $encrypted = []): void
    {
        foreach ($values as $key => $value) {
            static::put($key, $value, in_array($key, $encrypted, true));
        }
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
