<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Integrations\PayPalCheckout;
use App\Models\Appointment;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (app()->environment('local')) {
    Artisan::command('paypal:smoke', function (PayPalCheckout $paypal) {
        if (! $paypal->available()) {
            $this->error('Local PayPal Sandbox is not configured.');
            return 1;
        }

        try {
            $appointment = new Appointment([
                'reference' => 'IB-SANDBOX-'.Str::upper(Str::random(8)),
                'manage_token' => Str::random(48),
                'total_centavos' => 70000,
            ]);
            $order = $paypal->createOrder($appointment);
            $details = $paypal->orderDetails($order['id']);

            if (data_get($details, 'id') !== $order['id'] || data_get($details, 'status') !== 'PAYER_ACTION_REQUIRED') {
                $this->error('Sandbox order was created, but its details did not match the expected status.');
                return 1;
            }

            $this->info('PayPal Sandbox OAuth, order creation, and order retrieval succeeded. No payment was captured.');
            return 0;
        } catch (\Throwable $error) {
            $this->error('PayPal Sandbox smoke test failed ('.class_basename($error).').');
            return 1;
        }
    })->purpose('Check PayPal Sandbox with a non-captured test order (local only)');
}
