<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'paystack' => [
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    ],
    'swiftverify' => [
        'base_url' => env('SWIFTVERIFY_BASE_URL'),
        'api_key' => env('SWIFTVERIFY_API_KEY'),
        'timeout' => env('SWIFTVERIFY_TIMEOUT', 30),
    ],
];
