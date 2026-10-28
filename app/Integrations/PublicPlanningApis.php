<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PublicPlanningApis
{
    private const LATITUDE = 8.2280;
    private const LONGITUDE = 124.2452;

    public function airQuality(): array
    {
        return Cache::remember('planning:air-quality', now()->addHour(), function (): array {
            $data = Http::acceptJson()->timeout(8)->get('https://air-quality-api.open-meteo.com/v1/air-quality', [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'current' => 'us_aqi,pm2_5',
                'timezone' => 'Asia/Manila',
            ])->throw()->json();

            $aqi = data_get($data, 'current.us_aqi');
            $pm = data_get($data, 'current.pm2_5');
            if (! is_numeric($aqi) || ! is_numeric($pm)) {
                throw new RuntimeException('Air-quality data is incomplete.');
            }

            return ['aqi' => (int) $aqi, 'pm25' => round((float) $pm, 1), 'time' => data_get($data, 'current.time')];
        });
    }

    public function upcomingHolidays(): array
    {
        $year = now()->year;
        $holidays = Cache::remember('planning:holidays:'.$year, now()->addDay(), function () use ($year): array {
            $data = Http::acceptJson()->timeout(8)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH")->throw()->json();
            if (! is_array($data) || ! array_is_list($data)) {
                throw new RuntimeException('Holiday data is incomplete.');
            }

            return collect($data)->filter(fn ($holiday) => is_array($holiday) && isset($holiday['date'], $holiday['localName']))
                ->map(fn ($holiday) => ['date' => $holiday['date'], 'name' => $holiday['localName']])->all();
        });

        return collect($holidays)->filter(fn ($holiday) => $holiday['date'] >= today()->toDateString())->take(3)->values()->all();
    }

    public function daylight(): array
    {
        return Cache::remember('planning:daylight:'.today()->toDateString(), now()->addDay(), function (): array {
            $data = Http::acceptJson()->timeout(8)->get('https://api.sunrise-sunset.org/v2', [
                'lat' => self::LATITUDE,
                'lng' => self::LONGITUDE,
                'date' => today()->toDateString(),
                'tz' => 'Asia/Manila',
            ])->throw()->json();

            if (! is_string($data['sunrise'] ?? null) || ! is_string($data['sunset'] ?? null)) {
                throw new RuntimeException('Daylight data is incomplete.');
            }

            return [
                'sunrise' => \Carbon\Carbon::parse($data['sunrise'])->format('g:i A'),
                'sunset' => \Carbon\Carbon::parse($data['sunset'])->format('g:i A'),
            ];
        });
    }

    public function serviceArea(): array
    {
        // Only the public city name is sent. Never send a customer's private address to this service.
        return Cache::remember('planning:osm:iligan:v2', now()->addDays(30), function (): array {
            $data = Http::acceptJson()->withUserAgent('IcyBreezeSchoolProject/1.0 (contact: icybreezeaccleaning@gmail.com)')
                ->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => 'Iligan',
                    'countrycodes' => 'ph',
                    'format' => 'jsonv2',
                    'limit' => 1,
                ])->throw()->json();

            $latitude = (float) data_get($data, '0.lat');
            $longitude = (float) data_get($data, '0.lon');
            if (! is_array($data) || $latitude < 8.0 || $latitude > 8.4 || $longitude < 124.0 || $longitude > 124.7) {
                throw new RuntimeException('Service-area lookup did not return coordinates.');
            }

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => 'Iligan City, Philippines',
            ];
        });
    }
}
