<?php

return [
    'brand' => [
        'name' => 'Info-Sphere Technologies',
        'product' => 'Infosphere Verification Portal',
        'primary_color' => '#145DA0',
        'accent_color' => '#19B6D2',
    ],
    'security' => [
        'request_logging_enabled' => env('IVP_REQUEST_LOGGING', true),
        'security_headers_enabled' => env('IVP_SECURITY_HEADERS', true),
    ],
    'features' => [
        'subscription_billing' => false,
        'wallet' => false,
        'payments' => false,
        'verification' => false,
        'reports' => false,
        'developer_api' => false,
    ],
];
