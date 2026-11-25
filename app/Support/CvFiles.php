<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * The two CV PDFs. They live on the private disk and are served from the
 * same URLs the previous site used, so existing links keep working.
 */
final class CvFiles
{
    public static function url(string $locale): string
    {
        return "/assets/cv/cv_fvasquez_{$locale}.pdf";
    }

    public static function path(string $locale): ?string
    {
        $path = Setting::get("cv.{$locale}");

        return $path && Storage::disk('local')->exists($path) ? $path : null;
    }

    /** @return array{locale: string, url: string, size: int, updated: ?string}|null */
    public static function describe(string $locale): ?array
    {
        $path = self::path($locale);
        if (! $path) {
            return null;
        }

        return [
            'locale' => $locale,
            'url' => self::url($locale),
            'size' => Storage::disk('local')->size($path),
            'updated' => Setting::get("cv.{$locale}_updated_at"),
        ];
    }
}
