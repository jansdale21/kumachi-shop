<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        /** laravel = send via MAIL_* (SMTP relay + xsmtpsib password). api = Brevo REST + xkeysib API key. */
        'mail_transport' => env('BREVO_MAIL_TRANSPORT', 'laravel'),
        /** After SMTP failure, retry via REST if BREVO_API_KEY starts with xkeysib- */
        'fallback_to_api' => env('BREVO_FALLBACK_TO_API', true),
        'from_email' => env('BREVO_FROM_EMAIL', env('MAIL_FROM_ADDRESS', 'no-reply@example.com')),
        'from_name' => env('BREVO_FROM_NAME', env('MAIL_FROM_NAME', 'Kumachi')),
    ],

];
