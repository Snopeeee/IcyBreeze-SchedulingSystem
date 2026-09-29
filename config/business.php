<?php

return [
    'name' => env('BUSINESS_NAME', 'IcyBreeze Aircon Cleaning'),
    'tagline' => env('BUSINESS_TAGLINE', 'Cleaner Air, Cooler Life.'),
    'description' => env('BUSINESS_DESCRIPTION', 'Professional aircon cleaning and recurring care plans for homes in Iligan City.'),
    'phone_display' => env('BUSINESS_PHONE_DISPLAY', '0967 873 0654'),
    'phone_e164' => env('BUSINESS_PHONE_E164', '+639678730654'),
    'email' => env('BUSINESS_EMAIL', 'icybreezeaccleaning@gmail.com'),
    'base_location' => env('BUSINESS_BASE_LOCATION', 'Tambacan, Iligan City, Philippines'),
    'service_area' => env('BUSINESS_SERVICE_AREA', 'Iligan City'),
    'facebook_url' => env('BUSINESS_FACEBOOK_URL', 'https://www.facebook.com/icybreezeac/'),
    'facebook_handle' => env('BUSINESS_FACEBOOK_HANDLE', 'Icy Breeze Aircon Cleaning'),
    'instagram_url' => env('BUSINESS_INSTAGRAM_URL', 'https://www.instagram.com/icybreezeph/'),
    'instagram_handle' => env('BUSINESS_INSTAGRAM_HANDLE', '@icybreezeph'),
    'hours_short' => env('BUSINESS_HOURS_SHORT', 'Mon–Sat, 8:00 AM–5:00 PM'),
    'hours_full' => env('BUSINESS_HOURS_FULL', 'Monday to Saturday, 8:00 AM to 5:00 PM'),
    'reviews' => [
        [
            'name' => 'Bing Cabanes Nahcram',
            'quote' => 'Excellent service—highly recommended.',
        ],
        [
            'name' => 'Mari Car',
            'quote' => 'Excellent service! Fast, clean, and very affordable. I am fully satisfied. Best aircon cleaning service in Iligan! Highly recommended, and I will definitely book again.',
        ],
        [
            'name' => 'Iris Lorraine Millan-Salvani',
            'quote' => 'Highly recommended! Great service from start to finish. Mga buotan kaayo ang ga-cleaning, very neat and professional sila, and pinaka-nice kay on time jud sila niabot. Limpyo kaayo ilang trabaho! We will definitely have our aircon cleaned by them again next time.',
        ],
        [
            'name' => 'Dianne Quilo - Dosdos',
            'quote' => 'Smooth transaction. Communicative from inquiry and booking through payment and the service itself. Convenient payment method, prompt customer service, and honest, friendly service providers. Excited to book with them regularly and hoping to match with their schedules soon.',
        ],
    ],
    'facebook_updates' => [
        [
            'type' => 'Maintenance reminder',
            'date' => 'September 25, 2026',
            'title' => 'Five signs your aircon may need cleaning',
            'summary' => 'Weak airflow, reduced cooling, water leakage, unusual odors, or a higher electricity bill may indicate that your unit needs professional cleaning.',
            'icon' => 'ph-warning-circle',
            'url' => 'https://www.facebook.com/reel/1073668675638158/',
            'featured' => true,
        ],
        [
            'type' => 'Service update',
            'date' => 'September 23, 2026',
            'title' => 'Three aircon units deep cleaned in Luinab',
            'summary' => 'A recent multi-unit cleaning appointment completed for a customer in Luinab, Iligan City.',
            'icon' => 'ph-buildings',
            'url' => 'https://www.facebook.com/photo/?fbid=122117341719290339&set=pcb.122117343165290339',
        ],
        [
            'type' => 'Cleaning result',
            'date' => 'September 22, 2026',
            'title' => 'Inside a Samsung split-type unit in Luinab',
            'summary' => 'A closer look at what can collect inside a frequently used split-type aircon—and why thorough interior cleaning matters.',
            'icon' => 'ph-wind',
            'url' => 'https://www.facebook.com/reel/1597656315392856/',
        ],
    ],
];
