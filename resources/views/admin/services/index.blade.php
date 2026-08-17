@extends('layouts.admin')
@php($title = 'Service Pricing')

@section('content')
<div class="admin-top">
    <div><h1>Standard Cleaning</h1><p>Manage the one cleaning service and its aircon unit pricing.</p></div>
    <span class="admin-date"><i class="ph ph-check-circle"></i> Standard Cleaning only</span>
</div>

@if($errors->any())
    <div class="flash" style="margin:0 0 18px;color:#9b3a3a;background:#fff0f0;border:1px solid #f2caca"><i class="ph-fill ph-warning-circle"></i>{{ $errors->first() }}</div>
@endif

<section class="service-admin-card">
    <form class="admin-form" method="POST" action="{{ route('admin.services.update', $service) }}">
        @csrf
        @method('PATCH')
        <div class="field-grid">
            <div><label>Service name</label><input value="Standard Cleaning" disabled></div>
            <div><label>Availability</label><input value="Window and split-type units" disabled></div>
        </div>
        <div><label for="short_description">Short description</label><input id="short_description" name="short_description" value="{{ old('short_description', $service->short_description) }}" required></div>
        <div><label for="description">Full description</label><textarea id="description" name="description" required>{{ old('description', $service->description) }}</textarea></div>
        <div class="field-grid">
            <div><label for="duration_minutes">Minutes for the first unit</label><input id="duration_minutes" type="number" min="30" max="480" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" required></div>
            <div><label for="buffer_minutes">Schedule buffer (minutes)</label><input id="buffer_minutes" type="number" min="0" max="180" name="buffer_minutes" value="{{ old('buffer_minutes', $service->buffer_minutes) }}" required></div>
        </div>

        <div class="admin-rate-heading"><div><h2>Regular price list</h2><p>Gross is calculated as customer price minus technician share.</p></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table rate-admin-table">
                <thead><tr><th>Aircon unit type</th><th>Customer price</th><th>Technician share</th><th>Gross</th></tr></thead>
                <tbody>
                    @foreach($unitTypes as $unitType)
                        @php($price = old("rates.{$unitType->id}.price", $unitType->price_centavos / 100))
                        @php($share = old("rates.{$unitType->id}.technician_share", $unitType->technician_share_centavos / 100))
                        <tr>
                            <td><strong>{{ $unitType->name }}</strong><span>Standard Cleaning</span></td>
                            <td><div class="money-input"><span>₱</span><input type="number" min="1" step=".01" name="rates[{{ $unitType->id }}][price]" value="{{ $price }}" required></div></td>
                            <td><div class="money-input"><span>₱</span><input type="number" min="0" step=".01" name="rates[{{ $unitType->id }}][technician_share]" value="{{ $share }}" required></div></td>
                            <td><strong>{{ $unitType->formatted_gross }}</strong><span>Current configured gross</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button class="button button-navy button-small">Save service and pricing</button>
    </form>
</section>
@endsection
