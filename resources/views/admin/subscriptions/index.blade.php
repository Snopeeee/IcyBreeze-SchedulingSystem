@extends('layouts.admin')
@php($title = 'Subscriptions')

@section('content')
<div class="admin-top"><div><h1>Care plan subscriptions</h1><p>Review recurring Standard Cleaning requests, pricing, schedules, and technician assignments.</p></div><a class="button button-navy button-small" href="{{ route('subscriptions.create') }}" target="_blank"><i class="ph ph-arrow-square-out"></i> Public plan page</a></div>
<form class="admin-filters" method="GET"><select name="status"><option value="">All statuses</option>@foreach(['pending','active','paused','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ Str::title($status) }}</option>@endforeach</select><button class="button button-navy button-small">Apply</button>@if(request('status'))<a class="button button-ghost button-small" href="{{ route('admin.subscriptions.index') }}">Clear</a>@endif</form>
<div class="admin-page-card"><div class="admin-table-wrap"><table class="admin-table">
    <thead><tr><th>Plan / customer</th><th>Aircon</th><th>Per visit finances</th><th>Location</th><th>Next visit</th><th>Status / assignment</th></tr></thead>
    <tbody>
    @forelse($subscriptions as $subscription)
        <tr>
            <td><strong>{{ $subscription->reference }} · {{ $subscription->plan_label }}</strong>{{ $subscription->customer->full_name }} · every {{ $subscription->interval_months }} months</td>
            <td><strong>{{ $subscription->unit_type ?? 'Split Type' }}</strong>{{ $subscription->quantity }} {{ Str::plural('unit',$subscription->quantity) }}</td>
            <td><strong>{{ $subscription->formatted_price }} customer</strong>{{ $subscription->formatted_technician_share }} technician · {{ $subscription->formatted_gross }} gross</td>
            <td><strong>{{ $subscription->barangay }}, Iligan</strong>{{ $subscription->latitude ? 'GPS pin shared' : 'Address only' }}</td>
            <td><strong>{{ $subscription->next_service_date?->format('M j, Y') ?? 'Not set' }}</strong>{{ $subscription->preferred_day }} · {{ \Carbon\Carbon::createFromFormat('H:i',$subscription->preferred_time)->format('g:i A') }}</td>
            <td><form method="POST" action="{{ route('admin.subscriptions.update',$subscription) }}" style="display:grid;grid-template-columns:110px 145px 125px auto;gap:6px;align-items:center">@csrf @method('PATCH')<select name="status">@foreach(['pending','active','paused','cancelled'] as $status)<option value="{{ $status }}" @selected($subscription->status===$status)>{{ Str::title($status) }}</option>@endforeach</select><select name="technician_id"><option value="">Unassigned</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected($subscription->technician_id===$technician->id)>{{ $technician->name }}</option>@endforeach</select><input type="date" name="next_service_date" value="{{ $subscription->next_service_date?->format('Y-m-d') }}" min="{{ today()->format('Y-m-d') }}"><button class="button button-navy button-small" style="min-height:34px">Save</button></form></td>
        </tr>
    @empty
        <tr><td colspan="6"><div class="empty-state">No subscription requests yet.</div></td></tr>
    @endforelse
    </tbody>
</table></div><div class="pagination-wrap">{{ $subscriptions->links() }}</div></div>
@endsection
