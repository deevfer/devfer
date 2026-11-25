<?php

return [

    /*
    | Public URL of the site, used for canonical links, Open Graph, the
    | sitemap and JSON-LD. Not APP_URL, so local runs still emit the
    | production URLs search engines should index.
    */
    'url' => rtrim(env('SITE_URL', 'https://fernandovasquez.tech'), '/'),

    'email' => env('SITE_EMAIL', 'hola@fernandovasquez.tech'),

    'locales' => ['es', 'en'],

    'default_locale' => 'es',

];
