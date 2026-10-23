<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'ai' => [
        'provider' => env('AI_PROVIDER', 'mock'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
    ],

    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY', 'BEQA0rjQOwpq5CAXSFb3vvmqpMJcrp0GEnnJB1pFSlo--SwfLucP_-NPMDVPHI6WxZn-Ktv4cnK2SkvT7CE79ys'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'wc43PbVLkfE6uRj9m9PHzcjk6Rog063xIsMmNMKzPGg'),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@ruta-espanol.com'),
    ],

];
