@extends('layouts.site')
@php($title = 'Aircon Cleaning Prices')

@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Services and pricing</span><h1>Professional Aircon Cleaning</h1><p>Detailed cleaning for supported window and split-type units. Rates are determined by unit type and do not vary by horsepower.</p></div></section>

<section class="section">
    <div class="site-shell"><x-service-pricing :service="$service" :unit-types="$unitTypes" /></div>
</section>

<section class="section section-ice"><div class="site-shell coverage-callout"><div><span class="eyebrow">Supported unit types</span><h2>Schedule service for window or split-type units</h2><p>Select whether the unit is inverter or non-inverter. The booking system calculates the applicable total before confirmation.</p></div><div class="coverage-actions"><a class="button button-cyan" href="{{ route('booking.create') }}">Schedule a Service</a><a class="button button-ghost" href="{{ route('contact') }}">Contact IcyBreeze</a></div></div></section>
@endsection
