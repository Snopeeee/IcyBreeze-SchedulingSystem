<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function create(): View
    {
        return view('subscriptions.create');
    }

    public function success(Request $request, string $reference): View
    {
        $subscription = Subscription::with(['customer', 'service', 'airconUnitType'])
            ->where('reference', $reference)
            ->where('manage_token', $request->query('token'))
            ->firstOrFail();

        return view('subscriptions.success', compact('subscription'));
    }
}
