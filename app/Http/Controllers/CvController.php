<?php

namespace App\Http\Controllers;

use App\Support\CvFiles;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvController extends Controller
{
    public function show(string $locale): StreamedResponse
    {
        $path = CvFiles::path($locale) ?? abort(404);

        return Storage::disk('local')->response($path, "cv_fvasquez_{$locale}.pdf", [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ], 'inline');
    }
}
