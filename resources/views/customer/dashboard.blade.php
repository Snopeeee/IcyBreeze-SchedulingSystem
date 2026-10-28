@extends('layouts.site')
@php($title = 'My Appointments')
@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Customer account</span><h1>My Appointments</h1><p>Review bookings made while signed in to your IcyBreeze account.</p><form method="POST" action="{{ route('customer.logout') }}">@csrf<button class="button button-ghost button-small">Sign out</button></form></div></section>
<section class="section"><div class="site-shell account-list"><div class="account-heading"><h2>Booking history</h2><a class="button button-yellow button-small" href="{{ route('booking.create') }}">Schedule Service</a></div>
    @forelse($appointments as $appointment)
    <article class="account-appointment"><div><span class="eyebrow">{{ $appointment->reference }}</span><h3>{{ $appointment->unit_type }} · {{ $appointment->quantity }} {{ Str::plural('unit', $appointment->quantity) }}</h3><p>{{ $appointment->starts_at->format('D, M j, Y · g:i A') }} · {{ $appointment->formatted_total }}</p></div><div><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->status_label }}</span><a class="button button-ghost button-small" href="{{ route('booking.manage', $appointment->manage_token) }}">Manage booking</a></div></article>
    @empty <div class="account-empty"><h3>No account bookings yet</h3><p>Schedule your next aircon cleaning while signed in; it will appear here.</p></div>
    @endforelse
    {{ $appointments->links() }}
</div></section>
@endsection
