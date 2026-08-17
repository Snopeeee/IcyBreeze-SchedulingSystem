<?php

namespace Database\Seeders;

use App\Models\AirconUnitType;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\TechnicianLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@icybreeze.test'], [
            'name' => 'IcyBreeze Admin', 'phone' => '0917 123 2723', 'password' => Hash::make('password'),
            'role' => 'admin', 'is_active' => true,
        ]);

        $technicians = collect([
            ['name' => 'Rene Dela Cruz', 'email' => 'rene@icybreeze.test', 'phone' => '0917 800 1001'],
            ['name' => 'Carlo Mindalano', 'email' => 'carlo@icybreeze.test', 'phone' => '0917 800 1002'],
        ])->map(fn ($technician) => User::updateOrCreate(['email' => $technician['email']], $technician + [
            'password' => Hash::make('password'), 'role' => 'technician', 'is_active' => true,
        ]));

        TechnicianLocation::updateOrCreate(['user_id' => $technicians[0]->id], [
            'latitude' => 8.2280000, 'longitude' => 124.2452000, 'accuracy_meters' => 18, 'recorded_at' => now()->subMinutes(4),
        ]);
        TechnicianLocation::updateOrCreate(['user_id' => $technicians[1]->id], [
            'latitude' => 8.2390000, 'longitude' => 124.2490000, 'accuracy_meters' => 24, 'recorded_at' => now()->subMinutes(11),
        ]);

        Service::where('slug', '!=', Service::STANDARD_SLUG)->update(['is_active' => false]);
        $service = Service::updateOrCreate(['slug' => Service::STANDARD_SLUG], [
            'name' => 'Standard Cleaning',
            'short_description' => 'Professional standard cleaning priced by aircon unit type.',
            'description' => 'One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.',
            'price_centavos' => 70000,
            'duration_minutes' => 75,
            'buffer_minutes' => 30,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $unitTypes = collect([
            ['name' => 'Window Type', 'slug' => 'window-type', 'price_centavos' => 70000, 'technician_share_centavos' => 40000, 'sort_order' => 1],
            ['name' => 'Window Type Inverter', 'slug' => 'window-type-inverter', 'price_centavos' => 75000, 'technician_share_centavos' => 45000, 'sort_order' => 2],
            ['name' => 'Split Type', 'slug' => 'split-type', 'price_centavos' => 95000, 'technician_share_centavos' => 55000, 'sort_order' => 3],
            ['name' => 'Split Type Inverter', 'slug' => 'split-type-inverter', 'price_centavos' => 100000, 'technician_share_centavos' => 60000, 'sort_order' => 4],
        ])->map(fn ($unitType) => AirconUnitType::updateOrCreate(['slug' => $unitType['slug']], $unitType + ['is_active' => true]));

        if (Appointment::count() === 0) {
            $samples = [
                ['Maria', 'Santos', 'maria@example.test', '0917 321 4578', 8, 'assigned', 'paid', 'Pala-o', 8.2286000, 124.2449000, 0],
                ['Paolo', 'Reyes', 'paolo@example.test', '0998 420 1188', 10, 'assigned', 'unpaid', 'Tibanga', 8.2446000, 124.2595000, 0],
                ['Ana', 'Cruz', 'ana@example.test', '0922 885 9012', 15, 'assigned', 'unpaid', 'Tubod', 8.2149000, 124.2358000, 0],
                ['Luis', 'Garcia', 'luis@example.test', '0916 204 7765', 10, 'completed', 'paid', 'Mahayahay', 8.2256000, 124.2496000, 1],
            ];

            foreach ($samples as $index => [$first, $last, $email, $phone, $hour, $status, $paymentStatus, $barangay, $latitude, $longitude, $technicianIndex]) {
                $customer = Customer::create(['first_name' => $first, 'last_name' => $last, 'email' => $email, 'phone' => $phone]);
                $starts = now()->startOfDay()->addDays($index === 3 ? -2 : 0)->setHour($hour);
                $quantity = $index === 1 ? 2 : 1;
                $unitType = $unitTypes[$index];
                $subtotal = $unitType->price_centavos * $quantity;
                $technicianShare = $unitType->technician_share_centavos * $quantity;
                $appointment = Appointment::create([
                    'reference' => 'ICY-DEMO-100'.($index + 1), 'manage_token' => Str::random(48), 'customer_id' => $customer->id,
                    'service_id' => $service->id, 'aircon_unit_type_id' => $unitType->id, 'technician_id' => $technicians[$technicianIndex]->id,
                    'unit_type' => $unitType->name, 'quantity' => $quantity, 'unit_price_centavos' => $unitType->price_centavos,
                    'starts_at' => $starts, 'ends_at' => $starts->copy()->addMinutes($service->duration_minutes + $service->buffer_minutes),
                    'status' => $status, 'payment_status' => $paymentStatus, 'source' => 'web', 'subtotal_centavos' => $subtotal,
                    'travel_fee_centavos' => 0, 'total_centavos' => $subtotal, 'technician_share_centavos' => $technicianShare,
                    'gross_centavos' => $subtotal - $technicianShare, 'address_line' => (20 + $index).' Iligan Service Road',
                    'barangay' => $barangay, 'city' => 'Iligan City', 'province' => 'Lanao del Norte', 'postal_code' => '9200',
                    'latitude' => $latitude, 'longitude' => $longitude, 'location_accuracy_meters' => 20, 'location_consent_at' => now(),
                    'customer_notes' => $index === 1 ? 'Please call before arrival.' : null,
                    'confirmed_at' => now(), 'completed_at' => $status === 'completed' ? $starts->copy()->addHours(2) : null,
                ]);
                Payment::create([
                    'appointment_id' => $appointment->id, 'method' => $paymentStatus === 'paid' ? 'online' : 'cash',
                    'provider' => $paymentStatus === 'paid' ? 'paymongo' : null, 'reference' => $paymentStatus === 'paid' ? 'PM-DEMO-100'.($index + 1) : null,
                    'status' => $paymentStatus, 'amount_centavos' => $appointment->total_centavos, 'currency' => 'PHP', 'paid_at' => $paymentStatus === 'paid' ? now() : null,
                ]);
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id, 'from_status' => null, 'to_status' => $status,
                    'actor_type' => 'system', 'actor_name' => 'Demo Seeder', 'reason' => 'Iligan sample appointment', 'created_at' => now(),
                ]);
            }

            Subscription::create([
                'reference' => 'SUB-DEMO-1001', 'manage_token' => Str::random(48), 'customer_id' => Customer::first()->id,
                'service_id' => $service->id, 'aircon_unit_type_id' => $unitTypes[2]->id, 'technician_id' => $technicians[0]->id,
                'plan' => 'quarterly_care', 'unit_type' => $unitTypes[2]->name,
                'interval_months' => 3, 'quantity' => 1, 'unit_price_centavos' => $unitTypes[2]->price_centavos,
                'price_per_visit_centavos' => 85500, 'technician_share_per_visit_centavos' => 55000,
                'gross_per_visit_centavos' => 30500, 'status' => 'active',
                'next_service_date' => today()->addMonths(3), 'preferred_day' => 'Saturday', 'preferred_time' => '10:00',
                'address_line' => '20 Iligan Service Road', 'barangay' => 'Pala-o', 'city' => 'Iligan City',
                'province' => 'Lanao del Norte', 'postal_code' => '9200', 'latitude' => 8.2286000, 'longitude' => 124.2449000,
                'location_consent_at' => now(), 'notes' => 'Send a reminder one week before the visit.',
            ]);
        }
    }
}
