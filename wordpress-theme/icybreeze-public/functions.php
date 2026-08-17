<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ICYBREEZE_THEME_VERSION', '1.0.0' );

function icybreeze_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'icybreeze_theme_setup' );

function icybreeze_asset_uri( $path = '' ) {
    return trailingslashit( get_template_directory_uri() ) . ltrim( $path, '/' );
}

function icybreeze_booking_url( $path = '' ) {
    $base = defined( 'ICYBREEZE_BOOKING_URL' ) ? ICYBREEZE_BOOKING_URL : 'http://127.0.0.1:8080';
    return untrailingslashit( $base ) . '/' . ltrim( $path, '/' );
}

function icybreeze_page_url( $slug ) {
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

function icybreeze_enqueue_assets() {
    $build_uri = icybreeze_asset_uri( 'assets/build/' );
    $build_dir = get_template_directory() . '/assets/build/';

    $app_css = 'app-Dw0Wgvd3.css';
    $vendor_css = 'app-Cy4NUfxW.css';

    if ( $vendor_css ) {
        wp_enqueue_style(
            'icybreeze-fonts-icons',
            $build_uri . basename( $vendor_css ),
            array(),
            file_exists( $build_dir . basename( $vendor_css ) ) ? filemtime( $build_dir . basename( $vendor_css ) ) : ICYBREEZE_THEME_VERSION
        );
    }

    if ( $app_css ) {
        wp_enqueue_style(
            'icybreeze-app',
            $build_uri . basename( $app_css ),
            $vendor_css ? array( 'icybreeze-fonts-icons' ) : array(),
            file_exists( $build_dir . basename( $app_css ) ) ? filemtime( $build_dir . basename( $app_css ) ) : ICYBREEZE_THEME_VERSION
        );
    }

    $theme_css = get_template_directory() . '/assets/css/wordpress.css';
    wp_enqueue_style(
        'icybreeze-wordpress',
        icybreeze_asset_uri( 'assets/css/wordpress.css' ),
        $app_css ? array( 'icybreeze-app' ) : array(),
        file_exists( $theme_css ) ? filemtime( $theme_css ) : ICYBREEZE_THEME_VERSION
    );

    $theme_js = get_template_directory() . '/assets/js/theme.js';
    wp_enqueue_script(
        'icybreeze-theme',
        icybreeze_asset_uri( 'assets/js/theme.js' ),
        array(),
        file_exists( $theme_js ) ? filemtime( $theme_js ) : ICYBREEZE_THEME_VERSION,
        true
    );

    $hero = esc_url_raw( icybreeze_asset_uri( 'assets/images/icybreeze-hero.png' ) );
    $mark = esc_url_raw( icybreeze_asset_uri( 'assets/images/icybreeze-brand-mark.png' ) );
    wp_add_inline_style(
        'icybreeze-wordpress',
        ':root{--icybreeze-hero-image:url("' . $hero . '");--icybreeze-brand-mark:url("' . $mark . '");}'
    );
}
add_action( 'wp_enqueue_scripts', 'icybreeze_enqueue_assets' );

function icybreeze_remove_block_styles() {
    wp_dequeue_style( 'global-styles' );
    wp_dequeue_style( 'classic-theme-styles' );
    wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_enqueue_scripts', 'icybreeze_remove_block_styles', 100 );

function icybreeze_seed_public_pages() {
    $pages = array(
        'home'         => 'Home',
        'services'     => 'Services',
        'how-it-works' => 'How It Works',
        'coverage'     => 'Coverage',
        'faq'          => 'Frequently Asked Questions',
        'contact'      => 'Contact',
    );

    $page_ids = array();

    foreach ( $pages as $slug => $title ) {
        $page = get_page_by_path( $slug );

        if ( ! $page ) {
            $page_id = wp_insert_post(
                array(
                    'post_title'   => $title,
                    'post_name'    => $slug,
                    'post_type'    => 'page',
                    'post_status'  => 'publish',
                    'post_content' => '',
                )
            );
        } else {
            $page_id = $page->ID;
        }

        if ( ! is_wp_error( $page_id ) ) {
            $page_ids[ $slug ] = (int) $page_id;
        }
    }

    if ( ! empty( $page_ids['home'] ) ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_ids['home'] );
    }

    update_option( 'permalink_structure', '/%postname%/' );
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'icybreeze_seed_public_pages' );

function icybreeze_meta_description() {
    $descriptions = array(
        'services'     => 'See exact IcyBreeze standard aircon cleaning prices by unit type in Iligan City.',
        'how-it-works' => 'Learn how an IcyBreeze aircon cleaning appointment works from booking through the final cooling check.',
        'coverage'     => 'IcyBreeze provides one-time and recurring aircon cleaning throughout Iligan City.',
        'faq'          => 'Answers about IcyBreeze scheduling, aircon cleaning, preparation, recurring care, GPS consent, and payment.',
        'contact'      => 'Contact the IcyBreeze Iligan City team about schedules, cleaning, care plans, or service coverage.',
    );

    foreach ( $descriptions as $slug => $description ) {
        if ( is_page( $slug ) ) {
            return $description;
        }
    }

    return 'Professional aircon cleaning and recurring care plans for homes in Iligan City.';
}
