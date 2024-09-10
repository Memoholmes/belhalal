<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        '*',
        'http://localhost',
        isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        // '*',
        'Authorization',
        'X-Requested-With',
        'X-XSRF-Token',
        'X-CSRF-TOKEN',
        'Content-Type',
        'Api-Request-Signature',
    ],

    'exposed_headers' => false,

    'max_age' => false,

    'supports_credentials' => true,

];
