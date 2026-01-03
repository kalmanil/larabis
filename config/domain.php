<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    |
    | This config stores domain-specific settings passed from domain folders.
    | Replaces direct $_ENV manipulation for better upgrade safety.
    |
    | Values are set via putenv() in domain folder index.php files and
    | fall back to $_ENV for backward compatibility.
    |
    */

    'tenant_id' => env('DOMAIN_TENANT_ID'),
    'code' => env('DOMAIN_CODE', 'default'),
    'site_title' => env('DOMAIN_SITE_TITLE'),
    'theme_color' => env('DOMAIN_THEME_COLOR'),
];

