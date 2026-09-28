@extends('layouts.site')

@section('content')
<section class="hero">
    <div class="site-shell">
        <div class="hero-content">
            <span class="hero-badge"><i class="ph-fill ph-snowflake"></i> Professional aircon service in Iligan City</span>
            <h1>Professional aircon <br><em>cleaning services.</em></h1>
            <p class="hero-copy">Reliable cleaning for residential and commercial air-conditioning units.</p>
            <div class="hero-assurances">
                <span><i class="ph ph-shield-check"></i><strong>Thorough cleaning<br>and inspection</strong></span>
                <span><i class="ph ph-seal-check"></i><strong>Transparent pricing<br>and reliable service</strong></span>
            </div>
            <div class="hero-actions"><a class="button button-navy" href="{{ route('booking.create') }}">Schedule a Service <i class="ph ph-arrow-right"></i></a></div>
            <div class="hero-price-row">
                <span>Cleaning services from <strong>{{ $unitTypes->first()?->formatted_price ?? "\u{20B1}700" }}</strong></span>
                <span class="coverage-pill"><i class="ph-fill ph-map-pin"></i> Available throughout Iligan City</span>
            </div>
        </div>
    </div>
</section>

<section class="booking-band">
    <div class="site-shell">
        <h2>How to schedule your service</h2>
        <div class="booking-band-grid">
            <div class="booking-band-step"><div class="booking-band-icon"><i class="ph ph-calendar-check"></i></div><div><strong>1. Select a service schedule</strong><span>Choose an available date and arrival window.</span></div></div>
            <i class="ph ph-caret-right booking-band-arrow"></i>
            <div class="booking-band-step"><div class="booking-band-icon"><i class="ph ph-clipboard-text"></i></div><div><strong>2. Provide service information</strong><span>Enter your contact details and Iligan City address.</span></div></div>
            <i class="ph ph-caret-right booking-band-arrow"></i>
            <div class="booking-band-step"><div class="booking-band-icon"><i class="ph ph-check-circle"></i></div><div><strong>3. Receive professional service</strong><span>Our team completes the cleaning and operational check.</span></div></div>
        </div>
    </div>
</section>

<section class="section section-soft" id="services">
    <div class="site-shell">
        <div class="section-heading"><span class="eyebrow">Services and pricing</span><h2>Professional cleaning with transparent pricing</h2><p>Select your unit type to view the applicable rate. Every appointment includes cleaning, drainage inspection, and an operational cooling check.</p></div>
        <x-service-pricing :service="$service" :unit-types="$unitTypes" />
    </div>
</section>

<section class="section subscription-section" id="plans">
    <div class="site-shell subscription-layout">
        <div class="subscription-copy">
            <span class="eyebrow">Maintenance plans</span>
            <h2>Scheduled maintenance throughout the year</h2>
            <p>Select a recurring schedule based on your usage. Our office reviews the request and confirms each service visit.</p>
            <ul class="check-list"><li><i class="ph-fill ph-check-circle"></i> Quarterly or biannual service options</li><li><i class="ph-fill ph-check-circle"></i> 5–10% plan discount per completed visit</li><li><i class="ph-fill ph-check-circle"></i> Schedule adjustments coordinated through our office</li></ul>
            <a class="button button-navy" href="{{ route('subscriptions.create') }}">View Maintenance Plans <i class="ph ph-arrow-right"></i></a>
        </div>
        <div class="plan-stack">
            <article class="plan-card plan-card-featured"><span class="plan-kicker">Recommended for frequent use</span><div class="plan-icon"><i class="ph ph-arrows-clockwise"></i></div><h3>Quarterly Care</h3><p>Four scheduled cleanings each year for regularly used units.</p><div class="plan-saving"><strong>Save 10%</strong><span>per visit</span></div></article>
            <article class="plan-card"><span class="plan-kicker">Suitable for moderate use</span><div class="plan-icon"><i class="ph ph-calendar-dots"></i></div><h3>Biannual Care</h3><p>Two scheduled cleanings each year for moderately used units.</p><div class="plan-saving"><strong>Save 5%</strong><span>per visit</span></div></article>
        </div>
    </div>
</section>

<section class="section section-ice">
    <div class="site-shell">
        <div class="section-heading center"><span class="eyebrow">Customer information</span><h2>Service information and customer support</h2><p>Review pricing, understand the service process, request recurring maintenance, or consult our frequently asked questions.</p></div>
        <div class="explore-grid">
            <a class="explore-card" href="{{ route('services') }}"><i class="ph ph-broom"></i><span><strong>Review services and pricing</strong><small>Compare rates for each supported unit type.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="{{ route('how') }}"><i class="ph ph-list-checks"></i><span><strong>Review the service process</strong><small>Understand each stage of the appointment.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="{{ route('subscriptions.create') }}"><i class="ph ph-arrows-clockwise"></i><span><strong>Request a maintenance plan</strong><small>Arrange recurring cleaning services.</small></span><i class="ph ph-arrow-up-right"></i></a>
            <a class="explore-card" href="{{ route('faq') }}"><i class="ph ph-chat-circle-dots"></i><span><strong>Read frequently asked questions</strong><small>Review scheduling, preparation, and payment information.</small></span><i class="ph ph-arrow-up-right"></i></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="site-shell why-grid">
        <div class="why-visual"><div class="why-visual-card"><strong>Professional service quality</strong><span>Thorough cleaning, improved airflow, and documented service details.</span></div></div>
        <div>
            <div class="section-heading" style="margin-bottom:0"><span class="eyebrow">Why choose IcyBreeze</span><h2>Reliable service for cleaner indoor comfort</h2><p>Our process provides clear pricing, convenient scheduling, and careful service from booking through completion.</p></div>
            <div class="feature-list">
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-user-focus"></i></div><div><h3>Thorough aircon cleaning</h3><p>Service includes filters, coils, accessible components, drainage, and final cooling verification.</p></div></div>
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-map-trifold"></i></div><div><h3>Structured appointment scheduling</h3><p>Select a live arrival window and provide complete service-location details.</p></div></div>
                <div class="feature-row"><div class="feature-icon"><i class="ph ph-lock-key"></i></div><div><h3>Secure appointment management</h3><p>Use your private booking link to review appointment details and available cancellation options.</p></div></div>
            </div>
        </div>
    </div>
</section>

@php($facebookUpdates = config('business.facebook_updates', []))
<section class="section section-soft updates-section" aria-labelledby="facebook-updates-heading">
    <div class="site-shell">
        <div class="updates-heading">
            <div class="section-heading">
                <span class="eyebrow">Latest updates</span>
                <h2 id="facebook-updates-heading">Recent work and aircon care information</h2>
                <p>Current public updates from the official IcyBreeze Aircon Cleaning Facebook page.</p>
            </div>
            <a class="button button-ghost" href="{{ config('business.facebook_url') }}" target="_blank" rel="noopener noreferrer"><i class="ph-fill ph-facebook-logo"></i> Follow IcyBreeze on Facebook</a>
        </div>
        <div class="updates-grid">
            @foreach($facebookUpdates as $update)
                <article class="social-update{{ !empty($update['featured']) ? ' social-update-featured' : '' }}">
                    <div class="social-update-topline">
                        <span class="social-update-icon"><i class="ph {{ $update['icon'] }}"></i></span>
                        <span class="social-update-type">{{ $update['type'] }}</span>
                    </div>
                    <time datetime="{{ \Illuminate\Support\Carbon::parse($update['date'])->toDateString() }}">{{ $update['date'] }}</time>
                    <h3>{{ $update['title'] }}</h3>
                    <p>{{ $update['summary'] }}</p>
                    <a href="{{ $update['url'] }}" target="_blank" rel="noopener noreferrer">View original Facebook post <i class="ph ph-arrow-up-right"></i></a>
                </article>
            @endforeach
        </div>
        <p class="updates-source"><i class="ph ph-info"></i> Updates shown here were verified against the public Facebook page on September 28, 2026.</p>
    </div>
</section>

@php($customerReviews = config('business.reviews', []))
<section class="section reviews-section" aria-labelledby="customer-recommendations-heading" data-review-carousel data-autoplay-interval="6500" aria-roledescription="carousel">
    <div class="site-shell">
        <div class="reviews-heading">
            <div class="section-heading"><span class="eyebrow">Customer recommendations</span><h2 id="customer-recommendations-heading">Recommended by customers in Iligan City</h2><p>All four public recommendations currently shared on the official IcyBreeze Aircon Cleaning Facebook page.</p></div>
            <a class="button button-ghost" href="{{ rtrim(config('business.facebook_url'), '/') }}/reviews" target="_blank" rel="noopener noreferrer"><i class="ph-fill ph-facebook-logo"></i> View Facebook Reviews</a>
        </div>
        <div class="review-carousel">
            <div class="review-carousel-stage">
                <div class="review-carousel-track" data-review-track>
                    @foreach($customerReviews as $review)
                        <article class="customer-review{{ $loop->first ? ' is-active' : '' }}" data-review-slide data-reviewer="{{ $review['name'] }}" aria-roledescription="slide" aria-label="Recommendation {{ $loop->iteration }} of {{ count($customerReviews) }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                            <div class="review-card-topline">
                                <div class="review-mark"><i class="ph-fill ph-quotes"></i></div>
                                <span class="review-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad((string) count($customerReviews), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <blockquote>&ldquo;{{ $review['quote'] }}&rdquo;</blockquote>
                            <div class="review-author"><strong>{{ $review['name'] }}</strong><span><i class="ph-fill ph-check-circle"></i> Recommends IcyBreeze Aircon Cleaning</span></div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="review-carousel-footer">
                <div class="review-position"><strong data-review-status>1 of {{ count($customerReviews) }}</strong><span>Public Facebook recommendations</span></div>
                <div class="review-pagination" aria-label="Choose a customer recommendation">
                    @foreach($customerReviews as $review)
                        <button type="button" class="review-dot{{ $loop->first ? ' is-active' : '' }}" data-review-dot data-slide-index="{{ $loop->index }}" aria-label="Show recommendation {{ $loop->iteration }} from {{ $review['name'] }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
                <div class="review-controls">
                    <button type="button" class="review-control" data-review-previous aria-label="Show previous recommendation"><i class="ph ph-arrow-left"></i></button>
                    <button type="button" class="review-control" data-review-autoplay aria-label="Pause automatic rotation" aria-pressed="false"><i class="ph ph-pause"></i></button>
                    <button type="button" class="review-control" data-review-next aria-label="Show next recommendation"><i class="ph ph-arrow-right"></i></button>
                </div>
            </div>
            <p class="sr-only" data-review-announcement aria-live="polite"></p>
        </div>
        <p class="review-source">Customer wording has been lightly edited for capitalization, clarity, and readability. Source: public recommendations on the official IcyBreeze Facebook page.</p>
    </div>
</section>

<section class="section section-soft">
    <div class="site-shell">
        <div class="section-heading center"><span class="eyebrow">Maintenance guidance</span><h2>Recommended practices between professional cleanings</h2><p>Routine observation and a clear service area can help maintain airflow and identify concerns early.</p></div>
        <div class="care-grid">
            <article class="care-card"><span>01</span><i class="ph ph-wind"></i><h3>Monitor airflow</h3><p>Reduced airflow, unusual odors, or water leakage may indicate that service is required.</p></article>
            <article class="care-card"><span>02</span><i class="ph ph-broom"></i><h3>Keep the unit area clear</h3><p>Maintain adequate clearance around indoor and outdoor units to support unobstructed airflow.</p></article>
            <article class="care-card"><span>03</span><i class="ph ph-calendar-heart"></i><h3>Maintain a regular schedule</h3><p>Frequently used units benefit from scheduled professional cleaning before performance declines.</p></article>
        </div>
    </div>
</section>

<section class="section section-ice">
    <div class="site-shell coverage-callout">
        <div><span class="eyebrow">Iligan City service area</span><h2>Professional aircon cleaning throughout Iligan City</h2><p>One-time appointments and maintenance plans are available Monday through Saturday.</p></div>
        <div class="coverage-actions"><a class="button button-cyan" href="{{ route('booking.create') }}">View Available Schedules</a><a class="button button-ghost" href="{{ route('coverage') }}">Review Service Area</a></div>
    </div>
</section>
@endsection
