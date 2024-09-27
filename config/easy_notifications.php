<?php

return [

    "onfon" => [
        'version' => env('ONFON_VERSION', 'OLD'),
        "api_sender_id" => env('ONFON_SENDER_ID'),
        "api_username" => env("ONFON_API_USERNAME"),
        "api_password" => env("ONFON_API_PASSWORD"),
        "is_sandbox" => env("ONFON_IS_SANDBOX", true),
        "sandbox_url" => env("ONFON_SANDBOX_URL", "https://apis.onfonmedia.co.ke"),
        "production_url" => env("ONFON_PRODUCT_URL", "https://apis.onfonmedia.co.ke"),
        "dlr_callback" => env("ONFON_DLR_CALLBACK", "/v1/easy/onfon/callback"),
        "dlr" => env("ONFON_DLR", "no"),
        "endpoints" => [
            "authorization" => [
                "endpoint" => "/v1/authorization",
                "method" => "POST",
            ],
            "balance" => [
                "endpoint" => "/v2_balance",
                "method" => "GET",
            ],
            "send_sms" => [
                "endpoint" => "/v2_send",
                "method" => "POST",
            ],
        ],
        "old_version" => [
            "endpoint" => env('ONFON_OV_URL'),
            "access_key" => env('ONFON_OV_ACCESS_KEY'),
            "sender_id" => env("ONFON_OV_SENDER_ID"),
            "client_id" => env("ONFON_OV_CLIENT_ID"),
            "api_key" => env("ONFON_OV_API_KEY"),
        ],
    ],
    'africastalking' => [
        'api_host' => env('AT_API_HOST', 'api.africastalking.com'),
        'api_url' => env('AT_URL', 'https://api.africastalking.com/version1'),
        'auth_endpoint' => '/auth-token/generate',
        'sms_endpoint' => '/version1/messaging',
        'api_key' => env('AT_API_KEY'),
        'username' => env('AT_USERNAME', 'sandbox'),
        'from' => env('AT_FROM', 'AFRICASTKNG'),
        'max_bulk_sms' => env('AT_MAX_BULK_SMS', 20),

    ]
];
