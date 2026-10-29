<?php

namespace App\Http\Controllers;

use App\Integrations\GoogleCalendar;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AdminGoogleCalendarController extends Controller
{
    public function connect(Request $request, GoogleCalendar $calendar): RedirectResponse
    {
        if (! $calendar->configured()) {
            return back()->withErrors(['google_calendar' => 'Google Calendar OAuth credentials are not configured.']);
        }

        $state = Str::random(48);
        $request->session()->put('google_calendar_oauth_state', $state);

        return redirect()->away($calendar->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleCalendar $calendar): RedirectResponse
    {
        $expected = $request->session()->pull('google_calendar_oauth_state');
        $state = $request->query('state');
        abort_unless(is_string($expected) && is_string($state) && hash_equals($expected, $state), 403);

        if ($request->filled('error')) {
            return redirect()->route('admin.integrations.index')
                ->withErrors(['google_calendar' => 'Google Calendar access was not granted.']);
        }

        $code = $request->query('code');
        abort_unless(is_string($code) && $code !== '', 422);

        try {
            $calendar->connect($request->user(), $code);

            return redirect()->route('admin.integrations.index')
                ->with('success', 'Google Calendar connected. You can now sync confirmed appointments.');
        } catch (Throwable $error) {
            Log::warning('Google Calendar connection failed.', ['error_type' => get_class($error)]);

            return redirect()->route('admin.integrations.index')
                ->withErrors(['google_calendar' => 'Google Calendar could not connect. Check the OAuth client, redirect URI, and consent settings.']);
        }
    }

    public function sync(Request $request, Appointment $appointment, GoogleCalendar $calendar): RedirectResponse
    {
        try {
            $outcome = $calendar->sync($request->user(), $appointment);

            return back()->with('success', $outcome === 'removed'
                ? 'The calendar event was removed.'
                : 'The appointment was synced to your Google Calendar.');
        } catch (Throwable $error) {
            Log::warning('Google Calendar appointment sync failed.', [
                'appointment_id' => $appointment->id,
                'error_type' => get_class($error),
            ]);

            return back()->withErrors(['google_calendar' => $error->getMessage()]);
        }
    }

    public function verify(Request $request, GoogleCalendar $calendar): RedirectResponse
    {
        try {
            $calendar->verify($request->user());

            return back()->with('success', 'Google Calendar access verified. No events were changed.');
        } catch (Throwable $error) {
            Log::warning('Google Calendar verification failed.', ['error_type' => get_class($error)]);

            return back()->withErrors(['google_calendar' => 'Google Calendar access could not be verified. Reconnect and try again.']);
        }
    }
}
