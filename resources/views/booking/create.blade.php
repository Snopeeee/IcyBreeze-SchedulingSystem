@extends('layouts.site')
@php($title = 'Book an Aircon Cleaning')

@section('content')
<section class="booking-page" data-booking-top>
    <div class="site-shell booking-container">
        <div class="booking-header">
            <span class="eyebrow">Book online</span>
            <h1>Schedule your cleaning</h1>
            <p>Four short steps. Your price and appointment details stay visible as you book.</p>
        </div>

        <div class="booking-progress" aria-label="Booking progress">
            @foreach (['Service','Schedule','Your details','Confirm'] as $label)
                <div class="progress-item {{ $loop->first ? 'is-active' : '' }}" data-progress-step="{{ $loop->iteration }}"><div class="progress-dot">{{ $loop->iteration }}</div><span>{{ $label }}</span></div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('booking.store') }}" data-booking-wizard data-initial-step="1">
            @csrf
            <div class="booking-panel">
                @if ($errors->any())
                    <div class="flash" style="margin:0 0 22px;color:#9b3a3a;background:#fff0f0;border:1px solid #f2caca"><i class="ph-fill ph-warning-circle"></i>Please review the highlighted fields and try again.</div>
                @endif
                <div class="booking-layout">
                    <div class="booking-main">
                        <section class="booking-step is-active" data-step="1">
                            <h2 class="booking-step-title">What type of aircon will we clean?</h2>
                            <p class="booking-step-copy">Every booking is Standard Cleaning. Select the unit type to calculate its exact regular price.</p>
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <div class="booking-service-list">
                                @foreach($unitTypes as $unitType)
                                    @php($checked = (string) old('aircon_unit_type_id', $unitTypes->first()?->id) === (string) $unitType->id)
                                    <div class="booking-service">
                                        <input id="unit-type-{{ $unitType->id }}" type="radio" name="aircon_unit_type_id" value="{{ $unitType->id }}" data-name="{{ $unitType->name }}" data-price="{{ $unitType->price_centavos }}" {{ $checked ? 'checked' : '' }} required>
                                        <label for="unit-type-{{ $unitType->id }}"><span class="radio-icon"><i class="ph {{ str_contains($unitType->name, 'Window') ? 'ph-air-conditioner' : 'ph-wind' }}"></i></span><span><h3>{{ $unitType->name }}</h3><p>Standard Cleaning · price per unit</p></span><span class="price">{{ $unitType->formatted_price }}</span></label>
                                    </div>
                                @endforeach
                            </div>
                            @error('aircon_unit_type_id')<div class="input-error">{{ $message }}</div>@enderror
                            <div class="field-grid" style="margin-top:22px">
                                <div class="field"><label for="quantity">Number of matching units</label><select id="quantity" name="quantity" required>@for($quantity = 1; $quantity <= 5; $quantity++)<option value="{{ $quantity }}" @selected((int)old('quantity',1) === $quantity)>{{ $quantity }} {{ $quantity === 1 ? 'unit' : 'units' }}</option>@endfor</select>@error('quantity')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="demo-note" style="margin:0"><i class="ph ph-info"></i><span>Have different aircon types? Create a separate booking for each type so every price stays accurate.</span></div>
                            </div>
                            <div class="booking-actions"><span></span><button class="button button-navy" type="button" data-next>Choose schedule <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="2">
                            <h2 class="booking-step-title">When should we come?</h2>
                            <p class="booking-step-copy">Choose your preferred date and arrival time. We allow one active appointment per time window in this MVP.</p>
                            <div class="field"><label for="appointment_date">Appointment date</label><input id="appointment_date" name="appointment_date" type="date" min="{{ today()->addDay()->format('Y-m-d') }}" value="{{ old('appointment_date', today()->addDays(2)->format('Y-m-d')) }}" required>@error('appointment_date')<div class="input-error">{{ $message }}</div>@enderror</div>
                            <div class="field" style="margin-top:22px"><label>Preferred arrival time</label><div class="time-grid">@foreach($times as $time)<div class="time-option"><input id="time-{{ str_replace(':','',$time) }}" type="radio" name="appointment_time" value="{{ $time }}" @checked(old('appointment_time','10:00') === $time) required><label for="time-{{ str_replace(':','',$time) }}">{{ \Carbon\Carbon::createFromFormat('H:i',$time)->format('g:i A') }}</label></div>@endforeach</div>@error('appointment_time')<div class="input-error">{{ $message }}</div>@enderror</div>
                            <div class="demo-note"><i class="ph ph-info"></i><span>A selected time is confirmed only after submission. If another customer books it first, we will ask you to choose another slot.</span></div>
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-navy" type="button" data-next>Your details <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="3">
                            <h2 class="booking-step-title">Where should we meet you?</h2>
                            <p class="booking-step-copy">We use these details only to deliver and manage your appointment.</p>
                            <div class="field-grid">
                                <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>@error('first_name')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>@error('last_name')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="phone">Mobile number</label><input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09XX XXX XXXX" required>@error('phone')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field full"><label for="address_line">Street address / building / unit</label><input id="address_line" name="address_line" value="{{ old('address_line') }}" autocomplete="street-address" required>@error('address_line')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="barangay">Barangay</label><input id="barangay" name="barangay" value="{{ old('barangay') }}" required>@error('barangay')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="city">City</label><select id="city" name="city" required><option value="Iligan City" selected>Iligan City</option></select>@error('city')<div class="input-error">{{ $message }}</div>@enderror</div>
                                <div class="field"><label for="postal_code">Postal code <span style="font-weight:400">(optional)</span></label><input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric"></div>
                                <div class="field"><label for="landmark">Nearby landmark <span style="font-weight:400">(optional)</span></label><input id="landmark" name="landmark" value="{{ old('landmark') }}"></div>
                                <div class="field full">
                                    <div class="gps-capture" data-location-fields>
                                        <div class="gps-icon"><i class="ph ph-crosshair"></i></div>
                                        <div><strong>Help your technician find the service address</strong><p>Optionally share this device’s current GPS pin. We save it only with this appointment and show it to the assigned technician.</p><span data-location-status>No location shared yet.</span></div>
                                        <button class="button button-ghost button-small" type="button" data-geolocate><i class="ph ph-map-pin"></i> Use my location</button>
                                        <input type="hidden" name="latitude" value="{{ old('latitude') }}" data-latitude>
                                        <input type="hidden" name="longitude" value="{{ old('longitude') }}" data-longitude>
                                        <input type="hidden" name="location_accuracy_meters" value="{{ old('location_accuracy_meters') }}" data-accuracy>
                                        <input type="hidden" name="location_consent" value="{{ old('location_consent') }}" data-location-consent>
                                    </div>
                                    @error('latitude')<div class="input-error">{{ $message }}</div>@enderror
                                    @error('location_consent')<div class="input-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="field full"><label for="customer_notes">Anything we should know? <span style="font-weight:400">(optional)</span></label><textarea id="customer_notes" name="customer_notes" placeholder="Parking instructions, access notes, or aircon concerns">{{ old('customer_notes') }}</textarea></div>
                            </div>
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-navy" type="button" data-next>Review booking <i class="ph ph-arrow-right"></i></button></div>
                        </section>

                        <section class="booking-step" data-step="4">
                            <h2 class="booking-step-title">Review and confirm</h2>
                            <p class="booking-step-copy">Check your selections, choose how you plan to pay, and create the appointment.</p>
                            <div class="review-card">
                                <div class="review-row"><span>Service</span><strong data-summary-service>{{ $service->name }}</strong></div>
                                <div class="review-row"><span>Aircon</span><strong data-summary-quantity>—</strong></div>
                                <div class="review-row"><span>Schedule</span><strong data-summary-schedule>—</strong></div>
                                <div class="review-row"><span>Service area</span><strong>Iligan City · No travel fee</strong></div>
                            </div>
                            <div class="field" style="margin-top:22px"><label>Payment method</label><div class="payment-options"><div class="payment-option"><input id="payment-cash" type="radio" name="payment_method" value="cash" @checked(old('payment_method','cash') === 'cash') required><label for="payment-cash"><i class="ph ph-money"></i><span><strong>Cash after service</strong><span>Pay the technician once the cleaning is completed.</span></span></label></div><div class="payment-option"><input id="payment-online" type="radio" name="payment_method" value="online" @checked(old('payment_method') === 'online') required><label for="payment-online"><i class="ph ph-credit-card"></i><span><strong>Online payment</strong><span>Creates a safe demo payment record for this MVP.</span></span></label></div></div></div>
                            <div class="demo-note"><i class="ph ph-info"></i><span>Online payment is not yet connected to live PayMongo checkout. No card or wallet information is collected in this local MVP.</span></div>
                            <label class="terms"><input type="checkbox" name="terms" value="1" required><span>I confirm that the appointment information is correct and I agree to the 24-hour online cancellation policy.</span></label>
                            @error('terms')<div class="input-error">{{ $message }}</div>@enderror
                            <div class="booking-actions"><button class="button button-ghost" type="button" data-back><i class="ph ph-arrow-left"></i> Back</button><button class="button button-cyan" type="button" data-confirm-submit>Confirm appointment <i class="ph ph-check"></i></button></div>
                        </section>
                    </div>
                    <aside class="booking-summary">
                        <h3>Your booking</h3>
                        <div class="summary-row"><span>Service</span><strong data-summary-service>{{ $service->name }}</strong></div>
                        <div class="summary-row"><span>Aircon</span><strong data-summary-quantity>1 unit</strong></div>
                        <div class="summary-row"><span>Schedule</span><strong data-summary-schedule>Choose your schedule</strong></div>
                        <div class="summary-total"><span>Estimated total</span><strong data-summary-price>—</strong></div>
                        <p style="margin:10px 0 0;color:#6c8290;font-size:9px;line-height:1.5">Final price assumes the selected quantity and no extra repair work.</p>
                    </aside>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
