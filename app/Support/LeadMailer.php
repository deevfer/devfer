<?php

namespace App\Support;

use App\Mail\TemplateMail;
use App\Models\Lead;
use App\Models\MailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Sends the two emails for a lead. Each one is tracked separately, so calling
 * deliver() again (the panel's "retry" action) only sends what has not been
 * sent yet.
 */
final class LeadMailer
{
    public static function deliver(Lead $lead): void
    {
        if ($lead->ack_status !== 'sent') {
            self::send($lead, 'ack', MailTemplate::VISITOR_ACK, $lead->email, replyTo: SiteMailer::notifyTo());
        }
        if ($lead->notify_status !== 'sent') {
            self::send($lead, 'notify', MailTemplate::OWNER_NOTIFICATION, SiteMailer::notifyTo(), replyTo: $lead->email);
        }
    }

    private static function send(Lead $lead, string $kind, string $templateKey, string $to, string $replyTo): void
    {
        try {
            $template = MailTemplate::query()->where('key', $templateKey)->firstOrFail();
            $vars = $lead->templateVariables();
            $vars['company'] = $vars['company'] ?: '—';
            $vars['phone'] = $vars['phone'] ?: '—';
            $mail = $template->render($lead->locale, $vars);

            SiteMailer::mailer()->to($to)->send(new TemplateMail($mail['subject'], $mail['body'], SiteMailer::from(), $replyTo, $lead->locale));

            $lead->forceFill(["{$kind}_status" => 'sent', "{$kind}_sent_at" => now(), "{$kind}_error" => null])->save();
        } catch (\Throwable $e) {
            $error = self::redact($e->getMessage());
            $lead->forceFill(["{$kind}_status" => 'failed', "{$kind}_error" => $error])->save();
            Log::warning("Lead #{$lead->id}: {$kind} email failed", ['error' => $error]);
        }
    }

    /** SMTP errors can echo the username back; keep credentials out of the database and logs. */
    public static function redact(string $message): string
    {
        foreach (array_filter([Setting::get('mail.username'), Setting::get('mail.password')]) as $secret) {
            $message = str_replace((string) $secret, '[redacted]', $message);
        }

        return mb_substr($message, 0, 1000);
    }
}
