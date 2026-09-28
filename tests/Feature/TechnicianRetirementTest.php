<?php

namespace Tests\Feature;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TechnicianRetirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_routes_are_unavailable_even_to_an_admin(): void
    {
        foreach (['/technician', '/technician/login', '/admin/technicians'] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['/technician', '/technician/login', '/admin/technicians'] as $url) {
            $this->get($url)->assertNotFound();
        }
        foreach (['/technician/login', '/technician/logout', '/technician/location', '/admin/technicians'] as $url) {
            $this->post($url, [])->assertNotFound();
        }
        foreach (['/technician/appointments/1/status', '/admin/technicians/1', '/admin/appointments/1/assign'] as $url) {
            $this->patch($url, [])->assertNotFound();
        }

        $this->assertArrayNotHasKey('technician', config('auth.guards'));
    }

    public function test_only_administrators_can_sign_in_to_the_admin_area(): void
    {
        foreach (['customer', 'technician'] as $role) {
            $account = User::factory()->create([
                'role' => $role,
                'is_active' => true,
                'password' => 'Local-test-password!42',
            ]);

            $this->from('/admin/login')->post('/admin/login', [
                'email' => $account->email,
                'password' => 'Local-test-password!42',
            ])->assertSessionHasErrors('email');

            $this->assertGuest();
        }

        $this->actingAs($account)->get('/admin')->assertForbidden();
    }

    public function test_customer_and_admin_pages_have_no_technician_controls(): void
    {
        $appointment = $this->bookAppointment();

        foreach (['/', '/services', '/how-it-works', '/coverage', '/faq', '/contact', '/book', '/subscriptions', '/admin/login'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('Technician portal')
                ->assertDontSee('data-geolocate', false)
                ->assertDontSee('/technician', false);
        }

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['/admin', '/admin/appointments', '/admin/appointments/'.$appointment->id, '/admin/payments', '/admin/customers', '/admin/services', '/admin/subscriptions'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('name="technician_id"', false)
                ->assertDontSee('/technician', false)
                ->assertDontSee('Technician field status')
                ->assertDontSee('Assign technician');
        }
    }

    public function test_admin_can_manage_a_booking_and_payment_without_assignment(): void
    {
        $appointment = $this->bookAppointment();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patch('/admin/appointments/'.$appointment->id.'/reschedule', [
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '09:00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->patch('/admin/appointments/'.$appointment->id.'/status', ['status' => 'assigned'])
            ->assertSessionHasErrors('status');

        foreach (['in_progress', 'completed'] as $status) {
            $this->patch('/admin/appointments/'.$appointment->id.'/status', ['status' => $status])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($status, $appointment->fresh()->status);
        }

        $this->patch('/admin/payments/'.$appointment->payments->first()->id, ['status' => 'paid'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $appointment->refresh();
        $this->assertSame('paid', $appointment->payment_status);
        $this->assertNull($appointment->technician_id);
        $this->assertNull($appointment->latitude);
        $this->assertNull($appointment->longitude);
        $this->assertNull($appointment->location_consent_at);
        $this->assertNotNull($appointment->completed_at);
        $this->assertSame(95000, $appointment->total_centavos);
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_legacy_assignments_are_preserved_and_prevent_double_booking(): void
    {
        $appointment = $this->bookAppointment();
        $legacyAccount = User::factory()->create(['role' => 'technician']);
        DB::table('appointments')->where('id', $appointment->id)->update([
            'status' => 'assigned', 'technician_id' => $legacyAccount->id,
        ]);

        $this->post('/book', $this->bookingData())->assertStatus(422);
        $this->assertDatabaseCount('appointments', 1);

        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/appointments?status=confirmed')->assertOk()->assertSee($appointment->reference);
        $this->get('/admin/appointments/'.$appointment->id)->assertOk()->assertSee('Confirmed');
        $this->assertSame($legacyAccount->id, $appointment->fresh()->technician_id);
    }

    public function test_care_plan_updates_ignore_retired_assignment_fields(): void
    {
        $this->seed();
        $data = array_merge($this->bookingData(), [
            'plan' => 'quarterly_care',
            'next_service_date' => now()->addDays(7)->toDateString(),
            'preferred_day' => 'Saturday',
            'preferred_time' => '09:00',
        ]);
        $this->post('/subscriptions', $data)->assertSessionHasNoErrors()->assertRedirect();
        $subscription = Subscription::firstOrFail();
        $legacyAccount = User::factory()->create(['role' => 'technician']);
        DB::table('subscriptions')->where('id', $subscription->id)->update(['technician_id' => $legacyAccount->id]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->patch('/admin/subscriptions/'.$subscription->id, [
            'status' => 'active',
            'next_service_date' => now()->addDays(10)->toDateString(),
            'technician_id' => null,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame($legacyAccount->id, $subscription->technician_id);
        $this->assertNull($subscription->latitude);
        $this->assertNull($subscription->longitude);
        $this->get('/admin/subscriptions')->assertOk()->assertSee($subscription->reference)->assertDontSee('name="technician_id"', false);
    }

    private function bookAppointment(): Appointment
    {
        $this->seed();
        $this->post('/book', $this->bookingData())->assertSessionHasNoErrors()->assertRedirect();

        return Appointment::firstOrFail();
    }

    private function bookingData(): array
    {
        return [
            'aircon_unit_type_id' => AirconUnitType::where('slug', 'split-type')->firstOrFail()->id,
            'quantity' => 1,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '09:00',
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'customer@example.test',
            'phone' => '09171234567',
            'address_line' => '12 Test Street',
            'barangay' => 'Pala-o',
            'city' => 'Iligan City',
            'landmark' => 'Near the plaza',
            'payment_method' => 'cash',
            'terms' => '1',
            // Old browser clients must not continue collecting GPS or assigning staff.
            'latitude' => 8.2286,
            'longitude' => 124.2449,
            'location_consent' => '1',
            'technician_id' => 123,
        ];
    }
}
