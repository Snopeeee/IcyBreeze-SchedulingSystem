<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class OpenWeatherForecast
{
    public function available(): bool
    {
        return filled($this->apiKey());
    }

    public function nextServiceWindows(): array
    {
        if (! $this->available()) {
            return [];
        }

        return Cache::remember('icybreeze.openweather.service_windows', now()->addMinutes(30), function (): array {
            try {
                $forecast = Http::acceptJson()->timeout(8)
                    ->get('https://api.openweathermap.org/data/2.5/forecast', [
                        'lat' => config('integrations.openweather.latitude'),
                        'lon' => config('integrations.openweather.longitude'),
                        'appid' => $this->apiKey(),
                        'units' => 'metric',
                        'cnt' => 24,
                    ])->throw()->json();
            } catch (ConnectionException|RequestException) {
                throw new RuntimeException('OpenWeather forecast is temporarily unavailable.');
            }

            return collect($forecast['list'] ?? [])->filter(function (array $slot): bool {
                $hour = now()->setTimestamp((int) $slot['dt'])->timezone('Asia/Manila')->hour;

                return $hour >= 9 && $hour <= 17;
            })->map(fn (array $slot): array => [
                'time' => now()->setTimestamp((int) $slot['dt'])->timezone('Asia/Manila')->format('D, M j · g:i A'),
                'temperature' => round((float) data_get($slot, 'main.temp')),
                'rain_chance' => round(((float) ($slot['pop'] ?? 0)) * 100),
                'description' => (string) data_get($slot, 'weather.0.description', 'Forecast'),
            ])
                ->take(3)->values()->all();
        });
    }

    private function apiKey(): ?string
    {
        return app(LocalOpenWeatherKey::class)->read() ?? config('integrations.openweather.api_key');
    }
}
