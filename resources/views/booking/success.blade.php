@extends('layouts.site')
@php($title = 'Booking Confirmed')
@section('content')
<section class="booking-page"><div class="site-shell"><div class="confirmation-card">
    <div class="confirmation-icon"><i class="ph-fill ph-check-circle"></i></div>
    <h1>You are booked!</h1>
    <p>Thanks, {{ $appointment->customer->first_name }}. We saved your IcyBreeze appointment and its private management link.</p>
    <div class="reference-pill">Reference: {{ $appointment->reference }}</div>
    <div class="confirmation-details">
        <div><span class="detail-label">Service</span><span class="detail-value">{{ $appointment->service->name }} · {{ $appointment->quantity }} {{ Str::plural('unit',$appointment->quantity) }}</span></div>
        <div><span class="detail-label">Aircon type</span><span class="detail-value">{{ $appointment->unit_type }}</span></div>
        <div><span class="detail-label">Total</span><span class="detail-value">{{ $appointment->formatted_total }}</span></div>
        <div><span class="detail-label">Schedule</span><span class="detail-value">{{ $appointment->starts_at->format('D, M j, Y · g:i A') }}</span></div>
        <div><span class="detail-label">Payment</span><span class="detail-value">Cash after service</span></div>
        <div><span class="detail-label">Address</span><span class="detail-value">{{ $appointment->address_line }}, {{ $appointment->barangay }}, {{ $appointment->city }}</span></div>
        <div><span class="detail-label">Status</span><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->status_label }}</span></div>
    </div>
    <div class="hero-actions" style="justify-content:center"><a class="button button-navy" href="{{ route('booking.manage', $appointment->manage_token) }}">Manage this booking</a><a class="button button-ghost" href="{{ route('home') }}">Return home</a></div>
</div></div></section>
@endsection
