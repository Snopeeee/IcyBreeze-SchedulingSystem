@extends('layouts.site')
@php($title = 'Manage Booking '.$appointment->reference)
@section('content')
<section class="booking-page"><div class="site-shell booking-container">
    <div class="booking-header"><span class="eyebrow">Private booking link</span><h1>Manage your appointment</h1><p>Reference {{ $appointment->reference }}</p></div>
    <div class="booking-panel"><div class="booking-layout"><div class="booking-main">
        <h2 class="booking-step-title">{{ $appointment->service->name }}</h2><p class="booking-step-copy">Booked for {{ $appointment->customer->full_name }}</p>
        <div class="review-card"><div class="review-row"><span>Status</span><strong><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->status_label }}</span></strong></div><div class="review-row"><span>Schedule</span><strong>{{ $appointment->starts_at->format('D, M j, Y · g:i A') }}–{{ $appointment->ends_at->format('g:i A') }}</strong></div><div class="review-row"><span>Aircon</span><strong>{{ $appointment->quantity }} {{ Str::plural('unit',$appointment->quantity) }} · {{ $appointment->unit_type }}</strong></div><div class="review-row"><span>Address</span><strong>{{ $appointment->address_line }}, {{ $appointment->barangay }}, {{ $appointment->city }}</strong></div><div class="review-row"><span>Payment</span><strong>{{ $appointment->payment_status_label }} · {{ $appointment->formatted_total }}</strong></div></div>
        @if(!in_array($appointment->status,['completed','cancelled','no_show']) && now()->addHours(24)->lessThan($appointment->starts_at))
            <form method="POST" action="{{ route('booking.cancel',$appointment->manage_token) }}" style="margin-top:25px" onsubmit="return confirm('Cancel this appointment?')">@csrf<div class="field"><label for="reason">Cancellation reason <span style="font-weight:400">(optional)</span></label><input id="reason" name="reason" maxlength="180"></div><button class="button button-danger button-small" style="margin-top:12px">Cancel appointment</button></form>
        @elseif(!in_array($appointment->status,['completed','cancelled','no_show']))
            <div class="demo-note"><i class="ph ph-phone"></i><span>Online cancellation closes 24 hours before the visit. Please call 0917 123 2723 for help.</span></div>
        @endif
    </div><aside class="booking-summary"><h3>Booking history</h3>@forelse($appointment->histories as $history)<div class="summary-row"><span>{{ $history->created_at->format('M j, g:i A') }}</span><strong>{{ Str::of($history->to_status)->replace('_',' ')->title() }}</strong><p style="margin:3px 0 0;color:#6c8290;font-size:9px">{{ $history->reason }}</p></div>@empty<p style="font-size:10px;color:#6c8290">No history yet.</p>@endforelse</aside></div></div>
</div></section>
@endsection
