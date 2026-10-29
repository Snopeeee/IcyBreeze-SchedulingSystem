<?php

namespace Tests\Feature;

use App\Integrations\LocalOpenWeatherKey;
use App\Integrations\OpenWeatherForecast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocalOpenWeatherKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_key_is_encrypted_and_available_only_locally(): void
    {
        $this->app['env'] = 'local';

        try {
            $store = app(LocalOpenWeatherKey::class);
            $key = str_repeat('a', 32);
            $store->save($key);

            $ciphertext = DB::table('local_integration_credentials')
                ->where('provider', 'openweather')->value('encrypted_credentials');

            $this->assertIsString($ciphertext);
            $this->assertStringNotContainsString($key, $ciphertext);
            $this->assertSame($key, $store->read());
            $this->assertTrue(app(OpenWeatherForecast::class)->available());

            $this->app['env'] = 'production';
            $this->assertNull($store->read());
            $this->assertFalse(app(OpenWeatherForecast::class)->available());
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
