<?php

namespace App\Http\Controllers;

use App\Integrations\GoogleCalendar;
use App\Integrations\OpenWeatherForecast;
use App\Integrations\PayPalCheckout;
use App\Integrations\PublicPlanningApis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AdminIntegrationController extends Controller
{
    public function index(
        Request $request,
        PayPalCheckout $paypal,
        OpenWeatherForecast $weather,
        PublicPlanningApis $planning,
        GoogleCalendar $calendar,
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

        try {
            $airQuality = $planning->airQuality();
        } catch (Throwable $error) {
            $airQuality = null;
            Log::warning('Open-Meteo air-quality API unavailable.', ['error_type' => get_class($error)]);
        }

        $calendarConfigured = $calendar->configured();
        $calendarConnected = $calendar->connected($request->user());
        $weatherConfigured = $weather->available();
        $paypalConfigured = $paypal->available();

        return view('admin.integrations', [
            'integrations' => [
                ['name' => 'PayPal Sandbox', 'purpose' => 'Test-only appointment checkout; no real money is charged', 'status' => $paypalConfigured ? 'Configured' : 'Not configured', 'active' => $paypalConfigured],
                ['name' => 'OpenWeather', 'purpose' => 'Rain and temperature forecast for appointment planning', 'status' => $weatherError ? 'Temporarily unavailable' : ($weatherConfigured ? 'Connected' : 'Not configured'), 'active' => $weatherConfigured && ! $weatherError],
                ['name' => 'Google Calendar', 'purpose' => 'Administrator-approved appointment synchronization', 'status' => $calendarConnected ? 'Connected' : ($calendarConfigured ? 'Authorization needed' : 'Not configured'), 'active' => $calendarConnected],
                ['name' => 'Open-Meteo Air Quality', 'purpose' => 'Outdoor air-quality conditions in Iligan City', 'status' => $airQuality !== null ? 'Connected' : 'Temporarily unavailable', 'active' => $airQuality !== null],
                ['name' => 'OpenStreetMap Nominatim', 'purpose' => 'On-demand verification of the public Iligan City service area', 'status' => $request->session()->has('service_area_result') ? 'Verified' : 'Check on demand', 'active' => $request->session()->has('service_area_result')],
            ],
            'forecast' => $forecast,
            'weatherError' => $weatherError,
            'weatherConfigured' => $weatherConfigured,
            'airQuality' => $airQuality,
            'calendarConfigured' => $calendarConfigured,
            'calendarConnected' => $calendarConnected,
            'paypalConfigured' => $paypalConfigured,
        ]);
    }

    public function verifyPayPal(PayPalCheckout $paypal): RedirectResponse
    {
        if (! $paypal->available()) {
            return back()->withErrors(['paypal' => 'PayPal Sandbox is not configured for this local test environment.']);
        }

        try {
            $paypal->verifySandboxConnection();

            return back()->with('success', 'PayPal Sandbox credentials verified. No payment or test order was created.');
        } catch (Throwable $error) {
            Log::warning('PayPal Sandbox connection unavailable.', ['error_type' => get_class($error)]);

            return back()->withErrors(['paypal' => 'PayPal Sandbox could not be reached. Check your connection and try again. No payment was created.']);
        }
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
}
