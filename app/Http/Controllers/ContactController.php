<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteText;
use App\Support\LeadMailer;
use App\Support\Recaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

use function Illuminate\Support\defer;

class ContactController extends Controller
{
    public function store(ContactRequest $request, string $locale): RedirectResponse
    {
        $data = $request->validated();
        $texts = SiteText::forLocale($locale);

        // Bots that fill the hidden field get the normal success response and nothing is stored.
        if (filled($data['website'] ?? null)) {
            return back()->with('contact', ['name' => $data['name']]);
        }

        $check = Recaptcha::verify($data['recaptcha_token'] ?? null, $request->ip());
        if (! $check['ok']) {
            $checkbox = $check['reason'] === 'missing-token' && Recaptcha::publicConfig()['version'] === 'v2';

            return back()->withErrors(['form' => $texts[$checkbox ? 'errors.recaptcha_checkbox' : 'errors.recaptcha']]);
        }

        // Only accepted messages count towards the limit, so fixing a typo never locks anyone out.
        $ip = hash('sha256', (string) $request->ip());
        $limits = ["contact:10m:{$ip}" => [3, 600], "contact:day:{$ip}" => [10, 86400]];
        foreach ($limits as $key => [$max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return back()->withErrors(['form' => $texts['errors.rate']]);
            }
        }
        foreach ($limits as $key => [, $decay]) {
            RateLimiter::hit($key, $decay);
        }

        $service = $data['service'] === 'other' ? null : Service::query()->where('slug', $data['service'])->first();

        // The lead is stored first; emails go out after the response and are tracked on the lead.
        $lead = Lead::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'service_id' => $service?->id,
            'service_label' => $service?->tr('title', $locale) ?? $texts['form.service_other'],
            'message' => $data['message'],
            'locale' => $locale,
            'recaptcha_score' => $check['score'],
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
        ]);

        defer(fn () => LeadMailer::deliver($lead->fresh()));

        return back()->with('contact', ['name' => $lead->name]);
    }
}
