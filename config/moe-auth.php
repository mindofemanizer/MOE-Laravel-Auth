<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Load package routes
    |--------------------------------------------------------------------------
    | Set false for multi-portal apps that define their own auth routes
    | (e.g. /backoffice/login + /customer/login).
    */
    'load_routes' => env('MOE_AUTH_LOAD_ROUTES', true),

    /*
    |--------------------------------------------------------------------------
    | Default auth guard
    |--------------------------------------------------------------------------
    | Used by LoginAction / RegisterAction / GoogleService when no guard is
    | passed explicitly. Null = Laravel default guard.
    */
    'guard' => env('MOE_AUTH_GUARD', null),

    /*
    |--------------------------------------------------------------------------
    | Redirect paths
    |--------------------------------------------------------------------------
    */
    'redirects' => [
        'login' => '/dashboard',
        'register' => '/dashboard',
        'logout' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features (enable/disable)
    |--------------------------------------------------------------------------
    | Set to true/false to enable/disable each feature.
    | If moe/laravel-settings is installed, settings table overrides config.
    */
    'features' => [
        'registration' => true,
        'password_reset' => true,
        'otp' => false,
        'google_oauth' => false,
        'role_middleware' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expiry' => 300, // seconds (5 minutes)
        'throttle' => 60, // seconds between resends

        /*
        | Enable/disable OTP channels.
        | Available: email, whatsapp, sms
        */
        'channels' => [
            'email' => [
                'enabled' => true,
            ],
            'whatsapp' => [
                'enabled' => false,
                'provider' => 'fonnte', // fonnte, wablas, manual (custom API)
                'api_key' => '',
                'api_url' => '',
            ],
            'sms' => [
                'enabled' => false,
                'provider' => 'twilio', // twilio, nexmo, zenziva, manual (custom API)
                'api_key' => '',
                'api_secret' => '',
                'from' => '',
            ],
        ],

        /*
        | Custom OTP message template.
        | Available vars: {code}, {minutes}, {app_name}
        */
        'message' => [
            'email' => 'Your verification code is: {code}\n\nThis code expires in {minutes} minutes.\n\nIf you did not request this code, please ignore this message.',
            'whatsapp' => '{app_name} verification code: {code} (expires in {minutes} min)',
            'sms' => '{app_name}: {code} is your verification code ({minutes} min)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth Configuration
    |--------------------------------------------------------------------------
    | Requires laravel/socialite package.
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),

        /*
        | When false, only existing accounts may sign in via Google.
        | Useful for invite-only / pre-provisioned customer portals.
        */
        'auto_create' => env('MOE_AUTH_GOOGLE_AUTO_CREATE', true),

        /*
        | Extra attributes written when linking an existing account.
        | Supported placeholders: {id}, {name}, {email}, {avatar}
        */
        'link_attributes' => [
            'google_id' => '{id}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Middleware Configuration
    |--------------------------------------------------------------------------
    | For multi-portal apps (admin, vendor, customer, etc.)
    */
    'roles' => [
        /*
        | Map portal names to role values.
        | The middleware will check if authenticated user's role matches.
        */
        'portals' => [
            'admin' => ['admin', 'supervisor', 'super_admin'],
            'vendor' => ['vendor'],
            'staff' => ['admin', 'supervisor', 'staff', 'warehouse', 'finance', 'cs', 'marketing', 'purchasing', 'qc', 'super_admin', 'support', 'developer'],
            'backoffice' => ['super_admin', 'finance', 'support', 'developer'],
        ],

        /*
        | Redirect paths per portal when unauthorized.
        */
        'redirects' => [
            'admin' => '/backoffice/login',
            'vendor' => '/vendor/login',
            'staff' => '/backoffice/login',
            'backoffice' => '/backoffice/login',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    | The user model to use for auth. If not set, uses config('auth.providers.users.model').
    */
    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Password Rules
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min' => 8,
        'require_uppercase' => false,
        'require_lowercase' => false,
        'require_digit' => false,
        'require_symbol' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'otp_send' => [
            'max_attempts' => 3,
            'decay_minutes' => 5,
        ],
        'otp_verify' => [
            'max_attempts' => 5,
            'decay_minutes' => 5,
        ],
    ],
];
