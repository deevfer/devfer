<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

$locales = config('site.locales');

Route::get('/', [HomeController::class, 'redirect']);
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

Route::get('/{locale}', [HomeController::class, 'show'])->whereIn('locale', $locales)->name('home');
Route::get('/{locale}/opengraph-image', [SeoController::class, 'ogImage'])->whereIn('locale', $locales);
