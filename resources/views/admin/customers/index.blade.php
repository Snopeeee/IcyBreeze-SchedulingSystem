@extends('layouts.admin')
@php($title = 'Customers')
@section('content')
<div class="admin-top"><div><h1>Customers</h1><p>A lightweight customer directory built from appointment activity.</p></div></div>
<form class="admin-filters" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Search name, email, or phone"><button class="button button-navy button-small">Search</button>@if(request('q'))<a class="button button-ghost button-small" href="{{ route('admin.customers.index') }}">Clear</a>@endif</form>
<div class="admin-page-card"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Customer</th><th>Contact</th><th>Appointments</th><th>First recorded</th></tr></thead><tbody>@forelse($customers as $customer)<tr><td><strong>{{ $customer->full_name }}</strong>#{{ str_pad($customer->id,4,'0',STR_PAD_LEFT) }}</td><td><strong>{{ $customer->phone }}</strong>{{ $customer->email }}</td><td><strong>{{ $customer->appointments_count }}</strong>{{ Str::plural('booking',$customer->appointments_count) }}</td><td>{{ $customer->created_at->format('M j, Y') }}</td></tr>@empty<tr><td colspan="4"><div class="empty-state">No matching customers.</div></td></tr>@endforelse</tbody></table></div><div class="pagination-wrap">{{ $customers->links() }}</div></div>
@endsection
