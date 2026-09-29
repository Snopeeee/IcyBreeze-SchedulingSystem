@extends('layouts.admin')
@php($title = 'Subscriptions')

@section('content')
<div class="admin-top"><div><h1>Maintenance Plan Records</h1><p>Review legacy records and prepare for future recurring-service offerings. Public enrollment is currently unavailable.</p></div><a class="button button-navy button-small" href="{{ route('subscriptions.create') }}" target="_blank"><i class="ph ph-arrow-square-out"></i> View Coming Soon Page</a></div>
<form class="admin-filters" method="GET"><select aria-label="Filter by status" name="status"><option value="">All statuses</option>@foreach(['pending','active','paused','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ Str::title($status) }}</option>@endforeach</select><button class="button button-navy button-small">Apply</button>@if(request('status'))<a class="button button-ghost button-small" href="{{ route('admin.subscriptions.index') }}">Clear</a>@endif</form>
<div class="admin-page-card"><div class="admin-table-wrap"><table class="admin-table">
    <thead><tr><th>Plan / customer</th><th>Aircon</th><th>Per visit finances</th><th>Location</th><th>Next visit</th><th>Update plan</th></tr></thead>
    <tbody>
    @forelse($subscriptions as $subscription)
        <tr>
            <td><strong>{{ $subscription->reference }} · {{ $subscription->plan_label }}</strong>{{ $subscription->customer->full_name }} · every {{ $subscription->interval_months }} months</td>
            <td><strong>{{ $subscription->unit_type ?? 'Split Type' }}</strong>{{ $subscription->quantity }} {{ Str::plural('unit',$subscription->quantity) }}</td>
            <td><strong>{{ $subscription->formatted_price }} customer</strong>{{ $subscription->formatted_technician_share }} service cost · {{ $subscription->formatted_gross }} net</td>
            <td><strong>{{ $subscription->barangay }}, Iligan</strong>{{ $subscription->address_line }}</td>
            <td><strong>{{ $subscription->next_service_date?->format('M j, Y') ?? 'Not set' }}</strong>{{ $subscription->preferred_day }} · {{ \Carbon\Carbon::createFromFormat('H:i',$subscription->preferred_time)->format('g:i A') }}</td>
            <td><form method="POST" action="{{ route('admin.subscriptions.update',$subscription) }}" class="table-update-form">@csrf @method('PATCH')<select name="status" aria-label="Plan status">@foreach(['pending','active','paused','cancelled'] as $status)<option value="{{ $status }}" @selected($subscription->status===$status)>{{ Str::title($status) }}</option>@endforeach</select><input type="date" aria-label="Next service date" name="next_service_date" value="{{ $subscription->next_service_date?->format('Y-m-d') }}" min="{{ today()->format('Y-m-d') }}"><button class="button button-navy button-small" style="min-height:34px">Save</button></form></td>
        </tr>
    @empty
        <tr><td colspan="6"><div class="empty-state">No subscription requests yet.</div></td></tr>
    @endforelse
    </tbody>
</table></div><div class="pagination-wrap">{{ $subscriptions->links() }}</div></div>
@endsection
