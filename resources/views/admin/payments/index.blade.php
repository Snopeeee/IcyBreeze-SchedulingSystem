@extends('layouts.admin')
@php($title = 'Payments')

@section('content')
<div class="admin-top"><div><h1>Payments</h1><p>Reconcile customer payments, configured service costs, and net income.</p></div><span class="admin-date"><i class="ph ph-money"></i> Cash and verified PayPal payments</span></div>
<div class="admin-page-card"><div class="admin-table-wrap"><table class="admin-table">
    <thead><tr><th>Date</th><th>Booking / customer</th><th>Aircon</th><th>Method</th><th>Customer amount</th><th>Service cost</th><th>Net</th><th>Status / update</th></tr></thead>
    <tbody>
    @forelse($payments as $payment)
        <tr>
            <td><strong>{{ $payment->created_at->format('M j, Y') }}</strong>{{ $payment->created_at->format('g:i A') }}</td>
            <td><a class="text-link" href="{{ route('admin.appointments.show',$payment->appointment) }}">{{ $payment->appointment->reference }}</a><strong>{{ $payment->appointment->customer->full_name }}</strong>{{ $payment->appointment->customer->phone }}</td>
            <td><strong>{{ $payment->appointment->unit_type }}</strong>{{ $payment->appointment->quantity }} {{ Str::plural('unit', $payment->appointment->quantity) }}</td>
            <td><strong>{{ Str::title($payment->method) }}</strong>{{ $payment->reference ?: ($payment->provider ?: 'Manual') }}</td>
            <td><strong>{{ $payment->formatted_amount }}</strong></td>
            <td><strong>{{ $payment->appointment->formatted_technician_share }}</strong></td>
            <td><strong>{{ $payment->appointment->formatted_gross }}</strong></td>
            <td>
                @if($payment->provider === 'paypal')
                    <span class="status-badge status-{{ $payment->status }}">{{ Str::title($payment->status) }}</span>
                    @if($payment->status === 'pending' && app(\App\Integrations\PayPalCheckout::class)->available())
                        <form method="POST" action="{{ route('admin.payments.verify-paypal', $payment) }}" style="margin-top:6px">@csrf<button class="button button-ghost button-small" type="submit">Verify with PayPal</button></form>
                        <form method="POST" action="{{ route('admin.payments.switch-to-cash', $payment) }}" style="margin-top:6px" onsubmit="return confirm('Check that no PayPal payment was captured before switching to cash?')">@csrf<button class="button button-ghost button-small" type="submit">Switch to cash</button></form>
                    @else
                        <small>Provider confirmed</small>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.payments.update',$payment) }}" style="display:flex;gap:6px">@csrf @method('PATCH')<select name="status" aria-label="Payment status" style="min-height:34px;border:1px solid #ccdee7;border-radius:6px;">@foreach(['unpaid','pending','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected($payment->status===$status)>{{ Str::title($status) }}</option>@endforeach</select><button class="button button-navy button-small" style="min-height:34px">Save</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="8"><div class="empty-state">No payment records.</div></td></tr>
    @endforelse
    </tbody>
</table></div><div class="pagination-wrap">{{ $payments->links() }}</div></div>
@endsection
