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

        $this->get('/manage-booking/'.$appointment->manage_token)->assertOk()->assertSee('PayPal Sandbox checkout');
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

    public function test_paypal_pending_review_keeps_the_booking_unpaid(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $capture = $this->capture($appointment);
        $capture['purchase_units'][0]['payments']['captures'][0]['status'] = 'PENDING';
        $capture['purchase_units'][0]['payments']['captures'][0]['status_details'] = ['reason' => 'PENDING_REVIEW'];

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
        $this->assertTrue(Payment::firstOrFail()->isSandboxReviewPending());
        $this->get('/manage-booking/'.$appointment->manage_token)
            ->assertOk()
            ->assertSee('PayPal Sandbox review pending')
            ->assertDontSee('Try Sandbox payment');
        $this->post('/manage-booking/'.$appointment->manage_token.'/paypal')
            ->assertRedirect()
            ->assertSessionHas('error', 'PayPal is still reviewing this Sandbox payment. Please do not try to pay again.');
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

    public function test_admin_verification_keeps_a_paypal_review_pending_and_blocks_customer_retry(): void
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertRedirect();
        $appointment = Appointment::firstOrFail();
        $this->enablePaypal();
        $payment = Payment::firstOrFail();
        $payment->update(['method' => 'paypal', 'provider' => 'paypal', 'reference' => 'ORDER-123', 'status' => 'pending']);
        $appointment->update(['payment_status' => 'pending']);
        $capture = $this->capture($appointment);
        $capture['purchase_units'][0]['payments']['captures'][0]['status'] = 'PENDING';
        $capture['purchase_units'][0]['payments']['captures'][0]['status_details'] = ['reason' => 'PENDING_REVIEW'];

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123' => Http::response($capture, 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/payments/'.$payment->id.'/verify-paypal')
            ->assertRedirect()
            ->assertSessionHasErrors('paypal');

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $appointment->fresh()->payment_status);
        $this->assertTrue($payment->fresh()->isSandboxReviewPending());
        $this->get('/manage-booking/'.$appointment->manage_token)
            ->assertOk()
            ->assertSee('PayPal Sandbox review pending')
            ->assertDontSee('Try Sandbox payment');
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

    public function test_admin_can_verify_paypal_sandbox_without_creating_an_order(): void
    {
        $this->enablePaypal();
        Http::fake(['api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'test-access'], 200)]);

        $this->post('/admin/integrations/verify-paypal')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/integrations/verify-paypal')
            ->assertRedirect()
            ->assertSessionHas('success', 'PayPal Sandbox credentials verified. No payment or test order was created.');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v1/oauth2/token'));
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_openstreetmap_checks_only_the_public_city(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '8.2280', 'lon' => '124.2452', 'display_name' => 'Iligan City, Philippines'],
        ], 200)]);

        $this->post('/admin/integrations/verify-service-area')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/integrations/verify-service-area')
            ->assertRedirect()
            ->assertSessionHas('service_area_result.name', 'Iligan City, Philippines');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'nominatim.openstreetmap.org/search')
            && $request['q'] === 'Iligan'
            && ! str_contains($request->url(), 'Pala-o'));
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
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/integrations')->assertOk()
            ->assertViewHas('integrations', fn ($items) => array_column($items, 'name') === [
                'PayPal Sandbox', 'OpenWeather', 'Google Calendar', 'Open-Meteo Air Quality', 'OpenStreetMap Nominatim',
            ])
            ->assertSee('API Integrations')
            ->assertSee('30% chance of rain')
            ->assertSee('AQI 42')
            ->assertDontSee('Nager.Date')
            ->assertDontSee('Sunrise-Sunset.org');
        Http::assertSentCount(2);
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
