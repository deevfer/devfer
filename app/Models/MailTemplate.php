<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Editable email templates. Bodies are plain text with {variables}; only the
 * names in VARIABLES are replaced, everything else is left as written.
 */
class MailTemplate extends Model
{
    use HasTranslations;

    public const VISITOR_ACK = 'visitor_ack';

    public const OWNER_NOTIFICATION = 'owner_notification';

    public const VARIABLES = ['name', 'email', 'company', 'phone', 'service', 'message', 'date'];

    protected $fillable = ['key', 'name', 'subject', 'body'];

    protected array $translatable = ['subject', 'body'];

    /** @param array<string, string|null> $vars */
    public static function interpolate(string $text, array $vars): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($vars) {
            return in_array($m[1], self::VARIABLES, true) ? (string) ($vars[$m[1]] ?? '') : $m[0];
        }, $text);
    }

    /** @return array{subject: string, body: string} */
    public function render(string $locale, array $vars): array
    {
        return [
            'subject' => self::interpolate((string) $this->tr('subject', $locale), $vars),
            'body' => self::interpolate((string) $this->tr('body', $locale), $vars),
        ];
    }
}
