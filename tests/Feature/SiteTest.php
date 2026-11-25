<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\SiteText;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->seed(ContentSeeder::class);
        config(['inertia.ssr.enabled' => false]);
    }

    public function test_both_locales_render_with_their_own_meta(): void
    {
        foreach (['es' => 'Desarrollador full-stack y diseñador UX/UI', 'en' => 'Full-stack developer and UX/UI designer'] as $locale => $title) {
            $html = $this->get("/{$locale}")->assertOk()->getContent();

            $this->assertStringContainsString("<html lang=\"{$locale}\"", $html);
            $this->assertStringContainsString($title, $html);
            $this->assertStringContainsString("<link rel=\"canonical\" href=\"https://fernandovasquez.tech/{$locale}\">", $html);
            $this->assertStringContainsString('hreflang="es" href="https://fernandovasquez.tech/es"', $html);
            $this->assertStringContainsString('hreflang="en" href="https://fernandovasquez.tech/en"', $html);
            $this->assertStringContainsString('hreflang="x-default" href="https://fernandovasquez.tech/"', $html);
            $this->assertStringContainsString('"@type":"Person"', $html);
            $this->assertStringContainsString('og:image', $html);
        }
    }

    public function test_page_props_are_in_the_requested_language(): void
    {
        $this->get('/en')->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('locale', 'en')
            ->where('texts', fn ($t) => $t['hero.cta_primary'] === "Let's talk")
            ->where('services.0.title', 'Full-stack development'));

        $this->get('/es')->assertInertia(fn ($page) => $page
            ->where('texts', fn ($t) => $t['hero.cta_primary'] === 'Hablemos' && $t['hero.cta_secondary'] === 'Descargar CV'));
    }

    public function test_items_missing_a_translation_are_hidden_on_that_locale(): void
    {
        Service::query()->where('slug', 'ux-ui')->first()->update(['title' => ['es' => 'Diseño UX/UI', 'en' => '']]);

        $this->get('/en')->assertInertia(fn ($page) => $page->has('services', 4));
        $this->get('/es')->assertInertia(fn ($page) => $page->has('services', 5));
    }

    public function test_previous_urls_keep_working(): void
    {
        $this->get('/servicios')->assertStatus(301)->assertRedirect('/es#servicios');
        $this->get('/contacto')->assertStatus(301)->assertRedirect('/es#contacto');

        foreach (['es', 'en'] as $locale) {
            $this->get("/assets/cv/cv_fvasquez_{$locale}.pdf")
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');
        }
    }

    public function test_root_redirects_by_cookie_then_browser_language(): void
    {
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertRedirect('/en');
        $this->get('/', ['Accept-Language' => 'es-GT'])->assertRedirect('/es');
        $this->withUnencryptedCookie('locale', 'en')->get('/', ['Accept-Language' => 'es'])->assertRedirect('/en');
    }

    public function test_sitemap_robots_and_panel_indexing(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<loc>https://fernandovasquez.tech/es</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://fernandovasquez.tech/en</loc>', $sitemap);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: https://fernandovasquez.tech/sitemap.xml');

        $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_edited_text_reaches_the_public_page(): void
    {
        $text = SiteText::query()->where('key', 'hero.cta_primary')->first();
        $text->update(['value' => ['es' => 'Conversemos', 'en' => "Let's talk"]]);

        $this->get('/es')->assertInertia(fn ($page) => $page->where('texts', fn ($t) => $t['hero.cta_primary'] === 'Conversemos'));
    }

    public function test_an_empty_og_image_setting_falls_back_to_the_generated_one(): void
    {
        \App\Models\Setting::put('seo.og_image', null);

        $this->get('/es')->assertSee('og:image" content="https://fernandovasquez.tech/es/opengraph-image', false);
    }

    public function test_og_image_is_generated_per_locale(): void
    {
        $this->get('/es/opengraph-image')->assertOk()->assertHeader('Content-Type', 'image/png');
    }
}
