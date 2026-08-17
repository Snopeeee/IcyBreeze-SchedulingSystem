<?php

namespace Tests\Feature;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\TechnicianLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IcyBreezeSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_and_booking_page_render(): void
    {
        $service = $this->service();

        $this->get('/')
            ->assertOk()
            ->assertSee('Breathe cleaner.')
            ->assertSee($service->name)
            ->assertSee('Window Type Inverter')
            ->assertSee('₱1,000')
            ->assertDontSee('Deep Clean')
            ->assertDontSee('Cassette Type')
            ->assertSee('IcyBreeze Care Plans')
            ->assertSee('Cleaner air, cooler life.')
            ->assertSee('images/icybreeze-logo-compact.png')
            ->assertSee('images/icybreeze-logo-brand-inverse.png')
            ->assertSee('images/icybreeze-favicon.png')
            ->assertDontSee('Service price preview');
        $this->get('/book')->assertOk()->assertSee('Schedule your cleaning');
    }

    public function test_customer_can_create_a_cash_appointment(): void
    {
        $service = $this->service();
        $response = $this->post('/book', $this->bookingData($service));

        $appointment = Appointment::firstOrFail();
        $response->assertRedirect(route('booking.success', ['reference' => $appointment->reference, 'token' => $appointment->manage_token]));
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('unpaid', $appointment->payment_status);
        $this->assertSame('Split Type', $appointment->unit_type);
        $this->assertSame(95000, $appointment->total_centavos);
        $this->assertSame(55000, $appointment->technician_share_centavos);
        $this->assertSame(40000, $appointment->gross_centavos);
        $this->assertDatabaseHas('payments', ['appointment_id' => $appointment->id, 'method' => 'cash', 'status' => 'unpaid']);
        $this->assertDatabaseHas('customers', ['email' => 'mika@example.test']);
    }

    public function test_every_pdf_unit_type_uses_its_exact_price_split(): void
    {
        $service = $this->service();
        $rates = [
            'window-type' => [70000, 40000, 30000],
            'window-type-inverter' => [75000, 45000, 30000],
            'split-type' => [95000, 55000, 40000],
            'split-type-inverter' => [100000, 60000, 40000],
        ];

        foreach ($rates as $slug => [$customerPrice, $technicianShare, $gross]) {
            $unitType = $this->unitType($slug);
            $data = $this->bookingData($service);
            $data['aircon_unit_type_id'] = $unitType->id;
            $data['appointment_date'] = now()->addDays($unitType->sort_order + 2)->format('Y-m-d');
            $data['email'] = $slug.'@example.test';

            $this->post('/book', $data)->assertRedirect();

            $appointment = Appointment::whereHas('customer', fn ($query) => $query->where('email', $data['email']))->firstOrFail();
            $this->assertSame($unitType->name, $appointment->unit_type);
            $this->assertSame($customerPrice, $appointment->total_centavos);
            $this->assertSame($technicianShare, $appointment->technician_share_centavos);
            $this->assertSame($gross, $appointment->gross_centavos);
        }
    }

    public function test_overlapping_active_appointment_is_rejected(): void
    {
        $service = $this->service();
        $data = $this->bookingData($service);
        $this->post('/book', $data)->assertRedirect();

        $this->post('/book', $data)->assertStatus(422);
        $this->assertSame(1, Appointment::count());
    }

    public function test_booking_is_limited_to_iligan_city(): void
    {
        $service = $this->service();
        $data = $this->bookingData($service);
        $data['city'] = 'Cagayan de Oro City';

        $this->post('/book', $data)->assertSessionHasErrors('city');
        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_customer_can_request_a_quarterly_subscription(): void
    {
        $service = $this->service();
        $response = $this->post('/subscriptions', [
            'plan' => 'quarterly_care',
            'service_id' => $service->id,
            'aircon_unit_type_id' => $this->unitType('split-type')->id,
            'quantity' => 2,
            'next_service_date' => now()->addDays(7)->format('Y-m-d'),
            'preferred_day' => 'Saturday',
            'preferred_time' => '10:00',
            'first_name' => 'Mika',
            'last_name' => 'Dela Cruz',
            'email' => 'mika@example.test',
            'phone' => '09171234567',
            'address_line' => '12 Sampaguita Street',
            'barangay' => 'Pala-o',
            'city' => 'Iligan City',
            'postal_code' => '9200',
            'latitude' => 8.2286,
            'longitude' => 124.2449,
            'location_consent' => '1',
            'terms' => '1',
        ]);

        $subscription = Subscription::firstOrFail();
        $response->assertRedirect(route('subscriptions.success', [
            'reference' => $subscription->reference,
            'token' => $subscription->manage_token,
        ]));
        $this->assertSame('Split Type', $subscription->unit_type);
        $this->assertSame(171000, $subscription->price_per_visit_centavos);
        $this->assertSame(110000, $subscription->technician_share_per_visit_centavos);
        $this->assertSame(61000, $subscription->gross_per_visit_centavos);
        $this->assertSame('Iligan City', $subscription->city);
        $this->assertNotNull($subscription->location_consent_at);
    }

    public function test_technician_can_open_mobile_jobs_and_share_location(): void
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $this->actingAs($technician, 'technician')
            ->get('/technician')
            ->assertOk()
            ->assertSee('Location and route tools');

        $this->actingAs($technician, 'technician')
            ->postJson('/technician/location', [
                'latitude' => 8.2286,
                'longitude' => 124.2449,
                'accuracy_meters' => 18,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Location shared securely.');

        $location = TechnicianLocation::firstOrFail();
        $this->assertSame($technician->id, $location->user_id);
        $this->assertSame('8.2286000', $location->latitude);
    }

    public function test_admin_dashboard_requires_login_and_allows_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Good morning')
            ->assertSee('Revenue and gross trend')
            ->assertSee('Appointment volume')
            ->assertSee('Booked unit mix')
            ->assertSee('images/icybreeze-logo-compact-inverse.png')
            ->assertSee('images/icybreeze-favicon.png');
    }

    private function service(): Service
    {
        return Service::create([
            'name' => 'Standard Clean',
            'slug' => 'standard-clean',
            'short_description' => 'Routine professional aircon cleaning.',
            'description' => 'Filter, cover, coil, drain, and cooling check.',
            'price_centavos' => 70000,
            'duration_minutes' => 75,
            'buffer_minutes' => 30,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function bookingData(Service $service): array
    {
        return [
            'service_id' => $service->id,
            'aircon_unit_type_id' => $this->unitType('split-type')->id,
            'quantity' => 1,
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'first_name' => 'Mika',
            'last_name' => 'Dela Cruz',
            'email' => 'mika@example.test',
            'phone' => '09171234567',
            'address_line' => '12 Sampaguita Street',
            'barangay' => 'Pala-o',
            'city' => 'Iligan City',
            'postal_code' => '9200',
            'landmark' => 'Near the park',
            'customer_notes' => 'Please call before arrival.',
            'payment_method' => 'cash',
            'terms' => '1',
        ];
    }

    private function unitType(string $slug): AirconUnitType
    {
        return AirconUnitType::where('slug', $slug)->firstOrFail();
    }
}
