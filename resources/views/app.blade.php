<!DOCTYPE html>
<html lang="{{ $page['props']['locale'] ?? config('site.default_locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ecedea">
        <meta name="color-scheme" content="light">
        <script>document.documentElement.classList.add('js')</script>

        @isset($meta)
            <title>{{ $meta['title'] }}</title>
            <meta name="description" content="{{ $meta['description'] }}">
            <meta name="author" content="Fernando Vásquez">
            <meta name="creator" content="Fernando Vásquez">
            <meta name="robots" content="index, follow">
            <link rel="canonical" href="{{ $meta['url'] }}">
            @foreach ($meta['alternates'] as $hreflang => $href)
                <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
            @endforeach

            <meta property="og:type" content="profile">
            <meta property="og:url" content="{{ $meta['url'] }}">
            <meta property="og:site_name" content="Fernando Vásquez">
            <meta property="og:title" content="{{ $meta['title'] }}">
            <meta property="og:description" content="{{ $meta['description'] }}">
            <meta property="og:locale" content="{{ $meta['ogLocale'] }}">
            <meta property="og:locale:alternate" content="{{ $meta['ogAlternate'] }}">
            <meta property="og:image" content="{{ $meta['image'] }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:type" content="image/png">
            <meta property="og:image:alt" content="{{ $meta['imageAlt'] }}">
            <meta property="profile:first_name" content="Fernando">
            <meta property="profile:last_name" content="Vásquez">

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $meta['title'] }}">
            <meta name="twitter:description" content="{{ $meta['description'] }}">
            <meta name="twitter:image" content="{{ $meta['image'] }}">

            <script type="application/ld+json">{!! json_encode($meta['person'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endisset

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
