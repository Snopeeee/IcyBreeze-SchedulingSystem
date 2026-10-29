<?php

namespace App\Http\Controllers;

use App\Integrations\LocalGoogleCalendarCredentials;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalGoogleCalendarSetupController extends Controller
{
    public function show(Request $request, LocalGoogleCalendarCredentials $credentials): View
    {
        $this->guard($request);

        return view('local.google-calendar-setup', ['configured' => $credentials->read() !== null]);
    }

    public function store(Request $request, LocalGoogleCalendarCredentials $credentials): RedirectResponse
    {
        $this->guard($request);

        $data = $request->validate([
            'client_id' => ['required', 'string', 'ends_with:.apps.googleusercontent.com', 'max:255'],
            'client_secret' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $credentials->save(trim($data['client_id']), trim($data['client_secret']));

        return redirect()->to($request->fullUrl())->with('status', 'Google Calendar OAuth credentials are encrypted in local MySQL.');
    }

    private function guard(Request $request): void
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);
    }
}
