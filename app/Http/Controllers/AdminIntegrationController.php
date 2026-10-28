<?php

namespace App\Http\Controllers;

use App\Integrations\GoogleAddressLookup;
use App\Integrations\OpenWeatherForecast;
use App\Integrations\PayPalCheckout;
use App\Integrations\ResendEmail;
use App\Integrations\TwilioSms;
use App\Integrations\PublicPlanningApis;
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
        PublicPlanningApis $planning,
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

        $planningData = [];
        foreach (['airQuality', 'upcomingHolidays', 'daylight'] as $feature) {
            try {
                $planningData[$feature] = $planning->{$feature}();
            } catch (Throwable $error) {
                $planningData[$feature] = null;
                Log::warning('Planning API unavailable.', ['feature' => $feature, 'error_type' => get_class($error)]);
            }
        }

        return view('admin.integrations', [
            'integrations' => [
                ['name' => 'OpenWeather', 'purpose' => 'Rain and temperature forecast for appointment planning', 'active' => $weather->available() && ! $weatherError],
                ['name' => 'Open-Meteo Air Quality', 'purpose' => 'Current outdoor AQI and fine-particle conditions in Iligan', 'active' => $planningData['airQuality'] !== null],
                ['name' => 'Nager.Date', 'purpose' => 'Philippine public holidays for scheduling awareness', 'active' => $planningData['upcomingHolidays'] !== null],
                ['name' => 'Sunrise-Sunset.org', 'purpose' => 'Daylight window for late-afternoon appointments', 'active' => $planningData['daylight'] !== null],
                ['name' => 'OpenStreetMap Nominatim', 'purpose' => 'Public Iligan City service-area lookup, on demand', 'active' => null],
            ],
            'forecast' => $forecast,
            'weatherError' => $weatherError,
            'planningData' => $planningData,
            'optionalIntegrations' => [
                ['name' => 'PayPal', 'active' => $paypal->available(), 'purpose' => 'Sandbox checkout on a non-production test environment only'],
                ['name' => 'Resend', 'active' => $resend->available(), 'purpose' => 'Email requires a verified sending domain'],
                ['name' => 'Twilio', 'active' => $twilio->available(), 'purpose' => 'SMS requires a sending number'],
                ['name' => 'Google Maps', 'active' => $maps->available(), 'purpose' => 'Private address lookup requires a configured billing project'],
            ],
        ]);
    }

    public function verifyServiceArea(PublicPlanningApis $planning): RedirectResponse
    {
        try {
            return back()->with('service_area_result', $planning->serviceArea());
        } catch (Throwable $error) {
            Log::warning('Public service-area lookup unavailable.', ['error_type' => get_class($error)]);

            return back()->withErrors(['service_area' => 'Service-area lookup is temporarily unavailable. Existing bookings are unaffected.']);
        }
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
