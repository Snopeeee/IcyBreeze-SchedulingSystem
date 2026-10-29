<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class LocalOpenWeatherKey
{
    public function read(): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        try {
            $encrypted = DB::table('local_integration_credentials')
                ->where('provider', 'openweather')->value('encrypted_credentials');

            return is_string($encrypted) ? Crypt::decryptString($encrypted) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function save(string $key): void
    {
        abort_unless(app()->environment('local'), 404);

        DB::table('local_integration_credentials')->updateOrInsert(
            ['provider' => 'openweather'],
            [
                'encrypted_credentials' => Crypt::encryptString($key),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
