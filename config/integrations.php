<?php

return [
    'paypal' => [
        'enabled' => env('PAYPAL_ENABLED', false),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],
    'resend' => [
        'api_key' => env('RESEND_API_KEY'),
        'from' => env('RESEND_FROM_ADDRESS'),
    ],
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_GEOCODING_KEY'),
    ],
    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
        'latitude' => env('OPENWEATHER_LATITUDE', 8.2280),
        'longitude' => env('OPENWEATHER_LONGITUDE', 124.2452),
    ],
];
