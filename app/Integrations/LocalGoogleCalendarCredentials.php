<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class LocalGoogleCalendarCredentials
{
    public function read(): ?array
    {
        if (! app()->environment('local')) {
            return null;
        }

        try {
            $encrypted = DB::table('local_integration_credentials')
                ->where('provider', 'google_calendar')->value('encrypted_credentials');
            $credentials = is_string($encrypted) ? json_decode(Crypt::decryptString($encrypted), true) : null;

            return is_array($credentials)
                && filled($credentials['client_id'] ?? null)
                && filled($credentials['client_secret'] ?? null) ? $credentials : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function save(string $clientId, string $clientSecret): void
    {
        abort_unless(app()->environment('local'), 404);

        DB::table('local_integration_credentials')->updateOrInsert(
            ['provider' => 'google_calendar'],
            [
                'encrypted_credentials' => Crypt::encryptString(json_encode([
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ], JSON_THROW_ON_ERROR)),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
