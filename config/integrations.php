<?php

return [
    'paypal' => [
        'enabled' => env('PAYPAL_ENABLED', false),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],
    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
        'latitude' => env('OPENWEATHER_LATITUDE', 8.2280),
        'longitude' => env('OPENWEATHER_LONGITUDE', 124.2452),
    ],
    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
    ],
];
