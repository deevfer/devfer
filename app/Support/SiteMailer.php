<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;

/**
 * Builds the mailer from the SMTP settings saved in the panel. When SMTP is
 * not enabled there, the app's default mailer (.env, `log` locally) is used.
 * The password is decrypted only here, in memory, and never logged.
 */
final class SiteMailer
{
    public static function usesPanelSmtp(): bool
    {
        return (bool) Setting::get('mail.enabled') && filled(Setting::get('mail.host'));
    }

    public static function mailer(): Mailer
    {
        if (! self::usesPanelSmtp()) {
            return Mail::mailer();
        }

        $encryption = Setting::get('mail.encryption', 'tls');
        config(['mail.mailers.site_smtp' => [
            'transport' => 'smtp',
            'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => Setting::get('mail.host'),
            'port' => (int) Setting::get('mail.port', 587),
            'username' => Setting::get('mail.username'),
            'password' => Setting::get('mail.password'),
            'timeout' => 15,
            'local_domain' => parse_url(config('site.url'), PHP_URL_HOST),
        ]]);
        app('mail.manager')->purge('site_smtp');

        return Mail::mailer('site_smtp');
    }

    /** @return array{address: string, name: string} */
    public static function from(): array
    {
        return [
            'address' => Setting::get('mail.from_address', config('mail.from.address')),
            'name' => Setting::get('mail.from_name', config('mail.from.name')),
        ];
    }

    public static function notifyTo(): string
    {
        return Setting::get('mail.notify_to', 'hola@fernandovasquez.tech');
    }
}
