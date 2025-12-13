<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pyramid API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the Pyramid data service
    |
    */

    'base_url' => env('PYRAMID_API_URL', 'http://pyramid:8000/api'),

    'api_key' => env('PYRAMID_API_KEY'),

    'timeout' => env('PYRAMID_TIMEOUT', 30),

    'cache_ttl' => env('PYRAMID_CACHE_TTL', 600), // 10 minutes

    'retry' => [
        'times' => env('PYRAMID_RETRY_TIMES', 3),
        'sleep' => env('PYRAMID_RETRY_SLEEP', 100), // milliseconds
    ],

];

