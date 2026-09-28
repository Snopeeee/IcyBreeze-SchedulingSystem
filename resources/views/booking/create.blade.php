@extends('layouts.site')
@php
    $title = 'Book an Aircon Cleaning';
    $initialStep = $errors->hasAny(['aircon_unit_type_id', 'quantity']) ? 1 : ($errors->hasAny(['appointment_date', 'appointment_time']) ? 2 : ($errors->hasAny(['first_name', 'last_name', 'email', 'phone', 'address_line', 'barangay', 'city', 'postal_code', 'landmark', 'customer_notes']) ? 3 : ($errors->any() ? 4 : 1)));
@endphp

@section('content')
<section class="booking-page" data-booking-top>
    <div class="site-shell booking-container">
        <div class="booking-progress" aria-label="Booking progress">
            @foreach (['Service','Schedule','Your details','Confirm'] as $label)
                <div class="progress-item {{ $loop->first ? 'is-active' : '' }}" data-progress-step="{{ $loop->iteration }}"><div class="progress-dot">{{ $loop->iteration }}</div><span>{{ $label }}</span></div>
            @endforeach
        </div>

        <div class="booking-header">
            <div class="booking-header-copy">
                <span class="eyebrow">Appointment scheduling</span>
                <h1>Schedule an aircon cleaning</h1>
                <p>Select the unit type, review live availability, and submit the complete Iligan City service address.</p>
                <div class="booking-promises"><span><i class="ph-fill ph-check-circle"></i> Live availability</span><span><i class="ph-fill ph-check-circle"></i> Exact unit pricing</span><span><i class="ph-fill ph-check-circle"></i> Pay after service</span></div>
            </div>
        </div>

        <form method="POST" action="{{ route('booking.store') }}" data-booking-wizard data-initial-step="{{ $initialStep }}" data-availability-url="{{ route('booking.availability') }}">
            @csrf
            <div class="booking-panel">
                @if ($errors->any())
                    <div class="flash" style="margin:0 0 22px;color:#9b3a3a;background:#fff0f0;border:1px solid #f2caca"><i class="ph-fill ph-warning-circle"></i>Please review the highlighted fields and try again.</div>
                @endif
                <div class="booking-layout">
                    <div class="booking-main">
                        <section class="booking-step is-active" data-step="1">
                            <div class="booking-step-heading"><span class="booking-step-icon"><i class="ph ph-wind"></i></span><div><h2 class="booking-step-title">Select the aircon unit type</h2><p class="booking-step-copy">Choose the applicable unit type and quantity. The estimated service total updates automatically.</p></div></div>
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <div class="booking-service-list">
                                @foreach($unitTypes as $unitType)
                                    @php($checked = (string) old('aircon_unit_type_id', $selectedUnitTypeId) === (string) $unitType->id)
                                    <div class="booking-service">
                                        <input id="unit-type-{{ $unitType->id }}" type="radio" name="aircon_unit_type_id" value="{{ $unitType->id }}" data-name="{{ $unitType->name }}" data-price="{{ $unitType->price_centavos }}" {{ $checked ? 'checked' : '' }} required>
                                        <label for="unit-type-{{ $unitType->id }}"><span class="radio-icon"><i class="ph ph-wind"></i></span><span><h3>{{ $unitType->name }}</h3><p>Aircon Cleaning · price per unit</p></span><span class="price">{{ $unitType->formatted_price }}</span></label>
                                    </div>
                                @endforeach
                            </div>
                            @error('aircon_unit_type_id')<div class="input-error">{{ $message }}</div>@enderror
                            <div class="field-grid" style="margin-top:22px">
                                <div class="field"><label for="quantity">Number of matching units</label><select id="quantity" name="quantity" required>@for($quantity = 1; $quantity <= 5; $quantity++)<option value="{{ $quantity }}" @selected((int)old('quantity',1) === $quantity)>{{ $quantity }} {{ $quantity === 1 ? 'unit' : 'units' }}</option>@endfor</select>@error('quantity')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="demo-note" style="margin:0"><i class="ph ph-info"></i><span>For different unit types, submit a separate booking for each type to ensure accurate pricing.</span></div>
                            </div>
                            <div class="booking-actions"><span></span><button class="button button-navy" type="button" data-next>Continue to Schedule <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="2">
                            <div class="booking-step-heading"><span class="booking-step-icon"><i class="ph ph-calendar-check"></i></span><div><h2 class="booking-step-title">Select date and time</h2><p class="booking-step-copy">Each arrival window shows its current status from our live schedule.</p></div></div>
                            <div class="field"><label for="appointment_date">Appointment date</label><div class="date-field-wrap"><i class="ph ph-calendar-blank"></i><input id="appointment_date" name="appointment_date" type="date" min="{{ today()->addDay()->format('Y-m-d') }}" value="{{ old('appointment_date', today()->addDays(2)->format('Y-m-d')) }}" required></div>@error('appointment_date')<div class="input-error">{{ $message }}</div>@enderror</div>
                            <fieldset class="choice-fieldset time-fieldset" data-availability-region aria-busy="false">
                                <div class="time-heading"><legend>Arrival windows</legend><div class="availability-legend" aria-label="Schedule status legend"><span><i class="status-dot status-dot-available"></i>Available</span><span><i class="status-dot status-dot-progress"></i>In progress</span><span><i class="status-dot status-dot-reserved"></i>Reserved</span></div></div>
                                <div class="time-grid">
                                    @foreach($availability as $slot)
                                        @php($selectable = $slot['status'] === 'available')
                                        <div class="time-option is-{{ $slot['status'] }}" data-time-option data-time="{{ $slot['time'] }}">
                                            <input id="time-{{ str_replace(':','',$slot['time']) }}" type="radio" name="appointment_time" value="{{ $slot['time'] }}" @checked($selectable && old('appointment_time', '09:00') === $slot['time']) @disabled(!$selectable) required>
                                            <label for="time-{{ str_replace(':','',$slot['time']) }}"><span class="time-status-icon"><i class="{{ $slot['status'] === 'available' ? 'ph ph-check' : ($slot['status'] === 'in_progress' ? 'ph ph-clock-countdown' : 'ph ph-prohibit') }}"></i></span><span><strong>{{ $slot['label'] }}</strong><small data-slot-status>{{ $slot['status_label'] }}</small></span></label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="availability-message" data-availability-message aria-live="polite">Availability is refreshed whenever you change the date.</p>
                                @error('appointment_time')<div class="input-error">{{ $message }}</div>@enderror
                            </fieldset>
                            <div class="demo-note availability-note"><i class="ph ph-info"></i><span>The selected arrival window is reserved only after appointment confirmation. Availability is verified again before the booking is saved.</span></div>
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-navy" type="button" data-next>Continue to Customer Details <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="3">
                            <div class="booking-step-heading"><span class="booking-step-icon"><i class="ph ph-map-pin-line"></i></span><div><h2 class="booking-step-title">Provide customer and service-location details</h2><p class="booking-step-copy">These details are used only to coordinate and manage the appointment.</p></div></div>
                            <div class="field-grid">
                                <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>@error('first_name')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>@error('last_name')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="phone">Mobile number</label><input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09XX XXX XXXX" required>@error('phone')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field full"><label for="address_line">Street address / building / unit</label><input id="address_line" name="address_line" value="{{ old('address_line') }}" autocomplete="street-address" required>@error('address_line')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="barangay">Barangay</label><input id="barangay" name="barangay" value="{{ old('barangay') }}" required>@error('barangay')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="city">City</label><select id="city" name="city" required><option value="Iligan City" selected>Iligan City</option></select>@error('city')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="postal_code">Postal code <span style="font-weight:400">(optional)</span></label><input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric"></div>
                                <div class="field"><label for="landmark">Nearby landmark</label><input id="landmark" name="landmark" value="{{ old('landmark') }}" placeholder="Example: beside the barangay hall" required>@error('landmark')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field full"><label for="customer_notes">Additional service information <span style="font-weight:400">(optional)</span></label><textarea id="customer_notes" name="customer_notes" placeholder="Access instructions, parking details, or aircon concerns">{{ old('customer_notes') }}</textarea></div>
                            </div>
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-navy" type="button" data-next>Review booking <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="4">
                            <div class="booking-step-heading"><span class="booking-step-icon"><i class="ph ph-check-circle"></i></span><div><h2 class="booking-step-title">Review and confirm</h2><p class="booking-step-copy">Review the appointment details and payment method before submission.</p></div></div>
                            <div class="review-card">
                                <div class="review-row"><span>Service</span><strong data-summary-service>{{ $service->name }}</strong></div>
                                <div class="review-row"><span>Aircon</span><strong data-summary-quantity>—</strong></div>
                                <div class="review-row"><span>Schedule</span><strong data-summary-schedule>—</strong></div>
                                <div class="review-row"><span>Service area</span><strong>Iligan City · No travel fee</strong></div>
                            </div>
                            <div class="field" style="margin-top:22px"><label>Payment method</label><div class="payment-options"><div class="payment-option"><input id="payment-cash" type="radio" name="payment_method" value="cash" checked required><label for="payment-cash"><i class="ph ph-money"></i><span><strong>Cash after service</strong><span>Payment is collected after the completed service and recorded by the office for reconciliation.</span></span></label></div></div></div>
                            <label class="terms"><input type="checkbox" name="terms" value="1" required><span>I confirm that the appointment information is correct and I agree to the 24-hour online cancellation policy.</span></label>
                            @error('terms')<div class="input-error">{{ $message }}</div>@enderror
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-cyan" type="submit" data-confirm-submit>Confirm appointment <i class="ph ph-check"></i></button></div>
                        </section>
                    </div>
                    <aside class="booking-summary">
                        <h3>Appointment summary</h3>
                        <div class="summary-row"><span>Service</span><strong data-summary-service>{{ $service->name }}</strong></div>
                        <div class="summary-row"><span>Aircon</span><strong data-summary-quantity>1 unit</strong></div>
                        <div class="summary-row"><span>Schedule</span><strong data-summary-schedule>Select an appointment schedule</strong></div>
                        <div class="summary-total"><span>Estimated total</span><strong data-summary-price>—</strong></div>
                        <p style="margin:10px 0 0;color:#6c8290;line-height:1.5">The estimate is based on the selected unit type and quantity. Repair work is not included.</p>
                        <div class="booking-assurance"><i class="ph-fill ph-shield-check"></i><div><strong>Secure appointment request</strong><span>No advance payment is required. Review and confirm the appointment before submission.</span></div></div>
                    </aside>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
