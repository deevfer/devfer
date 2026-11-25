<?php

namespace App\Support;

/**
 * The site copy lives in resources/messages/{locale}.json. The whole file is
 * sent to Vue as a page prop; PHP reads it for meta tags and the OG image.
 */
final class Messages
{
    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    /** @return array<string, mixed> */
    public static function all(string $locale): array
    {
        return self::$cache[$locale] ??= json_decode(
            file_get_contents(resource_path("messages/{$locale}.json")),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    public static function get(string $locale, string $key): mixed
    {
        return data_get(self::all($locale), $key);
    }
}
