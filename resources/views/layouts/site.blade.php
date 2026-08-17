<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'Professional aircon cleaning and recurring care plans for homes in Iligan City.' }}">
    <title>{{ isset($title) ? $title.' | IcyBreeze Aircon Cleaning' : 'IcyBreeze Aircon Cleaning' }}</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icybreeze-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icybreeze-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <div class="topbar">
        <div class="site-shell topbar-inner">
            <div class="topbar-contact">
                <a href="tel:+639171232723"><i class="ph ph-phone"></i><span>0917 123 2723</span></a>
            </div>
            <div class="topbar-meta">
                <span><i class="ph ph-clock"></i> Mon–Sat, 8:00 AM–5:00 PM</span>
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
                <a class="{{ request()->routeIs('services') ? 'is-active' : '' }}" href="{{ route('services') }}">Services</a>
                <a class="{{ request()->routeIs('how') ? 'is-active' : '' }}" href="{{ route('how') }}">How it works</a>
                <a class="{{ request()->routeIs('subscriptions.*') ? 'is-active' : '' }}" href="{{ route('subscriptions.create') }}">Care plans</a>
                <a class="{{ request()->routeIs('coverage') ? 'is-active' : '' }}" href="{{ route('coverage') }}">Coverage</a>
                <a class="{{ request()->routeIs('faq') ? 'is-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
                <a class="nav-cta" href="{{ route('booking.create') }}"><i class="ph ph-calendar-check"></i> Book a cleaning</a>
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="site-shell flash flash-success"><i class="ph-fill ph-check-circle"></i>{{ session('success') }}</div>
    @endif

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="site-shell footer-grid">
            <div class="footer-brand">
                <span class="footer-logo-panel"><img src="{{ asset('images/icybreeze-logo-brand-inverse.png') }}" alt="IcyBreeze Aircon Cleaning — Cleaner Air, Cooler Life"></span>
                <p>Clean, reliable, professional, and affordable aircon care for Iligan City.</p>
                <div class="footer-social"><a href="#" aria-label="Facebook"><i class="ph-fill ph-facebook-logo"></i></a><a href="#" aria-label="Instagram"><i class="ph-fill ph-instagram-logo"></i></a></div>
            </div>
            <div><h3>Explore</h3><a href="{{ route('services') }}">Services</a><a href="{{ route('how') }}">How it works</a><a href="{{ route('subscriptions.create') }}">Care plans</a><a href="{{ route('coverage') }}">Iligan coverage</a><a href="{{ route('faq') }}">Frequently asked questions</a></div>
            <div><h3>Need help?</h3><a href="tel:+639171232723">0917 123 2723</a><a href="mailto:hello@icybreeze.ph">hello@icybreeze.ph</a><a href="{{ route('contact') }}">Contact us</a><span>Mon–Sat, 8 AM–5 PM</span></div>
            <div class="footer-book"><h3>Ready for cleaner air?</h3><p>Choose your service and preferred schedule in a few simple steps.</p><a class="button button-yellow" href="{{ route('booking.create') }}">Book now <i class="ph ph-arrow-right"></i></a></div>
        </div>
        <div class="site-shell footer-bottom"><span>© {{ now()->year }} IcyBreeze Aircon Cleaning · Iligan City</span><span><a href="{{ route('technician.login') }}">Technician portal</a> · <a href="{{ route('admin.login') }}">Admin portal</a></span></div>
    </footer>
</body>
</html>
