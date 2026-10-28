<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AirconUnitType;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfigured_apis_do_not_change_cash_booking_or_make_external_calls(): void
    {
        $this->seed();
        Http::fake();

        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();

        $this->assertSame('unpaid', $appointment->payment_status);
        $this->assertSame('cash', Payment::firstOrFail()->method);
        $this->get('/manage-booking/'.$appointment->manage_token)->assertOk()->assertDontSee('Pay securely with PayPal');
        $this->post('/manage-booking/'.$appointment->manage_token.'/paypal')->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_paypal_requires_a_matching_completed_capture_before_marking_paid(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'ORDER-123',
                'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123']],
            ], 201),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123/capture' => Http::response($this->capture($appointment), 201),
        ]);

        $this->get('/manage-booking/'.$appointment->manage_token)->assertOk()->assertSee('Pay securely with PayPal');
        $this->post('/manage-booking/'.$appointment->manage_token.'/paypal')
            ->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123');
        $this->assertDatabaseHas('payments', ['appointment_id' => $appointment->id, 'provider' => 'paypal', 'status' => 'pending']);

        $this->get('/manage-booking/'.$appointment->manage_token.'/paypal/return?token=ORDER-123')->assertRedirect();
        $this->assertSame('paid', $appointment->fresh()->payment_status);
        $this->assertSame('paid', Payment::firstOrFail()->status);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch('/admin/payments/'.Payment::firstOrFail()->id, ['status' => 'refunded'])->assertStatus(422);
    }

    public function test_paypal_does_not_accept_a_capture_with_the_wrong_amount(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $capture = $this->capture($appointment);
        $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] = '1.00';

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'ORDER-123',
                'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123']],
            ], 201),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123/capture' => Http::response($capture, 201),
        ]);

        $this->post('/manage-booking/'.$appointment->manage_token.'/paypal')->assertRedirect();
        $this->get('/manage-booking/'.$appointment->manage_token.'/paypal/return?token=ORDER-123')->assertRedirect();
        $this->assertSame('pending', $appointment->fresh()->payment_status);
        $this->assertSame('pending', Payment::firstOrFail()->status);
    }

    public function test_admin_can_reconcile_a_completed_paypal_order_after_an_interrupted_return(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $payment = Payment::firstOrFail();
        $payment->update(['method' => 'paypal', 'provider' => 'paypal', 'reference' => 'ORDER-123', 'status' => 'pending']);
        $appointment->update(['payment_status' => 'pending']);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123' => Http::response($this->capture($appointment), 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/payments/'.$payment->id.'/verify-paypal')
            ->assertRedirect();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $appointment->fresh()->payment_status);
    }

    public function test_admin_can_switch_an_uncaptured_paypal_order_back_to_cash(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $payment = Payment::firstOrFail();
        $payment->update(['method' => 'paypal', 'provider' => 'paypal', 'reference' => 'ORDER-123', 'status' => 'pending']);
        $appointment->update(['payment_status' => 'pending']);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123' => Http::response([
                'id' => 'ORDER-123',
                'status' => 'PAYER_ACTION_REQUIRED',
                'purchase_units' => [['payments' => ['captures' => []]]],
            ], 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/payments/'.$payment->id.'/switch-to-cash')
            ->assertRedirect();

        $this->assertSame('cash', $payment->fresh()->method);
        $this->assertNull($payment->fresh()->provider);
        $this->assertSame('unpaid', $appointment->fresh()->payment_status);
    }

    public function test_admin_cannot_switch_a_captured_paypal_order_to_cash(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $payment = Payment::firstOrFail();
        $payment->update(['method' => 'paypal', 'provider' => 'paypal', 'reference' => 'ORDER-123', 'status' => 'pending']);
        $appointment->update(['payment_status' => 'pending']);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123' => Http::response($this->capture($appointment), 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/payments/'.$payment->id.'/switch-to-cash')
            ->assertSessionHasErrors('paypal');

        $this->assertSame('paypal', $payment->fresh()->provider);
        $this->assertSame('pending', $appointment->fresh()->payment_status);
    }

    public function test_configured_email_and_sms_send_booking_confirmation(): void
    {
        $this->seed();
        config()->set('integrations.resend.api_key', 'resend-test');
        config()->set('integrations.resend.from', 'bookings@example.test');
        config()->set('integrations.twilio.account_sid', 'AC'.str_repeat('1', 32));
        config()->set('integrations.twilio.auth_token', 'twilio-test');
        config()->set('integrations.twilio.from', '+15551234567');
        Http::fake([
            'api.resend.com/emails' => Http::response(['id' => 'email-1'], 200),
            'api.twilio.com/*' => Http::response(['sid' => 'SM'.str_repeat('1', 32)], 201),
        ]);

        $this->post('/book', $this->bookingData())->assertRedirect();
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.resend.com/emails')
            && in_array('mika@example.test', $request['to'], true));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/Messages.json')
            && $request['To'] === '+639171234567');
    }

    public function test_admin_can_check_an_address_without_saving_location_coordinates(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        config()->set('integrations.google_maps.api_key', 'maps-test');
        Http::fake(['maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'formatted_address' => 'Pala-o, Iligan City, Philippines',
                'geometry' => ['location' => ['lat' => 8.228, 'lng' => 124.245]],
            ]],
        ], 200)]);

        $this->post('/admin/appointments/'.$appointment->id.'/lookup-address')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/appointments/'.$appointment->id.'/lookup-address')
            ->assertSessionHas('location_result.address', 'Pala-o, Iligan City, Philippines');
        $this->assertNull($appointment->fresh()->latitude);
        $this->assertNull($appointment->fresh()->longitude);
        Http::assertSentCount(1);
    }

    public function test_admin_forecast_uses_openweather_response(): void
    {
        config()->set('integrations.openweather.api_key', 'weather-test');
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
            'list' => [[
                'dt' => now()->startOfDay()->addHours(13)->timestamp,
                'main' => ['temp' => 29.4],
                'pop' => 0.3,
                'weather' => [['description' => 'light rain']],
            ]],
            ], 200),
            'air-quality-api.open-meteo.com/*' => Http::response(['current' => ['us_aqi' => 42, 'pm2_5' => 8.2, 'time' => now()->toIso8601String()]], 200),
            'date.nager.at/*' => Http::response([['date' => now()->addDays(2)->format('Y-m-d'), 'localName' => 'Test Holiday']], 200),
            'api.sunrise-sunset.org/*' => Http::response(['sunrise' => '2026-10-28T05:30:00+08:00', 'sunset' => '2026-10-28T17:30:00+08:00'], 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/integrations')->assertOk()
            ->assertSee('OpenWeather')
            ->assertSee('API Integrations')
            ->assertSee('30% chance of rain')
            ->assertSee('AQI 42')
            ->assertSee('Test Holiday')
            ->assertSee('Sunset 5:30 PM');
        Http::assertSentCount(4);
    }

    private function enablePaypal(): void
    {
        config()->set('integrations.paypal.enabled', true);
        config()->set('integrations.paypal.mode', 'sandbox');
        config()->set('integrations.paypal.client_id', 'client-test');
        config()->set('integrations.paypal.client_secret', 'secret-test');
    }

    private function capture(Appointment $appointment): array
    {
        return [
            'id' => 'ORDER-123',
            'status' => 'COMPLETED',
            'purchase_units' => [[
                'custom_id' => $appointment->reference,
                'payments' => ['captures' => [[
                    'status' => 'COMPLETED',
                    'amount' => ['currency_code' => 'PHP', 'value' => '950.00'],
                ]]],
            ]],
        ];
    }

    private function bookingData(): array
    {
        return [
            'aircon_unit_type_id' => AirconUnitType::where('slug', 'split-type')->firstOrFail()->id,
            'quantity' => 1,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '09:00',
            'first_name' => 'Mika', 'last_name' => 'Dela Cruz',
            'email' => 'mika@example.test', 'phone' => '09171234567',
            'address_line' => '12 Sampaguita Street', 'barangay' => 'Pala-o',
            'city' => 'Iligan City', 'landmark' => 'Near the park',
            'payment_method' => 'cash', 'terms' => '1',
        ];
    }
}
