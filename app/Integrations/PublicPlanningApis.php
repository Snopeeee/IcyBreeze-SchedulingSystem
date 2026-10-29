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
