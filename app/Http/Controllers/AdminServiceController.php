<?php

namespace App\Http\Controllers;

use App\Models\AirconUnitType;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'service' => Service::bookable()->firstOrFail(),
            'unitTypes' => AirconUnitType::bookable()->get(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        abort_unless($service->slug === Service::STANDARD_SLUG, 404);

        $data = $request->validate([
            'short_description' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:1200'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:480'],
            'buffer_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'rates' => ['required', 'array'],
            'rates.*.price' => ['required', 'numeric', 'min:1'],
            'rates.*.technician_share' => ['required', 'numeric', 'min:0'],
        ]);

        $unitTypes = AirconUnitType::bookable()->get();
        foreach ($unitTypes as $unitType) {
            $rate = $data['rates'][$unitType->id] ?? null;
            if (! $rate) {
                throw ValidationException::withMessages([
                    'rates' => 'Every active aircon unit type needs a price.',
                ]);
            }

            if ((float) $rate['technician_share'] > (float) $rate['price']) {
                throw ValidationException::withMessages([
                    "rates.{$unitType->id}.technician_share" => "The technician share for {$unitType->name} cannot exceed its customer price.",
                ]);
            }
        }

        DB::transaction(function () use ($data, $service, $unitTypes) {
            $minimumPrice = null;

            foreach ($unitTypes as $unitType) {
                $rate = $data['rates'][$unitType->id];
                $price = (int) round($rate['price'] * 100);
                $technicianShare = (int) round($rate['technician_share'] * 100);

                $unitType->update([
                    'price_centavos' => $price,
                    'technician_share_centavos' => $technicianShare,
                ]);

                $minimumPrice = $minimumPrice === null ? $price : min($minimumPrice, $price);
            }

            $service->update([
                'name' => 'Standard Cleaning',
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'price_centavos' => $minimumPrice,
                'duration_minutes' => $data['duration_minutes'],
                'buffer_minutes' => $data['buffer_minutes'],
                'is_active' => true,
                'sort_order' => 1,
            ]);
        });

        return back()->with('success', 'Standard Cleaning and all unit prices were updated.');
    }
}
