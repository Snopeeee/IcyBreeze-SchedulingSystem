<?php

namespace App\Http\Controllers;

use App\Integrations\PayPalCheckout;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AdminPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payments.index', ['payments' => Payment::with(['appointment.customer', 'appointment.airconUnitType'])->latest()->paginate(20)]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        abort_if($payment->provider === 'paypal', 422, 'PayPal payments are updated only after provider confirmation.');
        $data = $request->validate(['status' => ['required', Rule::in(['unpaid', 'pending', 'paid', 'failed', 'refunded'])]]);
        $payment->update(['status' => $data['status'], 'paid_at' => $data['status'] === 'paid' ? now() : null]);
        $payment->appointment->update(['payment_status' => $data['status']]);

        return back()->with('success', 'Payment status updated.');
    }

    public function reconcile(Payment $payment, PayPalCheckout $paypal): RedirectResponse
    {
        abort_unless($paypal->available(), 404);
        abort_unless($payment->provider === 'paypal' && $payment->reference && $payment->status === 'pending', 422);

        try {
            DB::transaction(function () use ($payment, $paypal): void {
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($locked->provider === 'paypal' && $locked->status === 'pending') {
                    $paypal->verifyCapturedOrder($locked->appointment, $locked->reference);
                    $locked->update(['status' => 'paid', 'paid_at' => now(), 'notes' => 'Capture verified with PayPal by admin.']);
                    $locked->appointment->update(['payment_status' => 'paid']);
                }
            });

            return back()->with('success', 'PayPal payment verified and marked paid.');
        } catch (Throwable $error) {
            Log::warning('PayPal payment verification failed.', ['payment_id' => $payment->id, 'error' => $error->getMessage()]);

            return back()->withErrors(['paypal' => 'PayPal has not confirmed a completed payment for this order.']);
        }
    }

    public function switchToCash(Payment $payment, PayPalCheckout $paypal): RedirectResponse
    {
        abort_unless($paypal->available(), 404);
        abort_unless($payment->provider === 'paypal' && $payment->reference && $payment->status === 'pending', 422);

        try {
            DB::transaction(function () use ($payment, $paypal): void {
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->provider === 'paypal' && $locked->status === 'pending', 422);
                $order = $paypal->orderDetails($locked->reference);
                if (data_get($order, 'id') !== $locked->reference
                    || ! in_array(data_get($order, 'status'), ['CREATED', 'SAVED', 'PAYER_ACTION_REQUIRED', 'APPROVED', 'VOIDED'], true)
                    || count(data_get($order, 'purchase_units.0.payments.captures', [])) > 0) {
                    throw new \RuntimeException('This PayPal order cannot safely be switched to cash.');
                }

                $locked->update(['method' => 'cash', 'provider' => null, 'reference' => null, 'status' => 'unpaid', 'notes' => null]);
                $locked->appointment->update(['payment_status' => 'unpaid']);
            });

            return back()->with('success', 'Uncaptured PayPal order verified. This booking is now set to cash after service.');
        } catch (Throwable $error) {
            Log::warning('PayPal order could not switch to cash.', ['payment_id' => $payment->id, 'error' => $error->getMessage()]);

            return back()->withErrors(['paypal' => 'The PayPal order could not be verified as uncaptured. Leave it pending and contact PayPal support.']);
        }
    }
}
