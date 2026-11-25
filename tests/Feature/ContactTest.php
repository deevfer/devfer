<?php

namespace Tests\Feature;

use App\Mail\TemplateMail;
use App\Models\Lead;
use App\Models\Setting;
use App\Support\LeadMailer;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->seed(ContentSeeder::class);
        config(['inertia.ssr.enabled' => false]);
        $this->withoutDefer();
        RateLimiter::clear('contact:10m:'.hash('sha256', '127.0.0.1'));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'company' => 'Ejemplo S.A.',
            'phone' => '50255550000',
            'service' => 'fullstack',
            'message' => 'Necesitamos rediseñar el sitio y conectarlo con nuestro CRM.',
        ], $overrides);
    }

    public function test_validation_messages_follow_the_form_language(): void
    {
        $this->from('/es')->post('/es/contacto', ['email' => 'nope'])
            ->assertSessionHasErrors(['name' => 'Escribe tu nombre.', 'service', 'message'])
            ->assertSessionHasErrors(['email' => 'Escribe un correo válido, por ejemplo nombre@empresa.com.']);

        $this->from('/en')->post('/en/contacto', ['email' => 'nope'])
            ->assertSessionHasErrors(['name' => 'Enter your name.']);

        $this->assertSame(0, Lead::query()->count());
    }

    public function test_a_lead_is_stored_and_both_emails_go_out_in_its_language(): void
    {
        Mail::fake();

        $this->from('/en')->post('/en/contacto', $this->payload(['service' => 'automation']))
            ->assertRedirect('/en')
            ->assertSessionHas('contact.name', 'Ana López');

        $lead = Lead::query()->sole();
        $this->assertSame('en', $lead->locale);
        $this->assertSame('Automation, integrations and AI', $lead->service_label);
        $this->assertSame('sent', $lead->ack_status);
        $this->assertSame('sent', $lead->notify_status);

        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->hasTo('ana@example.com')
            && $m->subjectLine === 'I got your message, Ana López'
            && str_contains($m->bodyText, 'Automation, integrations and AI'));
        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->hasTo('hola@fernandovasquez.tech')
            && $m->hasReplyTo('ana@example.com')
            && str_starts_with($m->subjectLine, 'New lead: Ana López'));
        Mail::assertSentCount(2);
    }

    public function test_email_escapes_message_content_and_preserves_plain_text(): void
    {
        $body = "Hola, Ana:\n\n<script>alert('x')</script>";
        $mail = new TemplateMail('Prueba', $body, ['address' => 'hola@example.com', 'name' => 'Fernando'], locale_: 'es');

        $html = $mail->render();
        $this->assertStringContainsString('Hola, Ana:', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertSame($body."\n", view('mail.template-text', ['body' => $body])->render());
    }

    public function test_optional_fields_and_other_service_are_accepted(): void
    {
        Mail::fake();
        $this->from('/es')->post('/es/contacto', $this->payload(['company' => '', 'service' => 'other']))
            ->assertSessionHasNoErrors();

        $lead = Lead::query()->sole();
        $this->assertNull($lead->company);
        $this->assertSame('Otro o aún no lo sé', $lead->service_label);
    }

    public function test_phone_is_required_and_accepts_only_digits(): void
    {
        Mail::fake();

        foreach (['', '5555 0000', '+50255550000', 'abc123', '12345'] as $phone) {
            $this->from('/es')->post('/es/contacto', $this->payload(['phone' => $phone]))
                ->assertSessionHasErrors(['phone' => 'Escribe un teléfono de al menos 6 dígitos, usando solo números.']);
        }

        $this->from('/en')->post('/en/contacto', $this->payload(['phone' => '']))
            ->assertSessionHasErrors(['phone' => 'Enter a phone number with at least 6 digits, using numbers only.']);

        $this->assertSame(0, Lead::query()->count());
        Mail::assertNothingSent();
    }

    public function test_honeypot_submissions_look_successful_but_store_nothing(): void
    {
        Mail::fake();
        $this->from('/es')->post('/es/contacto', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHas('contact');

        $this->assertSame(0, Lead::query()->count());
        Mail::assertNothingSent();
    }

    public function test_only_accepted_messages_count_towards_the_limit(): void
    {
        Mail::fake();
        // Validation failures do not use up the allowance.
        for ($i = 0; $i < 5; $i++) {
            $this->from('/es')->post('/es/contacto', ['name' => '']);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->from('/es')->post('/es/contacto', $this->payload())->assertSessionHasNoErrors();
        }

        $this->from('/es')->post('/es/contacto', $this->payload())
            ->assertSessionHasErrors(['form' => 'Recibí varios mensajes seguidos desde tu conexión. Espera unos minutos e inténtalo de nuevo.']);
        $this->assertSame(3, Lead::query()->count());
    }

    public function test_retry_only_resends_the_failed_email(): void
    {
        Mail::fake();
        $lead = Lead::query()->create([
            'name' => 'Ana', 'email' => 'ana@example.com', 'service_label' => 'Diseño UX/UI', 'message' => 'Hola, quiero cotizar un rediseño.',
            'locale' => 'es', 'ack_status' => 'sent', 'ack_sent_at' => now(), 'notify_status' => 'failed', 'notify_error' => 'Connection refused',
        ]);

        LeadMailer::deliver($lead);

        Mail::assertSentCount(1);
        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->hasTo('hola@fernandovasquez.tech'));
        $this->assertSame('sent', $lead->fresh()->notify_status);
        $this->assertNull($lead->fresh()->notify_error);
    }

    public function test_failed_emails_are_recorded_without_losing_the_lead(): void
    {
        Setting::putMany([
            'mail.enabled' => true, 'mail.host' => '127.0.0.1', 'mail.port' => 1, 'mail.encryption' => 'none',
            'mail.username' => 'smtp-user', 'mail.password' => 'super-secret-password',
        ], ['mail.password']);

        $this->from('/es')->post('/es/contacto', $this->payload())->assertSessionHasNoErrors();

        $lead = Lead::query()->sole();
        $this->assertSame('failed', $lead->ack_status);
        $this->assertSame('failed', $lead->notify_status);
        $this->assertStringNotContainsString('super-secret-password', (string) $lead->ack_error);
        $this->assertStringNotContainsString('smtp-user', (string) $lead->ack_error);
    }

    public function test_smtp_password_is_stored_encrypted(): void
    {
        Setting::put('mail.password', 'super-secret-password', true);

        $raw = Setting::query()->where('key', 'mail.password')->value('value');
        $this->assertStringNotContainsString('super-secret-password', $raw);
        $this->assertSame('super-secret-password', Setting::get('mail.password'));
    }

    private function enableRecaptcha(string $version = 'v3'): void
    {
        Setting::putMany([
            'recaptcha.enabled' => true, 'recaptcha.version' => $version, 'recaptcha.site_key' => 'site-key',
            'recaptcha.secret' => 'secret-key', 'recaptcha.threshold' => 0.5, 'recaptcha.action' => 'contact',
        ], ['recaptcha.secret']);
    }

    public function test_recaptcha_v3_checks_token_action_and_score_on_the_server(): void
    {
        Mail::fake();
        $this->enableRecaptcha('v3');
        $verify = 'https://www.google.com/recaptcha/api/siteverify';

        Http::fakeSequence($verify)
            ->push(['success' => true, 'score' => 0.9, 'action' => 'contact'])
            ->push(['success' => true, 'score' => 0.2, 'action' => 'contact'])
            ->push(['success' => true, 'score' => 0.9, 'action' => 'login'])
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']]);

        $this->from('/es')->post('/es/contacto', $this->payload(['recaptcha_token' => 'good']))->assertSessionHasNoErrors();
        $this->assertSame(0.9, Lead::query()->sole()->recaptcha_score);

        foreach (['low-score', 'wrong-action', 'invalid'] as $case) {
            $this->from('/es')->post('/es/contacto', $this->payload(['recaptcha_token' => $case]))
                ->assertSessionHasErrors('form');
        }
        // A missing token is rejected without calling Google.
        $this->from('/es')->post('/es/contacto', $this->payload())->assertSessionHasErrors('form');

        $this->assertSame(1, Lead::query()->count());
        Http::assertSent(fn ($request) => $request['secret'] === 'secret-key' && $request['response'] === 'good');
    }

    public function test_recaptcha_v2_checks_the_token_only(): void
    {
        Mail::fake();
        $this->enableRecaptcha('v2');
        Http::fake(['www.google.com/*' => Http::response(['success' => true])]);

        $this->from('/en')->post('/en/contacto', $this->payload(['recaptcha_token' => 'checkbox-token']))->assertSessionHasNoErrors();
        $this->assertSame(1, Lead::query()->count());
    }

    public function test_recaptcha_settings_never_reach_the_browser_except_the_site_key(): void
    {
        $this->enableRecaptcha('v3');

        $this->get('/es')->assertInertia(fn ($page) => $page
            ->where('recaptcha.enabled', true)
            ->where('recaptcha.siteKey', 'site-key')
            ->missing('recaptcha.secret'));
        $this->assertStringNotContainsString('secret-key', $this->get('/es')->getContent());
    }
}
