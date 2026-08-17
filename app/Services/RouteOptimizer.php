<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RouteOptimizer
{
    public function optimize(Collection $appointments, float $originLatitude, float $originLongitude): Collection
    {
        $ordered = collect();
        $latitude = $originLatitude;
        $longitude = $originLongitude;

        $appointments->sortBy('starts_at')
            ->groupBy(fn ($appointment) => $appointment->starts_at->timestamp)
            ->sortKeys()
            ->each(function (Collection $timeWindow) use (&$ordered, &$latitude, &$longitude) {
                $withGps = $timeWindow->filter->has_gps->values();

                while ($withGps->isNotEmpty()) {
                    $nearest = $withGps->sortBy(fn ($appointment) => $this->distance(
                        $latitude,
                        $longitude,
                        (float) $appointment->latitude,
                        (float) $appointment->longitude,
                    ))->first();

                    $ordered->push($nearest);
                    $latitude = (float) $nearest->latitude;
                    $longitude = (float) $nearest->longitude;
                    $withGps = $withGps->reject(fn ($appointment) => $appointment->is($nearest))->values();
                }

                $ordered = $ordered->concat($timeWindow->reject->has_gps->sortBy('reference'));
            });

        return $ordered->values();
    }

    public function directionsUrl(Collection $appointments, float $originLatitude, float $originLongitude): ?string
    {
        $points = $appointments->filter->has_gps->values();
        if ($points->isEmpty()) {
            return null;
        }

        $destination = $points->last();
        $waypoints = $points->slice(0, -1)->map(fn ($appointment) => $appointment->latitude.','.$appointment->longitude)->implode('|');
        $parameters = [
            'api' => 1,
            'origin' => $originLatitude.','.$originLongitude,
            'destination' => $destination->latitude.','.$destination->longitude,
            'travelmode' => 'driving',
        ];
        if ($waypoints !== '') {
            $parameters['waypoints'] = $waypoints;
        }

        return 'https://www.google.com/maps/dir/?'.http_build_query($parameters);
    }

    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
