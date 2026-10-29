<?php

namespace App\Http\Controllers;

use App\Integrations\LocalPayPalCredentials;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalPayPalSetupController extends Controller
{
    public function show(Request $request, LocalPayPalCredentials $credentials): View
    {
        $this->guard($request);

        return view('local.paypal-setup', ['configured' => $credentials->read() !== null]);
    }

    public function store(Request $request, LocalPayPalCredentials $credentials): RedirectResponse
    {
        $this->guard($request);

        $data = $request->validate([
            'client_id' => ['required', 'string', 'min:40', 'max:255'],
            'client_secret' => ['required', 'string', 'min:40', 'max:255'],
        ]);

        $credentials->save(trim($data['client_id']), trim($data['client_secret']));

        return redirect()->to($request->fullUrl())->with('status', 'PayPal Sandbox credentials are stored securely for this local installation.');
    }

    private function guard(Request $request): void
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);
    }
}
