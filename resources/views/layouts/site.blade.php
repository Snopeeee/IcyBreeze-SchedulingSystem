<!doctype html>
<html lang="en">
<head>
    @php
        $business = config('business');
        $businessSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $business['name'],
            'description' => $business['description'],
            'telephone' => $business['phone_e164'],
            'email' => $business['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Tambacan',
                'addressLocality' => 'Iligan City',
                'addressCountry' => 'PH',
            ],
            'areaServed' => $business['service_area'],
            'sameAs' => [$business['facebook_url'], $business['instagram_url']],
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'opens' => '08:00',
                'closes' => '17:00',
            ]],
        ];
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? $business['description'] }}">
    <title>{{ isset($title) ? $title.' | IcyBreeze Aircon Cleaning' : 'IcyBreeze Aircon Cleaning' }}</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icybreeze-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icybreeze-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root{--hero-image:url('{{ asset('images/icybreeze-breeze-hero.png') }}');--brand-mark-image:url('{{ asset('images/icybreeze-brand-mark.png') }}');--brand-mark-inverse-image:url('{{ asset('images/icybreeze-brand-mark-inverse.png') }}')}</style>
    <script type="application/ld+json">{!! json_encode($businessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
<body class="site-body">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="topbar">
        <div class="site-shell topbar-inner">
            <div class="topbar-contact">
                <a href="tel:{{ $business['phone_e164'] }}"><i class="ph ph-phone"></i><span>{{ $business['phone_display'] }}</span></a>
            </div>
            <div class="topbar-meta">
                <span><i class="ph ph-clock"></i> {{ $business['hours_short'] }}</span>
            </div>
        </div>
    </div>

    <header class="site-header" data-header>
        <div class="site-shell nav-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="IcyBreeze home">
                <img src="{{ asset('images/icybreeze-logo-compact.png') }}" alt="IcyBreeze Aircon Cleaning">
            </a>
            <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="site-menu">
                <i class="ph ph-list"></i><span class="sr-only">Open menu</span>
            </button>
            <nav class="site-nav" id="site-menu" data-site-menu>
                <div class="site-nav-links">
                    <a class="{{ request()->routeIs('services') ? 'is-active' : '' }}" href="{{ route('services') }}">Services</a>
                    <a class="{{ request()->routeIs('how') ? 'is-active' : '' }}" href="{{ route('how') }}">Service Process</a>
                    <a class="{{ request()->routeIs('subscriptions.*') ? 'is-active' : '' }}" href="{{ route('subscriptions.create') }}">Maintenance Plans <span class="nav-status">Soon</span></a>
                    <a class="{{ request()->routeIs('coverage') ? 'is-active' : '' }}" href="{{ route('coverage') }}">Service Area</a>
                    <a class="{{ request()->routeIs('faq') ? 'is-active' : '' }}" href="{{ route('faq') }}">FAQs</a>
                </div>
                <a class="nav-cta" href="{{ route('booking.create') }}"><i class="ph ph-calendar-check"></i> Schedule Service</a>
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="site-shell flash flash-success"><i class="ph-fill ph-check-circle"></i>{{ session('success') }}</div>
    @endif

    <main id="main-content" class="site-main">@yield('content')</main>

    <footer class="site-footer">
        <div class="site-shell footer-grid">
            <div class="footer-brand">
                <span class="footer-logo-panel"><img src="{{ asset('images/icybreeze-logo-brand-inverse.png') }}" alt="IcyBreeze Aircon Cleaning — Cleaner Air, Cooler Life"></span>
                <p>{{ $business['tagline'] }} Professional, reliable, and transparent aircon cleaning services throughout Iligan City.</p>
                <div class="footer-social" aria-label="IcyBreeze social media">
                    <a href="{{ $business['facebook_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="IcyBreeze on Facebook"><i class="ph-fill ph-facebook-logo"></i></a>
                    <a href="{{ $business['instagram_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="IcyBreeze on Instagram"><i class="ph ph-instagram-logo"></i></a>
                </div>
            </div>
            <div><h3>Information</h3><a href="{{ route('services') }}">Services and Pricing</a><a href="{{ route('how') }}">Service Process</a><a href="{{ route('subscriptions.create') }}">Maintenance Plans (Coming Soon)</a><a href="{{ route('coverage') }}">Service Area</a><a href="{{ route('faq') }}">Frequently Asked Questions</a></div>
            <div><h3>Customer Support</h3><a href="tel:{{ $business['phone_e164'] }}">{{ $business['phone_display'] }}</a><a href="mailto:{{ $business['email'] }}">{{ $business['email'] }}</a><a href="{{ route('contact') }}">Contact IcyBreeze</a><span>{{ $business['base_location'] }}</span><span>{{ $business['hours_short'] }}</span></div>
            <div class="footer-book"><h3>Schedule professional service</h3><p>Select the unit type and an available appointment window.</p><a class="button button-yellow" href="{{ route('booking.create') }}">Schedule Service <i class="ph ph-arrow-right"></i></a></div>
        </div>
        <div class="site-shell footer-bottom"><span>© {{ now()->year }} IcyBreeze Aircon Cleaning · Iligan City</span><span><a href="{{ route('admin.login') }}">Admin portal</a></span></div>
    </footer>
</body>
</html>
