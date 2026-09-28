@extends('layouts.site')
@php($title = 'Aircon Care Plans')

@section('content')
<section class="page-hero">
    <div class="site-shell page-intro">
        <span class="eyebrow">Recurring maintenance</span>
        <h1>Scheduled Aircon Maintenance</h1>
        <p>Select a recurring cleaning plan for your property. Our office will review your preferences and confirm the initial appointment.</p>
    </div>
</section>

<section class="section subscription-form-section">
    <div class="site-shell">
        <form class="care-request-layout" method="POST" action="{{ route('subscriptions.store') }}" data-subscription-form>
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <div class="care-request-main">
                @if($errors->any())
                    <div class="flash flash-error" role="alert"><div><strong>Please review your request</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
                @endif
                <section class="form-surface" aria-labelledby="plan-heading">
                    <div class="form-section-heading"><span class="section-number">01</span><div><h2 id="plan-heading">Select a maintenance plan</h2><p>Each plan provides the same professional cleaning service at a defined interval.</p></div></div>
                    <fieldset class="choice-fieldset">
                        <legend class="sr-only">Cleaning frequency</legend>
                        <div class="payment-options plan-options">
                            <div class="payment-option">
                                <input id="plan-quarterly" type="radio" name="plan" value="quarterly_care" data-discount=".90" data-label="Quarterly Care" data-frequency="Every 3 months · 4 visits a year" @checked(old('plan','quarterly_care') === 'quarterly_care') required>
                                <label for="plan-quarterly"><span class="plan-option-content"><span class="plan-kicker">Save 10% per visit</span><strong>Quarterly Care</strong><span>Every 3 months · 4 visits a year</span></span><i class="ph ph-check-circle" aria-hidden="true"></i></label>
                            </div>
                            <div class="payment-option">
                                <input id="plan-biannual" type="radio" name="plan" value="biannual_care" data-discount=".95" data-label="Biannual Care" data-frequency="Every 6 months · 2 visits a year" @checked(old('plan') === 'biannual_care') required>
                                <label for="plan-biannual"><span class="plan-option-content"><span class="plan-kicker">Save 5% per visit</span><strong>Biannual Care</strong><span>Every 6 months · 2 visits a year</span></span><i class="ph ph-check-circle" aria-hidden="true"></i></label>
                            </div>
                        </div>
                    </fieldset>
                    <div class="field-grid">
                        <div class="field"><label for="subscription_unit_type">Aircon unit type</label><select id="subscription_unit_type" name="aircon_unit_type_id" required>@foreach($unitTypes as $unitType)<option value="{{ $unitType->id }}" data-price="{{ $unitType->price_centavos }}" data-name="{{ $unitType->name }}" @selected((int)old('aircon_unit_type_id', $unitTypes->first()?->id) === $unitType->id)>{{ $unitType->name }} · {{ $unitType->formatted_price }}</option>@endforeach</select></div>
                        <div class="field"><label for="subscription_quantity">Units of the same type per visit</label><select id="subscription_quantity" name="quantity">@for($quantity=1;$quantity<=5;$quantity++)<option value="{{ $quantity }}" @selected((int)old('quantity',1)===$quantity)>{{ $quantity }} {{ Str::plural('unit',$quantity) }}</option>@endfor</select></div>
                    </div>
                    <p class="form-hint">For different unit types, submit a separate request for each type to ensure accurate pricing.</p>
                </section>

                <section class="form-surface" aria-labelledby="schedule-heading">
                    <div class="form-section-heading"><span class="section-number">02</span><div><h2 id="schedule-heading">Select your preferred schedule</h2><p>Our office will confirm availability before activating the maintenance plan.</p></div></div>
                    <div class="field-grid">
                        <div class="field"><label for="next_service_date">Preferred first visit</label><input id="next_service_date" type="date" name="next_service_date" min="{{ today()->addDay()->format('Y-m-d') }}" value="{{ old('next_service_date',today()->addDays(7)->format('Y-m-d')) }}" required></div>
                        <div class="field"><label for="preferred_day">Preferred recurring day</label><select id="preferred_day" name="preferred_day">@foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)<option @selected(old('preferred_day','Saturday')===$day)>{{ $day }}</option>@endforeach</select></div>
                        <fieldset class="choice-fieldset field full"><legend>Preferred arrival time</legend><div class="time-grid subscription-time-grid">@foreach(config('scheduling.arrival_times') as $time)<div class="time-option is-available"><input id="sub-time-{{ str_replace(':','',$time) }}" type="radio" name="preferred_time" value="{{ $time }}" @checked(old('preferred_time','09:00')===$time) required><label for="sub-time-{{ str_replace(':','',$time) }}"><span><strong>{{ \Carbon\Carbon::createFromFormat('H:i',$time)->format('g:i A') }}</strong><small>Preferred window</small></span></label></div>@endforeach</div></fieldset>
                    </div>
                </section>

                <section class="form-surface" aria-labelledby="details-heading">
                    <div class="form-section-heading"><span class="section-number">03</span><div><h2 id="details-heading">Contact and service address</h2><p>Provide complete details for the Iligan City service location.</p></div></div>
                    <div class="field-grid">
                        <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required></div>
                        <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required></div>
                        <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
                        <div class="field"><label for="phone">Mobile number</label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required></div>
                        <div class="field full"><label for="address_line">Street / building / unit</label><input id="address_line" name="address_line" value="{{ old('address_line') }}" autocomplete="street-address" required></div>
                        <div class="field"><label for="barangay">Barangay</label><input id="barangay" name="barangay" value="{{ old('barangay') }}" required></div>
                        <div class="field"><label for="city">City</label><select id="city" name="city"><option value="Iligan City">Iligan City</option></select></div>
                        <div class="field"><label for="postal_code">Postal code <span class="optional">(optional)</span></label><input id="postal_code" name="postal_code" value="{{ old('postal_code','9200') }}" autocomplete="postal-code" inputmode="numeric"></div>
                        <div class="field"><label for="landmark">Nearby landmark</label><input id="landmark" name="landmark" value="{{ old('landmark') }}" placeholder="Example: beside the barangay hall" required>@error('landmark')<div class="input-error">{{ $message }}</div>@enderror</div>
                        <div class="field full"><label for="notes">Additional service information <span class="optional">(optional)</span></label><textarea id="notes" name="notes" placeholder="Access instructions, parking details, or aircon concerns">{{ old('notes') }}</textarea></div>
                    </div>
                    <label class="terms"><input type="checkbox" name="terms" value="1" @checked(old('terms')) required><span>I confirm this Iligan City service request and understand the office will confirm the recurring schedule before activation.</span></label>
                    <div class="form-submit-row"><span><i class="ph ph-lock-key" aria-hidden="true"></i> No advance payment required</span><button class="button button-yellow" type="submit">Submit Maintenance Plan Request <i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
                </section>
            </div>

            <aside class="care-summary" aria-label="Maintenance plan summary">
                <span class="eyebrow">Selected maintenance plan</span>
                <h2 data-plan-name>Quarterly Care</h2>
                <p data-plan-frequency>Every 3 months · 4 visits a year</p>
                <div class="summary-row"><span>Service</span><strong>{{ $service->name }}</strong></div>
                <div class="summary-row"><span>Aircon units</span><strong data-plan-units>Choose your unit type</strong></div>
                <div class="subscription-estimate" aria-live="polite" aria-atomic="true"><span>Estimated price per visit</span><strong data-subscription-price>—</strong></div>
                <p class="form-hint">The plan discount is included. Payment is due after each completed service visit.</p>
                <div class="summary-next"><i class="ph ph-check-circle" aria-hidden="true"></i><div><strong>Next step</strong><p>Our office reviews the request and contacts you to confirm the initial appointment.</p></div></div>
                <a class="text-link" href="{{ route('contact') }}">Contact Our Office <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>
            </aside>
        </form>
    </div>
</section>
@endsection
