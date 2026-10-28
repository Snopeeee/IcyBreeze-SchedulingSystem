<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\FaqEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FinalProjectReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_book_and_see_only_their_own_account_booking(): void
    {
        $this->seed();
        $this->post('/account/register', [
            'name' => 'Mika Dela Cruz', 'email' => 'mika@example.test', 'phone' => '09171234567',
            'password' => 'long-password-123', 'password_confirmation' => 'long-password-123',
        ])->assertRedirect('/account');
        $this->assertAuthenticated();
        $this->assertSame('customer', auth()->user()->role);

        $this->post('/book', [
            'aircon_unit_type_id' => \App\Models\AirconUnitType::where('slug', 'split-type')->firstOrFail()->id,
            'quantity' => 1, 'appointment_date' => now()->addDays(3)->format('Y-m-d'), 'appointment_time' => '09:00',
            'first_name' => 'Mika', 'last_name' => 'Dela Cruz', 'email' => 'mika@example.test',
            'phone' => '09171234567', 'address_line' => '12 Sampaguita Street', 'barangay' => 'Pala-o',
            'city' => 'Iligan City', 'landmark' => 'Near the park', 'payment_method' => 'cash', 'terms' => '1',
        ])->assertRedirect();

        $appointment = Appointment::firstOrFail();
        $this->assertSame(auth()->id(), $appointment->user_id);
        $this->get('/account')->assertOk()->assertSee($appointment->reference);

        $anotherCustomer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($anotherCustomer)->get('/account')->assertOk()->assertDontSee($appointment->reference);
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_can_create_read_update_and_delete_faq_without_publicly_showing_hidden_content(): void
    {
        $this->seed();
        $this->get('/admin/faqs')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/faqs', ['question' => 'New question?', 'answer' => 'New answer.', 'sort_order' => 10, 'is_published' => '1'])->assertRedirect();
        $faq = FaqEntry::where('question', 'New question?')->firstOrFail();
        $this->get('/admin/faqs')->assertOk()->assertSee('New question?');
        $this->get('/faq')->assertOk()->assertSee('New answer.');
        $this->put('/admin/faqs/'.$faq->id, ['question' => 'Updated question?', 'answer' => 'Updated answer.', 'sort_order' => 2])->assertRedirect();
        $this->get('/faq')->assertOk()->assertDontSee('Updated answer.');
        $this->delete('/admin/faqs/'.$faq->id)->assertRedirect();
        $this->assertDatabaseMissing('faq_entries', ['id' => $faq->id]);
    }

    public function test_service_area_lookup_sends_only_public_city_name(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([['lat' => '8.228', 'lon' => '124.245']], 200)]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/integrations/verify-service-area')->assertSessionHas('service_area_result.name', 'Iligan City, Philippines');
        Http::assertSent(fn ($request) => str_contains(urldecode($request->url()), 'Iligan') && str_contains($request->url(), 'countrycodes=ph') && ! str_contains($request->url(), 'Sampaguita'));
        Http::assertSentCount(1);
    }
}
