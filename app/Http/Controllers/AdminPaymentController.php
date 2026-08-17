<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payments.index', ['payments' => Payment::with(['appointment.customer', 'appointment.airconUnitType'])->latest()->paginate(20)]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['unpaid', 'pending', 'paid', 'failed', 'refunded'])]]);
        $payment->update(['status' => $data['status'], 'paid_at' => $data['status'] === 'paid' ? now() : null]);
        $payment->appointment->update(['payment_status' => $data['status']]);

        return back()->with('success', 'Payment status updated.');
    }
}
