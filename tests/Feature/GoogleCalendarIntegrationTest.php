<?php

namespace Tests\Feature;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_calendar_connection_requires_admin_and_valid_oauth_state(): void
    {
        config()->set('integrations.google_calendar.client_id', 'test.apps.googleusercontent.com');
        config()->set('integrations.google_calendar.client_secret', 'secret-test');

        $this->post('/admin/integrations/google-calendar/connect')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $start = $this->post('/admin/integrations/google-calendar/connect');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $start->headers->get('Location'));
        $this->assertStringContainsString('calendar.events.owned', urldecode($start->headers->get('Location')));

        $state = session('google_calendar_oauth_state');
        $this->get('/admin/integrations/google-calendar/callback?state=wrong&code=test')->assertForbidden();

        $this->post('/admin/integrations/google-calendar/connect');
        $state = session('google_calendar_oauth_state');
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['refresh_token' => 'refresh-test'], 200)]);
        $this->get('/admin/integrations/google-calendar/callback?state='.$state.'&code=code-test')
            ->assertRedirect(route('admin.integrations.index'));

        $this->assertDatabaseHas('google_calendar_connections', ['user_id' => $admin->id]);
        $this->assertNotSame('refresh-test', DB::table('google_calendar_connections')->value('refresh_token'));
        $this->assertSame('refresh-test', GoogleCalendarConnection::firstOrFail()->refresh_token);
        Http::assertSentCount(1);
    }

    public function test_admin_syncs_only_reference_and_schedule_without_customer_details(): void
    {
        $this->seed();
        $this->post('/book', [
            'aircon_unit_type_id' => AirconUnitType::where('slug', 'split-type')->firstOrFail()->id,
            'quantity' => 1,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '09:00',
            'first_name' => 'Mika', 'last_name' => 'Dela Cruz',
            'email' => 'mika@example.test', 'phone' => '09171234567',
            'address_line' => '12 Sampaguita Street', 'barangay' => 'Pala-o',
            'city' => 'Iligan City', 'landmark' => 'Near the park',
            'payment_method' => 'cash', 'terms' => '1',
        ])->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        config()->set('integrations.google_calendar.client_id', 'test.apps.googleusercontent.com');
        config()->set('integrations.google_calendar.client_secret', 'secret-test');
        GoogleCalendarConnection::create(['user_id' => $admin->id, 'refresh_token' => 'refresh-test']);
        $eventId = 'ib'.substr(hash('sha256', $appointment->reference), 0, 32);

        Http::fake(function (Request $request) use ($eventId) {
            if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
                return Http::response(['access_token' => 'access-test'], 200);
            }
            if ($request->method() === 'GET') {
                return Http::response([], 404);
            }
            if ($request->method() === 'POST' && str_contains($request->url(), '/calendar/v3/')) {
                return Http::response(['id' => $eventId], 200);
            }
            if ($request->method() === 'DELETE') {
                return Http::response('', 204);
            }

            return Http::response([], 500);
        });

        $this->actingAs($admin)->post('/admin/appointments/'.$appointment->id.'/sync-calendar')
            ->assertRedirect()->assertSessionHasNoErrors();

        Http::assertSent(function (Request $request) use ($eventId): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/calendar/v3/')) {
                return false;
            }
            $body = json_encode($request->data());

            return $request['id'] === $eventId
                && $request['visibility'] === 'private'
                && data_get($request->data(), 'start.timeZone') === 'Asia/Manila'
                && str_ends_with((string) data_get($request->data(), 'start.dateTime'), '+08:00')
                && str_contains((string) data_get($request->data(), 'start.dateTime'), 'T09:00:00')
                && ! str_contains($body, 'mika@example.test')
                && ! str_contains($body, '09171234567')
                && ! str_contains($body, 'Sampaguita Street');
        });

        $appointment->update(['status' => 'cancelled']);
        $this->post('/admin/appointments/'.$appointment->id.'/sync-calendar')
            ->assertRedirect()->assertSessionHasNoErrors();
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && str_contains($request->url(), $eventId));
    }

    public function test_admin_can_verify_calendar_access_without_loading_event_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        config()->set('integrations.google_calendar.client_id', 'test.apps.googleusercontent.com');
        config()->set('integrations.google_calendar.client_secret', 'secret-test');
        GoogleCalendarConnection::create(['user_id' => $admin->id, 'refresh_token' => 'refresh-test']);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-test'], 200),
            'www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response(['kind' => 'calendar#events'], 200),
        ]);

        $this->actingAs($admin)->post('/admin/integrations/google-calendar/verify')
            ->assertRedirect()->assertSessionHas('success', 'Google Calendar access verified. No events were changed.');

        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_contains($request->url(), 'fields=kind')
            && str_contains($request->url(), 'maxResults=1'));
        Http::assertSentCount(2);
    }
}
