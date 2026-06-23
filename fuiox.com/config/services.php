<?php

return [

    'whatsapp' => [
    'token'         => env('WHATSAPP_TOKEN'),
    'phone_id'      => env('WHATSAPP_PHONE_ID'),
    'template_name' => env('WHATSAPP_TEMPLATE_NAME'),
   ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
'meta' => [
        'app_id'      => env('META_APP_ID', ''),
        'app_secret'  => env('META_APP_SECRET', ''),
        'business_id' => env('META_BUSINESS_ID', ''),
    ],
    'razorpay' => [
        'key_id'     => env('RAZORPAY_KEY_ID', ''),
        'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
    ],
];
