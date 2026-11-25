<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

$locales = config('site.locales');

Route::get('/', [HomeController::class, 'redirect']);
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

// The previous site's URLs: the CV files keep their paths, the pages move to their sections.
Route::get('/assets/cv/cv_fvasquez_{locale}.pdf', [CvController::class, 'show'])->whereIn('locale', $locales)->name('cv');
Route::permanentRedirect('/servicios', '/es#servicios');
Route::permanentRedirect('/contacto', '/es#contacto');

Route::get('/{locale}', [HomeController::class, 'show'])->whereIn('locale', $locales)->name('home');
Route::post('/{locale}/contacto', [ContactController::class, 'store'])->whereIn('locale', $locales)
    ->middleware('throttle:contact')->name('contact.store');
Route::get('/{locale}/opengraph-image', [SeoController::class, 'ogImage'])->whereIn('locale', $locales)->name('og');
