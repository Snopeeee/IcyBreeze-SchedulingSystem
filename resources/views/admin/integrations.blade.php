@extends('layouts.admin')
@php($title = 'API Integrations')

@section('content')
<div class="admin-top">
    <div>
        <span class="admin-eyebrow">Five essential services</span>
        <h1>API Integrations</h1>
        <p>Payments, weather, scheduling, air quality, and service-area verification work alongside the IcyBreeze booking system. Appointments and payment records remain in our own database.</p>
    </div>
</div>

<div class="integration-grid integration-overview">
    @foreach($integrations as $integration)
        <article class="admin-card integration-card">
            <div class="integration-card-head">
                <h2>{{ $integration['name'] }}</h2>
                <span class="status-badge {{ $integration['active'] ? 'status-paid' : 'status-pending' }}">{{ $integration['status'] }}</span>
            </div>
            <p>{{ $integration['purpose'] }}</p>
        </article>
    @endforeach
</div>

<div class="integration-detail-grid">
    <section class="admin-card integration-forecast">
        <div class="admin-card-header"><div><span class="admin-eyebrow">01 · Payment</span><h2>PayPal Sandbox</h2></div><span>Test transactions only</span></div>
        <p>Customers can try the PayPal payment flow from their private booking page using a Sandbox buyer account. No real money is charged, and Sandbox checkout is disabled in production.</p>
        @if($paypalConfigured)
            <form method="POST" action="{{ route('admin.integrations.verify-paypal') }}">@csrf<button class="button button-navy button-small" type="submit">Verify Sandbox connection</button></form>
            <small class="integration-source-note">Verification requests an access token only. It does not create an order or charge.</small>
        @else
            <p>Add Sandbox credentials through the signed localhost setup screen or local environment variables before testing checkout.</p>
        @endif
    </section>

    <section class="admin-card integration-forecast">
        <div class="admin-card-header"><div><span class="admin-eyebrow">02 · Forecast</span><h2>OpenWeather</h2></div><span>Iligan City</span></div>
        @if(!$weatherConfigured)
            <p>Add an OpenWeather API key to view rain and temperature forecasts for upcoming service windows.</p>
        @elseif($weatherError)
            <p>The forecast service is temporarily unavailable. Your appointments are unaffected; refresh this page when the connection returns.</p>
        @elseif(count($forecast) === 0)
            <p>OpenWeather is connected, but no daytime forecast windows were returned right now.</p>
        @else
            <div class="integration-weather-grid">
                @foreach($forecast as $window)
                    <div><strong>{{ $window['time'] }}</strong><span>{{ $window['temperature'] }}°C · {{ ucfirst($window['description']) }}</span><small>{{ $window['rain_chance'] }}% chance of rain</small></div>
                @endforeach
            </div>
        @endif
        <small class="integration-source-note">Forecast data: <a href="https://openweathermap.org/forecast5" target="_blank" rel="noopener noreferrer">OpenWeather</a>.</small>
    </section>

    <section class="admin-card integration-forecast">
        <div class="admin-card-header"><div><span class="admin-eyebrow">03 · Scheduling</span><h2>Google Calendar</h2></div><span class="status-badge {{ $calendarConnected ? 'status-paid' : 'status-pending' }}">{{ $calendarConnected ? 'Connected' : ($calendarConfigured ? 'Authorization needed' : 'Not configured') }}</span></div>
        <p>After reviewing an appointment, an administrator can sync its booking reference and time to the connected calendar. Customer contact details and addresses are never included in the event.</p>
        @if($calendarConfigured)
            <div class="integration-actions">
                <form method="POST" action="{{ route('admin.google-calendar.connect') }}">@csrf<button class="button button-navy button-small" type="submit">{{ $calendarConnected ? 'Reconnect Calendar' : 'Connect Calendar' }}</button></form>
                @if($calendarConnected)<form method="POST" action="{{ route('admin.google-calendar.verify') }}">@csrf<button class="button button-ghost button-small" type="submit">Verify access</button></form>@endif
            </div>
        @else
            <p>Set up a Google OAuth Web client and enable the Calendar API. Authorized callback: <code>{{ route('admin.google-calendar.callback') }}</code>.</p>
        @endif
        <small class="integration-source-note">Verification reads only the Calendar API response. Appointment syncing is a separate, manual action.</small>
    </section>

    <section class="admin-card integration-forecast">
        <div class="admin-card-header"><div><span class="admin-eyebrow">04 · Air quality</span><h2>Open-Meteo Air Quality</h2></div><span>Iligan City</span></div>
        @if($airQuality)
            <p class="integration-key-metric">AQI {{ $airQuality['aqi'] }} <span>· PM2.5 {{ $airQuality['pm25'] }} µg/m³</span></p>
            <p>Outdoor conditions help the team plan service visits. This is an outdoor estimate, not a measurement of air quality inside a customer's home.</p>
        @else
            <p>Air-quality data is temporarily unavailable. Existing bookings are unaffected.</p>
        @endif
        <small class="integration-source-note">Data: <a href="https://open-meteo.com/en/docs/air-quality-api" target="_blank" rel="noopener noreferrer">Open-Meteo / CAMS ENSEMBLE</a>. Non-commercial educational use only.</small>
    </section>

    <section class="admin-card integration-forecast">
        <div class="admin-card-header"><div><span class="admin-eyebrow">05 · Service area</span><h2>OpenStreetMap</h2></div><span>On-demand check</span></div>
        <p>Check the public location of Iligan City without sending any customer's street address to the mapping service.</p>
        <form method="POST" action="{{ route('admin.integrations.verify-service-area') }}">@csrf<button class="button button-ghost button-small" type="submit">Verify Iligan City</button></form>
        @if(session('service_area_result'))
            <p class="integration-key-metric">{{ session('service_area_result.name') }} <span>· {{ session('service_area_result.latitude') }}, {{ session('service_area_result.longitude') }}</span></p>
        @endif
        <small class="integration-source-note">Location data © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap contributors</a>; lookup via <a href="https://operations.osmfoundation.org/policies/nominatim/" target="_blank" rel="noopener noreferrer">Nominatim</a>.</small>
    </section>
</div>

<p class="integration-help">Private credentials are stored in encrypted local settings or environment variables and are never displayed here. External-service interruptions do not erase or change stored appointments.</p>
@endsection
