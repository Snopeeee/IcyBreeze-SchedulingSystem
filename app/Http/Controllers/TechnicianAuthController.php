<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TechnicianAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return Auth::guard('technician')->check()
            ? redirect()->route('technician.dashboard')
            : view('technician.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $credentials['role'] = 'technician';
        $credentials['is_active'] = true;

        if (! Auth::guard('technician')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The technician email or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('technician.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('technician')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('technician.login');
    }
}
