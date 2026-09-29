<?php

namespace Tests\Feature;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
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
            ->assertSee('Professional aircon')
            ->assertSee($service->name)
            ->assertSee('Window Type Inverter')
            ->assertSee('₱1,000')
            ->assertDontSee('Deep Clean')
            ->assertDontSee('Cassette Type')
            ->assertSee('Maintenance plans')
            ->assertSee('Coming soon')
            ->assertSee('Customer recommendations')
            ->assertSee('images/icybreeze-logo-compact.png')
            ->assertSee('images/icybreeze-logo-brand-inverse.png')
            ->assertSee('images/icybreeze-favicon.png')
            ->assertDontSee('Service price preview');
        $this->get('/book')->assertOk()->assertSee('Schedule an aircon cleaning');
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

    public function test_simulated_online_payment_is_not_accepted(): void
    {
        $service = $this->service();
        $data = $this->bookingData($service);
        $data['payment_method'] = 'online';

        $this->post('/book', $data)->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('appointments', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_production_seeder_creates_catalog_without_sample_records(): void
    {
        $this->seed();

        $this->assertDatabaseHas('services', ['slug' => 'standard-clean', 'is_active' => true]);
        $this->assertDatabaseHas('aircon_unit_types', ['slug' => 'split-type-inverter', 'price_centavos' => 100000]);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('appointments', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('subscriptions', 0);
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

    public function test_maintenance_plan_requests_are_coming_soon_and_not_accepted(): void
    {
        $this->get('/subscriptions')
            ->assertOk()
            ->assertSee('Maintenance Plans Are Coming Soon')
            ->assertSee('Schedule a One-Time Cleaning')
            ->assertSee('Not yet available')
            ->assertDontSee('<form', false);

        $this->post('/subscriptions')->assertStatus(405);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_admin_dashboard_requires_login_and_allows_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Operations Dashboard')
            ->assertSee('Revenue and net trend')
            ->assertSee('Appointment volume')
            ->assertSee('Booked unit mix')
            ->assertSee('images/icybreeze-logo-compact-inverse.png')
            ->assertSee('images/icybreeze-favicon.png');
    }

    public function test_live_availability_exposes_available_reserved_and_in_progress_states(): void
    {
        $service = $this->service();
        $data = $this->bookingData($service);
        $data['appointment_time'] = '13:00';
        $this->post('/book', $data)->assertRedirect();

        $date = $data['appointment_date'];
        $available = $this->getJson('/book/availability?date='.$date.'&quantity=1')->assertOk()->json('slots');
        $this->assertSame(['09:00', '13:00', '16:00'], array_column($available, 'time'));
        $this->assertSame('reserved', collect($available)->firstWhere('time', '13:00')['status']);
        $this->assertSame('available', collect($available)->firstWhere('time', '09:00')['status']);

        Appointment::firstOrFail()->update(['status' => 'in_progress']);
        $inProgress = $this->getJson('/book/availability?date='.$date.'&quantity=1')->assertOk()->json('slots');
        $this->assertSame('in_progress', collect($inProgress)->firstWhere('time', '13:00')['status']);
    }

    public function test_booking_requires_a_landmark_and_rejects_retired_arrival_times(): void
    {
        $service = $this->service();
        $missingLandmark = $this->bookingData($service);
        unset($missingLandmark['landmark']);
        $this->post('/book', $missingLandmark)->assertSessionHasErrors('landmark');

        $retiredTime = $this->bookingData($service);
        $retiredTime['appointment_time'] = '10:00';
        $this->post('/book', $retiredTime)->assertSessionHasErrors('appointment_time');
        $this->assertDatabaseCount('appointments', 0);
    }

    private function service(): Service
    {
        return Service::create([
            'name' => 'Aircon Cleaning',
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
            'appointment_time' => '09:00',
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
