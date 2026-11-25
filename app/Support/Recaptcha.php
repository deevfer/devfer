<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google reCAPTCHA, configured from the panel. v3 checks the token, the
 * action and the score against the threshold; v2 (checkbox) checks the token.
 */
final class Recaptcha
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public static function enabled(): bool
    {
        return (bool) Setting::get('recaptcha.enabled') && filled(Setting::get('recaptcha.site_key')) && filled(Setting::get('recaptcha.secret'));
    }

    /** @return array{enabled: bool, version?: string, siteKey?: string, action?: string} */
    public static function publicConfig(): array
    {
        if (! self::enabled()) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'version' => Setting::get('recaptcha.version', 'v3'),
            'siteKey' => Setting::get('recaptcha.site_key'),
            'action' => Setting::get('recaptcha.action', 'contact'),
        ];
    }

    /** @return array{ok: bool, score: ?float, reason: ?string} */
    public static function verify(?string $token, ?string $ip = null): array
    {
        if (! self::enabled()) {
            return ['ok' => true, 'score' => null, 'reason' => null];
        }
        if (blank($token)) {
            return ['ok' => false, 'score' => null, 'reason' => 'missing-token'];
        }

        try {
            $response = Http::asForm()->timeout(8)->post(self::VERIFY_URL, array_filter([
                'secret' => Setting::get('recaptcha.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification request failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'score' => null, 'reason' => 'unreachable'];
        }

        $data = $response->json() ?? [];
        $score = isset($data['score']) ? (float) $data['score'] : null;

        if (! ($data['success'] ?? false)) {
            return ['ok' => false, 'score' => $score, 'reason' => implode(',', $data['error-codes'] ?? ['invalid'])];
        }

        if (Setting::get('recaptcha.version', 'v3') === 'v3') {
            $expected = Setting::get('recaptcha.action', 'contact');
            if (($data['action'] ?? null) !== $expected) {
                return ['ok' => false, 'score' => $score, 'reason' => 'action-mismatch'];
            }
            $threshold = (float) Setting::get('recaptcha.threshold', 0.5);
            if ($score === null || $score < $threshold) {
                return ['ok' => false, 'score' => $score, 'reason' => 'low-score'];
            }
        }

        return ['ok' => true, 'score' => $score, 'reason' => null];
    }
}
