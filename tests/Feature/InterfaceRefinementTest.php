<?php

namespace Tests\Feature;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class InterfaceRefinementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleaning_name_is_consistent_across_public_and_admin_pages(): void
    {
        $this->seed();
        foreach (['/', '/services', '/book', '/subscriptions', '/faq'] as $url) {
            $response = $this->get($url)->assertOk();
            $this->assertStringNotContainsString('standard', strtolower(strip_tags($response->getContent())));
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/services')->assertOk()->assertSee('Aircon Cleaning')->assertDontSee('Standard Cleaning');
    }

    public function test_official_business_contact_details_are_consistent_on_public_pages(): void
    {
        $this->seed();
        $contact = $this->get('/contact')->assertOk();
        $contact->assertSee('0967 873 0654')
            ->assertSee('icybreezeaccleaning@gmail.com')
            ->assertSee('Tambacan, Iligan City, Philippines')
            ->assertSee('https://www.facebook.com/icybreezeac/', false)
            ->assertSee('https://www.instagram.com/icybreezeph/', false)
            ->assertDontSee('0917 123 2723')
            ->assertDontSee('hello@icybreeze.ph');

        $this->get('/')->assertOk()
            ->assertSee('0967 873 0654')
            ->assertSee('icybreezeaccleaning@gmail.com')
            ->assertSee('application/ld+json', false)
            ->assertDontSee('hello@icybreeze.ph');

        $this->get('/coverage')->assertOk()->assertSee('Based in Tambacan');
    }

    public function test_homepage_presents_public_facebook_recommendations_without_an_invented_rating(): void
    {
        $this->seed();

        $this->get('/')->assertOk()
            ->assertSee('Customer recommendations')
            ->assertSee('All four public recommendations')
            ->assertSee('Bing Cabanes Nahcram')
            ->assertSee('Mari Car')
            ->assertSee('Iris Lorraine Millan-Salvani')
            ->assertSee('Dianne Quilo - Dosdos')
            ->assertSee('data-review-carousel', false)
            ->assertSee('data-review-autoplay', false)
            ->assertSee('Recommends IcyBreeze Aircon Cleaning')
            ->assertSee('https://www.facebook.com/icybreezeac/reviews', false)
            ->assertDontSee('5-star')
            ->assertDontSee('★★★★★');
    }

    public function test_homepage_presents_verified_facebook_updates_with_original_links(): void
    {
        $this->seed();

        $this->get('/')->assertOk()
            ->assertSee('Latest updates')
            ->assertSee('Five signs your aircon may need cleaning')
            ->assertSee('Three aircon units deep cleaned in Luinab')
            ->assertSee('Inside a Samsung split-type unit in Luinab')
            ->assertSee('September 25, 2026')
            ->assertSee('https://www.facebook.com/reel/1073668675638158/', false)
            ->assertSee('https://www.facebook.com/photo/?fbid=122117341719290339&amp;set=pcb.122117343165290339', false)
            ->assertSee('https://www.facebook.com/reel/1597656315392856/', false);
    }

    public function test_catalog_rename_preserves_ids_prices_and_custom_copy(): void
    {
        $this->seed();
        $service = Service::bookable()->firstOrFail();
        $service->update(['name' => 'Standard Cleaning', 'short_description' => 'Our standard cleaning for your space.', 'description' => 'Custom instructions are preserved.', 'price_centavos' => 123400, 'duration_minutes' => 90]);
        $before = $service->fresh()->getAttributes();
        $rates = DB::table('aircon_unit_types')->get()->toJson();
        $migration = require database_path('migrations/2026_09_04_000001_refine_cleaning_service_name.php');
        $migration->up();
        $service->refresh();
        $this->assertSame('Aircon Cleaning', $service->name);
        $this->assertSame('Our Aircon Cleaning for your space.', $service->short_description);
        foreach (array_diff(array_keys($before), ['name', 'short_description']) as $field) {
            $this->assertSame($before[$field], $service->getAttributes()[$field]);
        }
        $this->assertSame($rates, DB::table('aircon_unit_types')->get()->toJson());
        $migration->up();
        $this->assertSame('Aircon Cleaning', $service->fresh()->name);
    }

    public function test_pricing_links_preselect_only_an_active_unit_type(): void
    {
        $this->seed();
        $unit = AirconUnitType::where('slug', 'split-type-inverter')->firstOrFail();
        $this->get('/services')->assertSee(route('booking.create', ['unit' => $unit->id]), false);
        $this->get('/book?unit='.$unit->id)->assertOk()->assertViewHas('selectedUnitTypeId', $unit->id);
        $unit->update(['is_active' => false]);
        $first = AirconUnitType::bookable()->firstOrFail()->id;
        $this->get('/book?unit='.$unit->id)->assertOk()->assertViewHas('selectedUnitTypeId', $first);
        $this->get('/book?unit=999999')->assertOk()->assertViewHas('selectedUnitTypeId', $first);
    }

    public function test_booking_validation_returns_to_the_relevant_step(): void
    {
        $this->seed();
        foreach (['quantity' => 1, 'appointment_time' => 2, 'email' => 3, 'terms' => 4] as $field => $step) {
            $this->withSession(['errors' => (new ViewErrorBag)->put('default', new MessageBag([$field => 'Please check this field.']))])->get('/book')
                ->assertOk()->assertSee('data-initial-step="'.$step.'"', false);
        }
    }

    public function test_dashboard_counts_attention_once_and_limits_monthly_revenue_to_this_year(): void
    {
        $this->travelTo(now()->setDate(2026, 3, 31)->setTime(10, 0));
        $this->seed();
        $this->post('/book', [
            'aircon_unit_type_id' => AirconUnitType::bookable()->firstOrFail()->id,
            'quantity' => 1, 'appointment_date' => now()->addDays(3)->toDateString(), 'appointment_time' => '09:00',
            'first_name' => 'Layout', 'last_name' => 'Test', 'email' => 'layout@example.test', 'phone' => '09171234567',
            'address_line' => 'Test Street', 'barangay' => 'Pala-o', 'city' => 'Iligan City', 'landmark' => 'Near the plaza', 'payment_method' => 'cash', 'terms' => '1',
        ])->assertSessionHasNoErrors();
        $appointment = Appointment::firstOrFail();
        $appointment->update(['status' => 'pending_confirmation', 'payment_status' => 'unpaid']);
        $appointment->payments()->first()->update(['status' => 'paid', 'paid_at' => '2025-03-15', 'amount_centavos' => 10000]);
        Payment::create(['appointment_id' => $appointment->id, 'method' => 'cash', 'status' => 'paid', 'paid_at' => '2026-03-15', 'amount_centavos' => 20000]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin')->assertOk()->assertViewHas('attentionCount', 1)->assertViewHas('revenue', 20000)
            ->assertViewHas('financialTrend', fn ($trend) => $trend->pluck('label')->all() === ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar']);
        $appointment->update(['status' => 'cancelled']);
        $this->get('/admin')->assertViewHas('attentionCount', 0);
    }
}
