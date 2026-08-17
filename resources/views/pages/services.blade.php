@extends('layouts.site')
@php($title = 'Standard Aircon Cleaning Prices')

@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Clear care, exact prices</span><h1>Standard aircon cleaning</h1><p>Every booking receives the same careful Standard Cleaning. Your price depends only on the aircon unit type—not horsepower.</p></div></section>

<section class="section">
    <div class="site-shell pricing-showcase">
        <article class="service-card standard-service-card">
            <div class="service-card-top"><div class="service-icon"><i class="ph ph-wind"></i></div><span class="popular-label">Standard Cleaning only</span></div>
            <h3>{{ $service->name }}</h3>
            <p>{{ $service->description }}</p>
            <ul><li><i class="ph-fill ph-check-circle"></i> Professional cleaning and inspection</li><li><i class="ph-fill ph-check-circle"></i> Drainage and performance test</li><li><i class="ph-fill ph-check-circle"></i> About {{ $service->duration_minutes }} minutes for the first unit</li></ul>
            <a class="button button-navy" href="{{ route('booking.create') }}">Choose your unit type <i class="ph ph-arrow-right"></i></a>
        </article>
        <div class="unit-price-grid">
            @foreach($unitTypes as $unitType)
                <article class="unit-price-card">
                    <div><span>Standard Cleaning</span><h3>{{ $unitType->name }}</h3></div>
                    <strong>{{ $unitType->formatted_price }}</strong>
                </article>
            @endforeach
            <p class="price-note"><i class="ph ph-map-pin"></i> Regular prices are per unit and apply throughout Iligan City.</p>
        </div>
    </div>
</section>

<section class="section section-ice"><div class="site-shell coverage-callout"><div><span class="eyebrow">Have a window or split unit?</span><h2>Pick a convenient Iligan schedule.</h2><p>Tell us whether the unit is inverter or non-inverter and the booking form calculates the total instantly.</p></div><div class="coverage-actions"><a class="button button-cyan" href="{{ route('booking.create') }}">Book now</a><a class="button button-ghost" href="{{ route('contact') }}">Ask IcyBreeze</a></div></div></section>
@endsection
