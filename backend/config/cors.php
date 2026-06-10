<?php

declare(strict_types=1);

$frontendUrl = env('FRONTEND_URL');
$frontendOrigin = null;

if (is_string($frontendUrl) && $frontendUrl !== '') {
    $parts = parse_url($frontendUrl);
    if (isset($parts['scheme'], $parts['host'])) {
        $frontendOrigin = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $frontendOrigin .= ':' . $parts['port'];
        }
    }
}

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Browser Origin header never includes the path (e.g. GitHub Pages project sites).
    'allowed_origins' => array_values(array_unique(array_filter([
        $frontendOrigin,
        is_string($frontendUrl) ? rtrim($frontendUrl, '/') : null,
    ]))),

    'allowed_origins_patterns' => env('APP_ENV') === 'local'
        ? [
            '#^http://localhost:\d+$#',
            '#^http://127\.0\.0\.1:\d+$#',
        ]
        : array_values(array_filter([
            $frontendOrigin ? '#^' . preg_quote($frontendOrigin, '#') . '$#' : null,
        ])),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
