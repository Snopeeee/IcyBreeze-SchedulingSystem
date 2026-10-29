<?php

namespace App\Integrations;

use App\Models\Appointment;
use App\Models\GoogleCalendarConnection;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCalendar
{
    private const SCOPE = 'https://www.googleapis.com/auth/calendar.events.owned';

    public function configured(): bool
    {
        $credentials = $this->credentials();

        return filled($credentials['client_id']) && filled($credentials['client_secret']);
    }

    public function connected(User $admin): bool
    {
        return $this->configured() && GoogleCalendarConnection::where('user_id', $admin->id)->exists();
    }

    public function authorizationUrl(string $state): string
    {
        abort_unless($this->configured(), 404);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $this->credentials()['client_id'],
            'redirect_uri' => route('admin.google-calendar.callback'),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(User $admin, string $code): void
    {
        abort_unless($this->configured(), 404);

        try {
            $response = Http::asForm()->acceptJson()->timeout(12)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => $this->credentials()['client_id'],
                    'client_secret' => $this->credentials()['client_secret'],
                    'redirect_uri' => route('admin.google-calendar.callback'),
                    'grant_type' => 'authorization_code',
                ]);
        } catch (\Throwable) {
            throw new RuntimeException('Google Calendar authorization could not be completed.');
        }

        $token = $response->json('refresh_token');
        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new RuntimeException('Google Calendar did not grant offline access. Please reconnect and approve the requested scope.');
        }

        GoogleCalendarConnection::updateOrCreate(
            ['user_id' => $admin->id],
            ['refresh_token' => $token],
        );
    }

    public function verify(User $admin): void
    {
        $connection = GoogleCalendarConnection::where('user_id', $admin->id)->first();
        if (! $this->configured() || $connection === null) {
            throw new RuntimeException('Connect your Google Calendar before verifying it.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($this->accessToken($connection))
                ->timeout(12)
                ->get($this->eventsUrl(), ['fields' => 'kind', 'maxResults' => 1]);
        } catch (\Throwable) {
            throw new RuntimeException('Google Calendar verification could not be completed.');
        }

        if (! $response->successful() || $response->json('kind') !== 'calendar#events') {
            throw new RuntimeException('Google Calendar access could not be verified. Reconnect and try again.');
        }
    }

    public function sync(User $admin, Appointment $appointment): string
    {
        $connection = GoogleCalendarConnection::where('user_id', $admin->id)->first();
        if (! $this->configured() || $connection === null) {
            throw new RuntimeException('Connect your Google Calendar before syncing appointments.');
        }

        $eventId = 'ib'.substr(hash('sha256', $appointment->reference), 0, 32);
        $url = $this->eventsUrl();
        $http = Http::acceptJson()->withToken($this->accessToken($connection))->timeout(12);

        try {
            if (in_array($appointment->status, ['cancelled', 'no_show'], true)) {
                $response = $http->delete($url.'/'.$eventId);
                if (! $response->successful() && $response->status() !== 404) {
                    throw new RuntimeException();
                }

                return 'removed';
            }

            if (! in_array($appointment->status, ['confirmed', 'assigned', 'in_progress', 'completed'], true)) {
                throw new RuntimeException('Only confirmed appointments can be synced.');
            }

            $payload = [
                'id' => $eventId,
                'summary' => 'IcyBreeze Aircon Cleaning · '.$appointment->reference,
                'description' => 'Booking reference: '.$appointment->reference."\nStatus: ".str_replace('_', ' ', $appointment->status),
                'start' => ['dateTime' => $this->localDateTime($appointment->starts_at), 'timeZone' => 'Asia/Manila'],
                'end' => ['dateTime' => $this->localDateTime($appointment->ends_at), 'timeZone' => 'Asia/Manila'],
                'visibility' => 'private',
            ];

            $existing = $http->get($url.'/'.$eventId);
            if ($existing->successful()) {
                $saved = $http->put($url.'/'.$eventId, $payload);
            } elseif ($existing->status() === 404) {
                $saved = $http->post($url, $payload);
                if ($saved->status() === 409) {
                    $saved = $http->put($url.'/'.$eventId, $payload);
                }
            } else {
                throw new RuntimeException();
            }

            if (! $saved->successful() || $saved->json('id') !== $eventId) {
                throw new RuntimeException();
            }

            return 'synced';
        } catch (RuntimeException $error) {
            if ($error->getMessage() === 'Only confirmed appointments can be synced.') {
                throw $error;
            }
            throw new RuntimeException('Google Calendar could not sync this appointment.');
        } catch (\Throwable) {
            throw new RuntimeException('Google Calendar could not sync this appointment.');
        }
    }

    private function accessToken(GoogleCalendarConnection $connection): string
    {
        try {
            $response = Http::asForm()->acceptJson()->timeout(12)
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $this->credentials()['client_id'],
                    'client_secret' => $this->credentials()['client_secret'],
                    'refresh_token' => $connection->refresh_token,
                    'grant_type' => 'refresh_token',
                ]);
        } catch (\Throwable) {
            throw new RuntimeException('Google Calendar token refresh failed.');
        }

        $token = $response->json('access_token');
        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new RuntimeException('Google Calendar authorization expired. Reconnect your calendar.');
        }

        return $token;
    }

    private function credentials(): array
    {
        return app(LocalGoogleCalendarCredentials::class)->read() ?? [
            'client_id' => config('integrations.google_calendar.client_id'),
            'client_secret' => config('integrations.google_calendar.client_secret'),
        ];
    }

    private function eventsUrl(): string
    {
        return 'https://www.googleapis.com/calendar/v3/calendars/'
            .rawurlencode((string) config('integrations.google_calendar.calendar_id', 'primary'))
            .'/events';
    }

    private function localDateTime(\DateTimeInterface $value): string
    {
        return CarbonImmutable::parse($value->format('Y-m-d H:i:s'), 'Asia/Manila')->toIso8601String();
    }
}
