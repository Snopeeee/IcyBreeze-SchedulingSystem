@extends('layouts.admin')
@php($title = 'API Integrations')

@section('content')
<div class="admin-top"><div><span class="admin-eyebrow">Connected services</span><h1>API Integrations</h1><p>Five services support payments, customer messages, address checks, and service planning.</p></div></div>

<div class="integration-grid">
    @foreach($integrations as $integration)
        <article class="admin-card integration-card">
            <div class="integration-card-head"><h2>{{ $integration['name'] }}</h2><span class="status-badge {{ $integration['active'] ? 'status-paid' : 'status-pending' }}">{{ $integration['active'] ? 'Credentials set' : 'Setup needed' }}</span></div>
            <p>{{ $integration['purpose'] }}</p>
        </article>
    @endforeach
</div>

<section class="admin-card integration-forecast">
    <div class="admin-card-header"><div><span class="admin-eyebrow">Iligan City</span><h2>Service Planning Forecast</h2></div><span>Source: OpenWeather</span></div>
    @if(!$integrations[4]['active'])
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

<p class="integration-help">Credentials belong in local environment settings or Railway service variables. No API keys are shown on this page.</p>
@endsection
