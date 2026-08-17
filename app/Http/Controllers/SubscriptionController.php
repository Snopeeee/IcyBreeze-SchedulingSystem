<?php

namespace App\Http\Controllers;

use App\Models\AirconUnitType;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function create(): View
    {
        return view('subscriptions.create', [
            'service' => Service::bookable()->firstOrFail(),
            'unitTypes' => AirconUnitType::bookable()->get(),
            'plans' => $this->plans(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_keys($this->plans()))],
            'aircon_unit_type_id' => [
                'required',
                Rule::exists('aircon_unit_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
            'next_service_date' => ['required', 'date', 'after_or_equal:tomorrow'],
            'preferred_day' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
            'preferred_time' => ['required', Rule::in(['08:00', '10:00', '13:00', '15:00'])],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:180'],
            'barangay' => ['required', 'string', 'max:100'],
            'city' => ['required', Rule::in(['Iligan City'])],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'landmark' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'location_consent' => ['exclude_without:latitude', 'exclude_without:longitude', 'accepted'],
            'terms' => ['accepted'],
        ]);

        $service = Service::bookable()->firstOrFail();
        $unitType = AirconUnitType::bookable()->findOrFail($data['aircon_unit_type_id']);
        $plan = $this->plans()[$data['plan']];
        $price = (int) round($unitType->price_centavos * $data['quantity'] * $plan['discount']);
        $technicianShare = $unitType->technician_share_centavos * $data['quantity'];
        $gross = max($price - $technicianShare, 0);

        $subscription = DB::transaction(function () use ($data, $service, $unitType, $plan, $price, $technicianShare, $gross) {
            $customer = Customer::updateOrCreate(
                ['email' => Str::lower($data['email'])],
                collect($data)->only(['first_name', 'last_name', 'phone'])->all(),
            );

            do {
                $reference = 'SUB-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
            } while (Subscription::where('reference', $reference)->exists());

            return Subscription::create([
                'reference' => $reference,
                'manage_token' => Str::random(48),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'aircon_unit_type_id' => $unitType->id,
                'plan' => $data['plan'],
                'unit_type' => $unitType->name,
                'interval_months' => $plan['interval_months'],
                'quantity' => $data['quantity'],
                'unit_price_centavos' => $unitType->price_centavos,
                'price_per_visit_centavos' => $price,
                'technician_share_per_visit_centavos' => $technicianShare,
                'gross_per_visit_centavos' => $gross,
                'status' => 'pending',
                'next_service_date' => $data['next_service_date'],
                'preferred_day' => $data['preferred_day'],
                'preferred_time' => $data['preferred_time'],
                'address_line' => $data['address_line'],
                'barangay' => $data['barangay'],
                'city' => 'Iligan City',
                'province' => 'Lanao del Norte',
                'postal_code' => $data['postal_code'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_consent_at' => isset($data['latitude'], $data['longitude']) ? now() : null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('subscriptions.success', [
            'reference' => $subscription->reference,
            'token' => $subscription->manage_token,
        ]);
    }

    public function success(Request $request, string $reference): View
    {
        $subscription = Subscription::with(['customer', 'service', 'airconUnitType'])
            ->where('reference', $reference)
            ->where('manage_token', $request->query('token'))
            ->firstOrFail();

        return view('subscriptions.success', compact('subscription'));
    }

    private function plans(): array
    {
        return [
            'quarterly_care' => ['name' => 'Quarterly Care', 'interval_months' => 3, 'discount' => .90, 'visits' => 4],
            'biannual_care' => ['name' => 'Biannual Care', 'interval_months' => 6, 'discount' => .95, 'visits' => 2],
        ];
    }
}
