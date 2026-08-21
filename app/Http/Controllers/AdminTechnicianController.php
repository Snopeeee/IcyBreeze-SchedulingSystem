<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminTechnicianController extends Controller
{
    public function index(): View
    {
        return view('admin.technicians.index', [
            'technicians' => User::where('role', 'technician')
                ->with('technicianLocation')
                ->withCount(['technicianAppointments' => fn ($query) => $query->whereDate('starts_at', today())])
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        User::create($data + ['role' => 'technician', 'is_active' => true]);

        return back()->with('success', 'Technician mobile account created.');
    }

    public function update(Request $request, User $technician): RedirectResponse
    {
        abort_unless($technician->role === 'technician', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($technician)],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['nullable', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $technician->update($data);

        return back()->with('success', 'Technician account updated.');
    }
}
