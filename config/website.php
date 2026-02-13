<?php

return [
    'api_url' => env('CH_API_URL', 'http://localhost:8000'),
    'api_token' => env('CH_API_TOKEN', ''),
    'cache_ttl' => (int) env('CH_CACHE_TTL', app()->environment('production') ? 300 : 0),
    'theme' => env('CH_THEME', 'default'),
];
