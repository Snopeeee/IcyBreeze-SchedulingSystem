@extends('layouts.admin')
@php($title = 'API Integrations')

@section('content')
<div class="admin-top"><div><span class="admin-eyebrow">Connected services</span><h1>API Integrations</h1><p>Five external APIs support appointment planning and service-area checks. Provider data is advisory; bookings remain authoritative in our database.</p></div></div>

<div class="integration-grid">
    @foreach($integrations as $integration)
        <article class="admin-card integration-card">
            <div class="integration-card-head"><h2>{{ $integration['name'] }}</h2><span class="status-badge {{ $integration['active'] === true ? 'status-paid' : 'status-pending' }}">{{ $integration['active'] === null ? 'Check on demand' : ($integration['active'] ? 'Connected' : 'Unavailable') }}</span></div>
            <p>{{ $integration['purpose'] }}</p>
        </article>
    @endforeach
</div>

<section class="admin-card integration-forecast">
    <div class="admin-card-header"><div><span class="admin-eyebrow">Iligan City</span><h2>Service Planning Forecast</h2></div><span>Source: OpenWeather</span></div>
    @if(!$integrations[0]['active'] && !$weatherError)
        <p>Add an OpenWeather API key to view forecast conditions for upcoming service windows.</p>
    @elseif($weatherError)
        <p>The forecast is temporarily unavailable. Existing appointments are unaffected.</p>
    @elseif(count($forecast) === 0)
        <p>There are no daytime forecast windows available right now.</p>
    @else
        <div class="integration-weather-grid">
            @foreach($forecast as $window)
                <div><strong>{{ $window['time'] }}</strong><span>{{ $window['temperature'] }}°C · {{ ucfirst($window['description']) }}</span><small>{{ $window['rain_chance'] }}% chance of rain</small></div>
            @endforeach
        </div>
    @endif
</section>

<div class="integration-grid">
    <section class="admin-card integration-card"><h2>Air quality today</h2>
        @if($planningData['airQuality'])<p><strong>AQI {{ $planningData['airQuality']['aqi'] }}</strong> · PM2.5 {{ $planningData['airQuality']['pm25'] }} µg/m³</p><small>Outdoor estimate only; not an indoor air-quality measurement.</small>
        @else<p>Air-quality data is temporarily unavailable.</p>@endif
        <small>Data: <a href="https://open-meteo.com/en/docs/air-quality-api" target="_blank" rel="noopener noreferrer">Open-Meteo / CAMS ENSEMBLE</a>. Free endpoint is for non-commercial school demonstration.</small>
    </section>
    <section class="admin-card integration-card"><h2>Upcoming public holidays</h2>
        @forelse($planningData['upcomingHolidays'] ?? [] as $holiday)<p><strong>{{ \Carbon\Carbon::parse($holiday['date'])->format('M j') }}</strong> · {{ $holiday['name'] }}</p>
        @empty<p>No upcoming holidays returned for the current year.</p>@endforelse
        <small>Source: <a href="https://date.nager.at/Api" target="_blank" rel="noopener noreferrer">Nager.Date</a>. Confirm local observances separately.</small>
    </section>
    <section class="admin-card integration-card"><h2>Daylight in Iligan</h2>
        @if($planningData['daylight'])<p><strong>Sunrise {{ $planningData['daylight']['sunrise'] }}</strong><br>Sunset {{ $planningData['daylight']['sunset'] }}</p>
        @else<p>Daylight times are temporarily unavailable.</p>@endif
        <small>Source: <a href="https://sunrise-sunset.org/api" target="_blank" rel="noopener noreferrer">Sunrise-Sunset.org</a>.</small>
    </section>
    <section class="admin-card integration-card"><h2>Service-area map check</h2><p>Look up the public city location only. Customer street addresses are never sent to OpenStreetMap.</p>
        <form method="POST" action="{{ route('admin.integrations.verify-service-area') }}">@csrf<button class="button button-ghost button-small" type="submit">Verify Iligan City</button></form>
        @if(session('service_area_result'))<p><strong>{{ session('service_area_result.name') }}</strong><br>{{ session('service_area_result.latitude') }}, {{ session('service_area_result.longitude') }}</p>@endif
        <small>© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap contributors</a> · <a href="https://operations.osmfoundation.org/policies/nominatim/" target="_blank" rel="noopener noreferrer">Nominatim usage policy</a>.</small>
    </section>
</div>

<section class="admin-card integration-forecast"><div class="admin-card-header"><div><span class="admin-eyebrow">Optional provider setup</span><h2>Additional integrations</h2></div></div><div class="integration-grid">
@foreach($optionalIntegrations as $integration)
<div><strong>{{ $integration['name'] }}</strong> <span class="status-badge {{ $integration['active'] ? 'status-paid' : 'status-pending' }}">{{ $integration['active'] ? 'Configured' : 'Not configured' }}</span><p>{{ $integration['purpose'] }}</p></div>
@endforeach
</div><p>PayPal sandbox uses test money only. Never present sandbox transactions as real customer payments.</p></section>

<p class="integration-help">Credentialed integrations use local environment settings or Railway service variables. No API keys are shown on this page. External API outages do not affect stored appointments.</p>
@endsection
