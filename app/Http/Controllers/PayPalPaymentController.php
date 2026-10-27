<?php

namespace App\Http\Controllers;

use App\Integrations\PayPalCheckout;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayPalPaymentController extends Controller
{
    public function start(string $token, PayPalCheckout $paypal): RedirectResponse
    {
        abort_unless($paypal->available(), 404);

        $appointment = Appointment::where('manage_token', $token)->firstOrFail();
        abort_if(in_array($appointment->status, ['cancelled', 'no_show'], true), 422);
        abort_if($appointment->payment_status === 'paid', 422, 'This appointment has already been paid.');

        try {
            $approvalUrl = DB::transaction(function () use ($appointment, $paypal): string {
                $payment = Payment::where('appointment_id', $appointment->id)->lockForUpdate()->firstOrFail();
                abort_if($payment->status === 'paid', 422, 'This appointment has already been paid.');

                if ($payment->provider === 'paypal' && $payment->status === 'pending' && $payment->reference) {
                    return $paypal->approvalUrlFor($payment->reference)
                        ?? throw new \RuntimeException('PayPal approval is no longer available.');
                }

                $order = $paypal->createOrder($appointment);
                $payment->update([
                    'method' => 'paypal',
                    'provider' => 'paypal',
                    'reference' => $order['id'],
                    'status' => 'pending',
                    'notes' => 'Awaiting PayPal approval and capture.',
                ]);
                $appointment->update(['payment_status' => 'pending']);

                return $order['approval_url'];
            });

            return redirect()->away($approvalUrl);
        } catch (Throwable $error) {
            Log::error('PayPal checkout could not start.', ['appointment_id' => $appointment->id, 'error' => $error->getMessage()]);

            return redirect()->route('booking.manage', $token)->with('error', 'PayPal is temporarily unavailable. You may pay cash after service or try again later.');
        }
    }

    public function complete(Request $request, string $token, PayPalCheckout $paypal): RedirectResponse
    {
        abort_unless($paypal->available(), 404);
        $appointment = Appointment::where('manage_token', $token)->firstOrFail();
        $orderId = $request->query('token');
        abort_unless(is_string($orderId) && $orderId !== '', 422);

        try {
            DB::transaction(function () use ($appointment, $paypal, $orderId): void {
                $locked = Payment::where('appointment_id', $appointment->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->provider === 'paypal' && $locked->reference === $orderId, 422);
                if ($locked->status !== 'paid') {
                    $paypal->captureOrder($appointment, $orderId);
                    $locked->update(['status' => 'paid', 'paid_at' => now(), 'notes' => 'Captured and verified by PayPal.']);
                    $appointment->update(['payment_status' => 'paid']);
                }
            });

            return redirect()->route('booking.manage', $token)->with('success', 'Your PayPal payment is confirmed.');
        } catch (Throwable $error) {
            Log::error('PayPal payment requires reconciliation.', ['appointment_id' => $appointment->id, 'order_id' => $orderId, 'error' => $error->getMessage()]);

            return redirect()->route('booking.manage', $token)->with('error', 'Payment could not be confirmed yet. Please contact our office before trying another payment.');
        }
    }

    public function cancel(string $token): RedirectResponse
    {
        $appointment = Appointment::where('manage_token', $token)->firstOrFail();

        return redirect()->route('booking.manage', $appointment->manage_token)
            ->with('error', 'PayPal checkout was cancelled. Your booking remains confirmed. Contact our office if you prefer cash after service.');
    }
}
