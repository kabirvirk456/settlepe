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

    'didit' => [
        'api_key' => env('DIDIT_API_KEY'),
        'base_url' => env('DIDIT_BASE_URL', 'https://verification.didit.me'),
        'channel' => env('DIDIT_OTP_CHANNEL', 'sms'),
        'code_size' => env('DIDIT_OTP_CODE_SIZE', 6),
        'locale' => env('DIDIT_OTP_LOCALE', 'en-US'),
    ],

    'aisensy' => [
        'api_key' => env('AISENSY_API_KEY'),
        'base_url' => env('AISENSY_BASE_URL', 'https://backend.aisensy.com'),
        'send_path' => env('AISENSY_SEND_PATH', '/campaign/t1/api/v2'),
        'campaign_name' => env('AISENSY_OTP_CAMPAIGN_NAME'),
        'source' => env('AISENSY_SOURCE', 'new-landing-page form'),
        'template_params' => array_values(array_filter(explode(',', env('AISENSY_OTP_TEMPLATE_PARAMS', 'otp')))),
        'first_name_fallback' => env('AISENSY_FIRST_NAME_FALLBACK', 'user'),
        'otp_button_enabled' => env('AISENSY_OTP_BUTTON_ENABLED', true),
        'incomplete_application_campaign' => env('AISENSY_INCOMPLETE_APPLICATION_CAMPAIGN', 'Incomplete Application Reminder'),
        'incomplete_application_delay_minutes' => env('AISENSY_INCOMPLETE_APPLICATION_DELAY_MINUTES', 30),
        'incomplete_application_url' => env('AISENSY_INCOMPLETE_APPLICATION_URL'),
        'ttl_minutes' => env('AISENSY_OTP_TTL_MINUTES', 10),
        'fixed_otp' => env('AISENSY_FIXED_OTP'),
        'timeout' => env('AISENSY_TIMEOUT', 15),
    ],

    'legal' => [
        'terms_version' => env('TERMS_VERSION', '2026-07-13'),
    ],

    'crif' => [
        'base_url' => env('CRIF_BASE_URL', 'https://api.roopya.money/api/v2'),
        'domain_name' => env('CRIF_DOMAIN_NAME'),
        'auth_key' => env('CRIF_AUTH_KEY'),
        'request_api_code' => env('CRIF_REQUEST_API_CODE', 'CBC016'),
        'auth_api_code' => env('CRIF_AUTH_API_CODE', 'CBA017'),
        'timeout' => env('CRIF_TIMEOUT', 30),
    ],

    'razorpay' => [
        'base_url' => env('RAZORPAY_BASE_URL', 'https://api.razorpay.com/v1'),
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'consultation_amount' => (int) env('RAZORPAY_CONSULTATION_AMOUNT', 9900),
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
        'timeout' => (int) env('RAZORPAY_TIMEOUT', 15),
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

];
