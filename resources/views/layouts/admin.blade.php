<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>{{ $title ?? 'Admin Dashboard' }} | IcyBreeze</title><link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icybreeze-favicon.png') }}">@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-logo" href="{{ route('admin.dashboard') }}"><img class="admin-logo-full" src="{{ asset('images/icybreeze-logo-compact-inverse.png') }}" alt="IcyBreeze Aircon Cleaning"><img class="admin-logo-mark" src="{{ asset('images/icybreeze-brand-mark-inverse.png') }}" alt=""></a>
        <div class="admin-label">Workspace</div>
        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="ph ph-squares-four"></i><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('admin.appointments.*') ? 'is-active' : '' }}" href="{{ route('admin.appointments.index') }}"><i class="ph ph-calendar-dots"></i><span>Appointments</span></a>
            <a class="{{ request()->routeIs('admin.payments.*') ? 'is-active' : '' }}" href="{{ route('admin.payments.index') }}"><i class="ph ph-credit-card"></i><span>Payments</span></a>
            <a class="{{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}" href="{{ route('admin.customers.index') }}"><i class="ph ph-users-three"></i><span>Customers</span></a>
            <a class="{{ request()->routeIs('admin.subscriptions.*') ? 'is-active' : '' }}" href="{{ route('admin.subscriptions.index') }}"><i class="ph ph-arrows-clockwise"></i><span>Subscriptions</span></a>
            <a class="{{ request()->routeIs('admin.technicians.*') ? 'is-active' : '' }}" href="{{ route('admin.technicians.index') }}"><i class="ph ph-identification-card"></i><span>Technicians</span></a>
            <a class="{{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}" href="{{ route('admin.services.index') }}"><i class="ph ph-broom"></i><span>Services</span></a>
        </nav>
        <div class="admin-label">Website</div>
        <nav class="admin-nav"><a href="{{ route('home') }}" target="_blank"><i class="ph ph-arrow-square-out"></i><span>View website</span></a></nav>
        <div class="admin-user"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span><form method="POST" action="{{ route('admin.logout') }}">@csrf<button><i class="ph ph-sign-out"></i> <span>Sign out</span></button></form></div>
    </aside>
    <main class="admin-main">
        @if(session('success'))<div class="flash flash-success" style="margin:0 0 20px"><i class="ph-fill ph-check-circle"></i>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="flash" style="margin:0 0 20px;color:#9b3a3a;background:#fff0f0;border:1px solid #f2caca"><i class="ph-fill ph-warning-circle"></i>{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
