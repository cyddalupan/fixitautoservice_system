<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for cross-origin requests. The api routes in routes/api.php
    | are called via reverse proxy from fixitautoservices.com OR directly
    | from the WordPress SPA frontend.
    |
    */

    'paths' => [
        'api/*',
        'booking/api/*',
        '*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://fixitautoservices.com',
        'https://www.fixitautoservices.com',
        'https://app.fixitautoservices.com',
        'http://localhost',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-Booking-Token',
        'Accept',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,
];
