<?php

namespace App\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleAddressLookup
{
    public function available(): bool
    {
        return filled(config('integrations.google_maps.api_key'));
    }

    public function lookup(Appointment $appointment): array
    {
        if (! $this->available()) {
            throw new RuntimeException('Google Maps Geocoding is not configured.');
        }

        $result = Http::acceptJson()->timeout(8)
            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => implode(', ', array_filter([
                    $appointment->address_line,
                    $appointment->barangay,
                    'Iligan City',
                    'Philippines',
                ])),
                'components' => 'country:PH',
                'key' => config('integrations.google_maps.api_key'),
            ])->throw()->json();

        if (($result['status'] ?? null) !== 'OK' || ! isset($result['results'][0])) {
            throw new RuntimeException('Google Maps could not locate this address.');
        }

        $match = $result['results'][0];
        $lat = data_get($match, 'geometry.location.lat');
        $lng = data_get($match, 'geometry.location.lng');
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            throw new RuntimeException('Google Maps returned an incomplete location.');
        }

        return [
            'address' => (string) ($match['formatted_address'] ?? ''),
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'approximate' => (bool) ($match['partial_match'] ?? false)
                || ! str_contains(strtolower((string) ($match['formatted_address'] ?? '')), 'iligan'),
        ];
    }
}
