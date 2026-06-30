<?php

return [
    'central_domains' => array_filter(explode(',', env('TENANCY_CENTRAL_DOMAINS', 'localhost,127.0.0.1'))),
    'default_resolution_strategy' => env('TENANCY_RESOLUTION_STRATEGY', 'path'),
    'path_prefix' => env('TENANCY_PATH_PREFIX', 't'),
    'cache_ttl' => (int) env('TENANCY_CACHE_TTL', 300),
    'demo_tenant_slug' => env('DEMO_TENANT_SLUG', 'demo'),
];
