<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::with(['customer', 'service', 'airconUnitType', 'technician'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'technicians' => User::where('role', 'technician')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'paused', 'cancelled'])],
            'technician_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'technician')],
            'next_service_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $subscription->update($data);

        return back()->with('success', 'Subscription updated.');
    }
}
