@extends('layouts.admin')
@php
    $title = 'Dashboard';
@endphp

@section('content')
<div class="admin-top admin-top-branded">
    <div>
        <span class="admin-eyebrow"><i class="ph-fill ph-snowflake"></i> IcyBreeze command center</span>
        <h1>Good morning, {{ Str::before(auth()->user()->name, ' ') }}.</h1>
        <p>Keep appointments, field teams, and business performance moving smoothly.</p>
    </div>
    <span class="admin-date"><i class="ph ph-calendar-blank"></i>{{ now()->format('l, F j, Y') }}</span>
</div>

<div class="metric-grid">
    <div class="metric-card metric-card-cyan"><div class="metric-head"><span>Today's appointments</span><i class="ph ph-calendar-check"></i></div><div class="metric-value">{{ $todayCount }}</div><div class="metric-note">Scheduled for today</div></div>
    <div class="metric-card"><div class="metric-head"><span>Upcoming confirmed</span><i class="ph ph-check-circle"></i></div><div class="metric-value">{{ $upcomingCount }}</div><div class="metric-note">Ready or assigned</div></div>
    <div class="metric-card metric-card-sun"><div class="metric-head"><span>Needs attention</span><i class="ph ph-bell-ringing"></i></div><div class="metric-value">{{ $pendingCount + $unpaidCount }}</div><div class="metric-note">Pending or unpaid items</div></div>
    <div class="metric-card"><div class="metric-head"><span>Paid this month</span><i class="ph ph-wallet"></i></div><div class="metric-value">{{ "\u{20B1}".number_format($revenue / 100) }}</div><div class="metric-note">Recorded payments</div></div>
    <div class="metric-card"><div class="metric-head"><span>Technician shares</span><i class="ph ph-user-circle-gear"></i></div><div class="metric-value">{{ "\u{20B1}".number_format($technicianPayout / 100) }}</div><div class="metric-note">Paid jobs this month</div></div>
    <div class="metric-card metric-card-navy"><div class="metric-head"><span>Gross</span><i class="ph ph-chart-line-up"></i></div><div class="metric-value">{{ "\u{20B1}".number_format($gross / 100) }}</div><div class="metric-note">After technician shares</div></div>
    <div class="metric-card"><div class="metric-head"><span>Care plans</span><i class="ph ph-arrows-clockwise"></i></div><div class="metric-value">{{ $subscriptionCount }}</div><div class="metric-note">Pending or active</div></div>
</div>

<div class="dashboard-section-heading">
    <div><span class="admin-eyebrow">Performance overview</span><h2>Business at a glance</h2></div>
    <span>Live from appointments and recorded payments</span>
</div>

@php
    $maxFinance = max(1, (int) $financialTrend->max(fn ($month) => max($month['revenue'], $month['gross'])));
    $maxUpcoming = max(1, (int) $upcomingTrend->max('total'));
    $maxUnitType = max(1, (int) $unitTypeMix->max('total'));
@endphp

<div class="dashboard-insights">
    <section class="admin-card dashboard-chart-card dashboard-chart-card-wide">
        <div class="admin-card-header chart-card-header">
            <div><span class="chart-kicker">Last six months</span><h2>Revenue and gross trend</h2></div>
            <div class="chart-legend"><span><i class="legend-revenue"></i>Revenue</span><span><i class="legend-gross"></i>Gross</span></div>
        </div>
        <div class="finance-chart" role="img" aria-label="Six month comparison of paid revenue and gross after technician shares">
            @foreach($financialTrend as $month)
                @php
                    $revenueHeight = $month['revenue'] > 0 ? max(6, round($month['revenue'] / $maxFinance * 100)) : 2;
                    $grossHeight = $month['gross'] > 0 ? max(6, round($month['gross'] / $maxFinance * 100)) : 2;
                @endphp
                <div class="finance-month" aria-label="{{ $month['label'] }}: revenue {{ "\u{20B1}".number_format($month['revenue'] / 100) }}, gross {{ "\u{20B1}".number_format($month['gross'] / 100) }}">
                    <div class="finance-bars">
                        <span class="finance-bar finance-bar-revenue" style="--bar-height: {{ $revenueHeight }}%"></span>
                        <span class="finance-bar finance-bar-gross" style="--bar-height: {{ $grossHeight }}%"></span>
                    </div>
                    <strong>{{ $month['label'] }}</strong>
                </div>
            @endforeach
        </div>
        <div class="chart-summary-row">
            <span><small>This month revenue</small><strong>{{ "\u{20B1}".number_format($financialTrend->last()['revenue'] / 100) }}</strong></span>
            <span><small>This month gross</small><strong>{{ "\u{20B1}".number_format($financialTrend->last()['gross'] / 100) }}</strong></span>
        </div>
    </section>

    <section class="admin-card dashboard-chart-card">
        <div class="admin-card-header chart-card-header"><div><span class="chart-kicker">Next seven days</span><h2>Appointment volume</h2></div><i class="ph ph-calendar-dots chart-header-icon"></i></div>
        <div class="appointment-chart" role="img" aria-label="Appointment volume for the next seven days">
            @foreach($upcomingTrend as $day)
                @php($height = $day['total'] > 0 ? max(12, round($day['total'] / $maxUpcoming * 100)) : 3)
                <div class="appointment-column" aria-label="{{ $day['date'] }}: {{ $day['total'] }} appointments">
                    <span class="appointment-count">{{ $day['total'] }}</span>
                    <span class="appointment-bar" style="--bar-height: {{ $height }}%"></span>
                    <strong>{{ $day['label'] }}</strong>
                </div>
            @endforeach
        </div>
        <div class="unit-mix-block">
            <div class="unit-mix-heading"><span>Booked unit mix</span><small>All appointments</small></div>
            @forelse($unitTypeMix as $unitType)
                <div class="unit-mix-row">
                    <span>{{ $unitType['label'] }}</span>
                    <span class="unit-mix-track"><span style="--mix-width: {{ max(5, round($unitType['total'] / $maxUnitType * 100)) }}%"></span></span>
                    <strong>{{ $unitType['total'] }}</strong>
                </div>
            @empty
                <div class="chart-empty">Unit mix appears after the first booking.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="admin-grid">
    <section class="admin-card">
        <div class="admin-card-header"><h2>Today's schedule</h2><a class="text-link" href="{{ route('admin.appointments.index') }}">All appointments <i class="ph ph-arrow-right"></i></a></div>
        @if($todayAppointments->isEmpty())
            <div class="empty-state"><i class="ph ph-calendar-blank"></i>No appointments scheduled today.</div>
        @else
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Time</th><th>Customer</th><th>Aircon type</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach($todayAppointments as $appointment)
                    <tr><td><strong>{{ $appointment->starts_at->format('g:i A') }}</strong>{{ $appointment->ends_at->format('g:i A') }}</td><td><strong>{{ $appointment->customer->full_name }}</strong>{{ $appointment->city }}</td><td><strong>{{ $appointment->unit_type }}</strong>{{ $appointment->quantity }} {{ Str::plural('unit', $appointment->quantity) }}</td><td><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->status_label }}</span></td><td><a class="text-link" href="{{ route('admin.appointments.show', $appointment) }}">Open</a></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Recently booked</h2></div>
        @forelse($recentAppointments as $appointment)
            <a class="recent-appointment" href="{{ route('admin.appointments.show', $appointment) }}"><span><strong>{{ $appointment->customer->full_name }}</strong><small>{{ $appointment->service->name }} &middot; {{ $appointment->starts_at->format('M j') }}</small></span><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->status_label }}</span></a>
        @empty
            <div class="empty-state">No appointments yet.</div>
        @endforelse
    </section>
</div>

<section class="admin-card admin-field-status">
    <div class="admin-card-header"><h2>Technician field status</h2><a class="text-link" href="{{ route('admin.technicians.index') }}">Manage accounts <i class="ph ph-arrow-right"></i></a></div>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Technician</th><th>Jobs today</th><th>Last GPS update</th><th>Location</th></tr></thead><tbody>
        @forelse($technicians as $technician)
            <tr><td><strong>{{ $technician->name }}</strong>{{ $technician->phone }}</td><td><strong>{{ $technician->technician_appointments_count }}</strong>assigned</td><td>@if($technician->technicianLocation)<strong>{{ $technician->technicianLocation->recorded_at->diffForHumans() }}</strong>accuracy {{ round($technician->technicianLocation->accuracy_meters ?? 0) }} m @else<span class="status-badge status-unpaid">Not shared</span>@endif</td><td>@if($technician->technicianLocation)<a class="text-link" href="https://www.google.com/maps/search/?api=1&query={{ $technician->technicianLocation->latitude }},{{ $technician->technicianLocation->longitude }}" target="_blank" rel="noopener">Open pin <i class="ph ph-arrow-up-right"></i></a>@else&mdash;@endif</td></tr>
        @empty
            <tr><td colspan="4"><div class="empty-state">No technician accounts yet.</div></td></tr>
        @endforelse
    </tbody></table></div>
</section>
@endsection
