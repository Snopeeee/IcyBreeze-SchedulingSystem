@extends('layouts.site')
@php($title = 'Maintenance Plans | Coming Soon')

@section('content')
<section class="page-hero maintenance-coming-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">Future service offering</span>
        <h1>Maintenance Plans Are Coming Soon</h1>
        <p>We are carefully developing recurring maintenance options for IcyBreeze customers. Enrollment is not yet available while plan details, pricing, and service arrangements are being finalized.</p>
    </div>
</section>

<section class="section coming-soon-section">
    <div class="site-shell coming-soon-layout">
        <div class="coming-soon-panel">
            <div class="coming-soon-mark" aria-hidden="true"><i class="ph ph-clock-countdown"></i></div>
            <span class="eyebrow">In development</span>
            <h2>Designed for dependable, year-round care</h2>
            <p>Our planned maintenance program will make it easier to arrange regular aircon cleaning throughout the year. The options below are previews only and may change before the official launch.</p>
            <div class="launch-note" role="note">
                <i class="ph-fill ph-info"></i>
                <div><strong>Not yet available</strong><span>We are not accepting maintenance-plan requests or quoting plan discounts at this time.</span></div>
            </div>
            <div class="coming-soon-actions">
                <a class="button button-yellow" href="{{ route('booking.create') }}">Schedule a One-Time Cleaning <i class="ph ph-arrow-right"></i></a>
                <a class="button button-ghost" href="{{ route('contact') }}">Contact IcyBreeze</a>
            </div>
        </div>

        <div class="upcoming-plan-grid" aria-label="Planned maintenance options">
            <article class="upcoming-plan-card">
                <div class="upcoming-plan-head"><div class="plan-icon"><i class="ph ph-arrows-clockwise"></i></div><span class="upcoming-plan-status">Coming Soon</span></div>
                <span class="plan-kicker">Planned option</span>
                <h3>Quarterly Care</h3>
                <p>A proposed recurring schedule intended for aircon units that receive frequent use.</p>
                <div class="upcoming-plan-meta"><i class="ph ph-calendar-dots"></i><span>Frequency and pricing under review</span></div>
            </article>
            <article class="upcoming-plan-card">
                <div class="upcoming-plan-head"><div class="plan-icon"><i class="ph ph-calendar-check"></i></div><span class="upcoming-plan-status">Coming Soon</span></div>
                <span class="plan-kicker">Planned option</span>
                <h3>Biannual Care</h3>
                <p>A proposed recurring schedule intended for aircon units with moderate household use.</p>
                <div class="upcoming-plan-meta"><i class="ph ph-calendar-dots"></i><span>Frequency and pricing under review</span></div>
            </article>
        </div>
    </div>
</section>
@endsection
