<?php

return [
    'brand' => [
        'name' => 'Chacha Prime',
        'product_prefix' => 'CP',
        'default_currency' => 'USD',
        'return_window_business_days' => 7,
    ],
    'group_buying' => [
        'payment_required_immediately' => true,
        'failed_group_refund_to_wallet' => true,
    ],
    'admin_emails' => array_values(array_filter(array_map('trim', explode(',', env('CHACHA_ADMIN_EMAILS', ''))))),
    'ai' => [
        'provider' => 'gemini',
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
];
