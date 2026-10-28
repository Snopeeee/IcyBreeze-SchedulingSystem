<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'customer.dashboard') : view('customer.auth', ['register' => false]);
    }

    public function showRegister(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'customer.dashboard') : view('customer.auth', ['register' => true]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')->with('success', 'Your customer account is ready.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($data + ['role' => 'customer', 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function dashboard(Request $request): View
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->is_active, 403);

        return view('customer.dashboard', [
            'appointments' => $request->user()->appointments()->with(['airconUnitType', 'payments'])->latest()->paginate(10),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
