<?php

namespace App\Http\Controllers;

use App\Integrations\GoogleAddressLookup;
use App\Integrations\OpenWeatherForecast;
use App\Integrations\PayPalCheckout;
use App\Integrations\ResendEmail;
use App\Integrations\TwilioSms;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AdminIntegrationController extends Controller
{
    public function index(
        PayPalCheckout $paypal,
        ResendEmail $resend,
        TwilioSms $twilio,
        GoogleAddressLookup $maps,
        OpenWeatherForecast $weather,
    ): View {
        $forecast = [];
        $weatherError = false;
        if ($weather->available()) {
            try {
                $forecast = $weather->nextServiceWindows();
            } catch (Throwable $error) {
                $weatherError = true;
                Log::warning('OpenWeather forecast unavailable.', ['error_type' => get_class($error)]);
            }
        }

        return view('admin.integrations', [
            'integrations' => [
                ['name' => 'PayPal', 'purpose' => 'Customer online payment', 'active' => $paypal->available()],
                ['name' => 'Resend', 'purpose' => 'Booking confirmation email', 'active' => $resend->available()],
                ['name' => 'Twilio', 'purpose' => 'Booking confirmation SMS', 'active' => $twilio->available()],
                ['name' => 'Google Maps', 'purpose' => 'Address lookup for appointments', 'active' => $maps->available()],
                ['name' => 'OpenWeather', 'purpose' => 'Iligan service planning forecast', 'active' => $weather->available()],
            ],
            'forecast' => $forecast,
            'weatherError' => $weatherError,
        ]);
    }

    public function lookupAddress(Appointment $appointment, GoogleAddressLookup $maps): RedirectResponse
    {
        abort_unless($maps->available(), 404);

        try {
            return back()->with('location_result', $maps->lookup($appointment));
        } catch (Throwable $error) {
            Log::warning('Google Maps address lookup unavailable.', ['appointment_id' => $appointment->id, 'error_type' => get_class($error)]);

            return back()->withErrors(['address' => 'The address could not be located. Verify it directly with the customer.']);
        }
    }
}
