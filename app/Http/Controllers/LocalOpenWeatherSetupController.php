<?php

namespace App\Http\Controllers;

use App\Integrations\LocalOpenWeatherKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LocalOpenWeatherSetupController extends Controller
{
    public function show(Request $request, LocalOpenWeatherKey $key): View
    {
        $this->guard($request);

        return view('local.openweather-setup', ['configured' => filled($key->read())]);
    }

    public function store(Request $request, LocalOpenWeatherKey $key): RedirectResponse
    {
        $this->guard($request);

        $data = $request->validate([
            'api_key' => ['required', 'string', 'regex:/^[a-fA-F0-9]{32}$/'],
        ]);

        $key->save($data['api_key']);
        Cache::forget('icybreeze.openweather.service_windows');

        return redirect()->to($request->fullUrl())->with('status', 'OpenWeather key is encrypted in the local database.');
    }

    private function guard(Request $request): void
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);
    }
}
