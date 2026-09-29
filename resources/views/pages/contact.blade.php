@extends('layouts.site')
@php($title = 'Contact IcyBreeze')
@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Customer support</span><h1>Contact IcyBreeze</h1><p>Contact our office for assistance with cleaning services, future maintenance-plan updates, or an existing appointment.</p></div></section>
<section class="section contact-section">
    <div class="site-shell">
        <article class="contact-primary">
            <div class="contact-primary-copy">
                <span class="eyebrow">Official customer service channels</span>
                <h2>Professional aircon cleaning services in Iligan City</h2>
                <p>IcyBreeze is based in Tambacan and serves customers throughout Iligan City. Contact us by phone, email, Facebook, or Instagram for scheduling and service assistance.</p>
                <div class="contact-actions">
                    <a class="button button-yellow" href="tel:{{ config('business.phone_e164') }}"><i class="ph ph-phone-call"></i> Contact by Phone</a>
                    <a class="button contact-button-light" href="{{ config('business.facebook_url') }}" target="_blank" rel="noopener noreferrer"><i class="ph-fill ph-facebook-logo"></i> Message on Facebook</a>
                </div>
            </div>
            <div class="contact-profile-list" aria-label="IcyBreeze business profile">
                <div><i class="ph-fill ph-map-pin"></i><span><small>Business location</small><strong>{{ config('business.base_location') }}</strong></span></div>
                <div><i class="ph ph-map-trifold"></i><span><small>Service coverage</small><strong>{{ config('business.service_area') }} only</strong></span></div>
                <div><i class="ph ph-clock"></i><span><small>Service hours</small><strong>{{ config('business.hours_short') }}</strong></span></div>
            </div>
        </article>

        <div class="contact-grid">
            <article class="info-card"><div class="feature-icon"><i class="ph ph-phone-call"></i></div><h2>Phone</h2><p>For service inquiries and appointment changes.</p><a class="text-link" href="tel:{{ config('business.phone_e164') }}">{{ config('business.phone_display') }} <i class="ph ph-arrow-up-right"></i></a></article>
            <article class="info-card"><div class="feature-icon"><i class="ph ph-envelope-simple"></i></div><h2>Email</h2><p>For general business inquiries and future maintenance-plan updates.</p><a class="text-link contact-email" href="mailto:{{ config('business.email') }}">{{ config('business.email') }} <i class="ph ph-arrow-up-right"></i></a></article>
            <article class="info-card"><div class="feature-icon"><i class="ph-fill ph-facebook-logo"></i></div><h2>Facebook</h2><p>Visit our official page for updates or send us a message.</p><a class="text-link" href="{{ config('business.facebook_url') }}" target="_blank" rel="noopener noreferrer">{{ config('business.facebook_handle') }} <i class="ph ph-arrow-up-right"></i></a></article>
            <article class="info-card"><div class="feature-icon"><i class="ph ph-instagram-logo"></i></div><h2>Instagram</h2><p>Follow our official account for IcyBreeze updates.</p><a class="text-link" href="{{ config('business.instagram_url') }}" target="_blank" rel="noopener noreferrer">{{ config('business.instagram_handle') }} <i class="ph ph-arrow-up-right"></i></a></article>
            <article class="info-card"><div class="feature-icon"><i class="ph ph-clock"></i></div><h2>Service hours</h2><p>{{ config('business.hours_full') }}</p><a class="text-link" href="{{ route('booking.create') }}">View available schedules <i class="ph ph-arrow-right"></i></a></article>
            <article class="info-card"><div class="feature-icon"><i class="ph ph-map-pin"></i></div><h2>Iligan coverage</h2><p>Based in Tambacan and serving customers across Iligan City.</p><a class="text-link" href="{{ route('coverage') }}">Explore our service area <i class="ph ph-arrow-right"></i></a></article>
        </div>
    </div>
</section>
@endsection
