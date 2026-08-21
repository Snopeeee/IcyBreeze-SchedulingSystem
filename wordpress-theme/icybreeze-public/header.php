<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo esc_attr( icybreeze_meta_description() ); ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo esc_url( icybreeze_asset_uri( 'assets/images/icybreeze-favicon.png' ) ); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url( icybreeze_asset_uri( 'assets/images/icybreeze-favicon.png' ) ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'site-body' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content">Skip to content</a>

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
        <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="IcyBreeze home">
            <img src="<?php echo esc_url( icybreeze_asset_uri( 'assets/images/icybreeze-logo-compact.png' ) ); ?>" alt="IcyBreeze Aircon Cleaning">
        </a>
        <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="site-menu">
            <i class="ph ph-list"></i><span class="sr-only">Open menu</span>
        </button>
        <nav class="site-nav" id="site-menu" data-site-menu aria-label="Primary navigation">
            <a class="<?php echo is_page( 'services' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( icybreeze_page_url( 'services' ) ); ?>" <?php echo is_page( 'services' ) ? 'aria-current="page"' : ''; ?>>Services</a>
            <a class="<?php echo is_page( 'how-it-works' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( icybreeze_page_url( 'how-it-works' ) ); ?>" <?php echo is_page( 'how-it-works' ) ? 'aria-current="page"' : ''; ?>>How it works</a>
            <a href="<?php echo esc_url( icybreeze_booking_url( 'subscriptions' ) ); ?>">Care plans</a>
            <a class="<?php echo is_page( 'coverage' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( icybreeze_page_url( 'coverage' ) ); ?>" <?php echo is_page( 'coverage' ) ? 'aria-current="page"' : ''; ?>>Coverage</a>
            <a class="<?php echo is_page( 'faq' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( icybreeze_page_url( 'faq' ) ); ?>" <?php echo is_page( 'faq' ) ? 'aria-current="page"' : ''; ?>>FAQ</a>
            <a class="nav-cta" href="<?php echo esc_url( icybreeze_booking_url( 'book' ) ); ?>"><i class="ph ph-calendar-check"></i> Book a cleaning</a>
        </nav>
    </div>
</header>

<main id="main-content">
