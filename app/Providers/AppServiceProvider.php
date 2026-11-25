<?php

namespace App\Providers;

use App\Models\SiteText;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Flood guard for the contact endpoint. Accepted messages have their own,
        // stricter limit in ContactController.
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(20)->by('contact:burst:'.$request->ip())->response(function () use ($request) {
                $locale = $request->route('locale') ?? config('site.default_locale');

                return back()->withErrors(['form' => SiteText::forLocale($locale)['errors.rate'] ?? 'Too many attempts.']);
            });
        });
    }
}
