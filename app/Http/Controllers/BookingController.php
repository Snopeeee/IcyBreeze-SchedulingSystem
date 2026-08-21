<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\AirconUnitType;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const TIMES = ['08:00', '10:00', '13:00', '15:00'];

    public function create(Request $request): View
    {
        return view('booking.create', [
            'service' => Service::bookable()->firstOrFail(),
            'unitTypes' => AirconUnitType::bookable()->get(),
            'times' => self::TIMES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'aircon_unit_type_id' => [
                'required',
                Rule::exists('aircon_unit_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
            'appointment_date' => ['required', 'date', 'after_or_equal:tomorrow'],
            'appointment_time' => ['required', Rule::in(self::TIMES)],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:180'],
            'barangay' => ['required', 'string', 'max:100'],
            'city' => ['required', Rule::in(['Iligan City'])],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'landmark' => ['nullable', 'string', 'max:160'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'location_accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'location_consent' => ['exclude_without:latitude', 'exclude_without:longitude', 'accepted'],
            'payment_method' => ['required', Rule::in(['cash'])],
            'terms' => ['accepted'],
        ]);

        $service = Service::bookable()->firstOrFail();
        $unitType = AirconUnitType::bookable()->findOrFail($data['aircon_unit_type_id']);
        $startsAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $data['appointment_date'].' '.$data['appointment_time'],
            config('app.timezone')
        );
        $duration = $service->duration_minutes + (($data['quantity'] - 1) * 45);
        $endsAt = $startsAt->addMinutes($duration + $service->buffer_minutes);
        $subtotal = $unitType->price_centavos * $data['quantity'];
        $technicianShare = $unitType->technician_share_centavos * $data['quantity'];
        $gross = $subtotal - $technicianShare;

        $appointment = DB::transaction(function () use ($data, $service, $unitType, $startsAt, $endsAt, $subtotal, $technicianShare, $gross) {
            $conflict = Appointment::whereIn('status', Appointment::ACTIVE_STATUSES)
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                abort(422, 'That time has just been booked. Please choose another available schedule.');
            }

            $customer = Customer::updateOrCreate(
                ['email' => Str::lower($data['email'])],
                collect($data)->only(['first_name', 'last_name', 'phone'])->all()
            );

            do {
                $reference = 'ICY-'.$startsAt->format('ymd').'-'.Str::upper(Str::random(4));
            } while (Appointment::where('reference', $reference)->exists());

            $appointment = Appointment::create([
                'reference' => $reference,
                'manage_token' => Str::random(48),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'aircon_unit_type_id' => $unitType->id,
                'unit_type' => $unitType->name,
                'quantity' => $data['quantity'],
                'unit_price_centavos' => $unitType->price_centavos,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'source' => 'web',
                'subtotal_centavos' => $subtotal,
                'travel_fee_centavos' => 0,
                'total_centavos' => $subtotal,
                'technician_share_centavos' => $technicianShare,
                'gross_centavos' => $gross,
                'address_line' => $data['address_line'],
                'barangay' => $data['barangay'],
                'city' => $data['city'],
                'province' => 'Lanao del Norte',
                'postal_code' => $data['postal_code'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_accuracy_meters' => $data['location_accuracy_meters'] ?? null,
                'location_consent_at' => isset($data['latitude'], $data['longitude']) ? now() : null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'confirmed_at' => now(),
            ]);

            Payment::create([
                'appointment_id' => $appointment->id,
                'method' => 'cash',
                'provider' => null,
                'reference' => null,
                'status' => 'unpaid',
                'amount_centavos' => $subtotal,
                'currency' => 'PHP',
                'notes' => null,
            ]);

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => null,
                'to_status' => 'confirmed',
                'actor_type' => 'customer',
                'actor_name' => $customer->full_name,
                'reason' => 'Appointment booked through the website.',
                'created_at' => now(),
            ]);

            return $appointment;
        });

        return redirect()->route('booking.success', [
            'reference' => $appointment->reference,
            'token' => $appointment->manage_token,
        ]);
    }

    public function success(Request $request, string $reference): View
    {
        $appointment = Appointment::with(['customer', 'service', 'airconUnitType', 'payments'])
            ->where('reference', $reference)
            ->where('manage_token', $request->query('token'))
            ->firstOrFail();

        return view('booking.success', compact('appointment'));
    }

    public function manage(string $token): View
    {
        $appointment = Appointment::with(['customer', 'service', 'airconUnitType', 'payments', 'histories'])
            ->where('manage_token', $token)
            ->firstOrFail();

        return view('booking.manage', compact('appointment'));
    }

    public function cancel(Request $request, string $token): RedirectResponse
    {
        $appointment = Appointment::where('manage_token', $token)->firstOrFail();

        abort_if(in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true), 422, 'This appointment can no longer be cancelled.');
        abort_if(now()->addHours(24)->greaterThan($appointment->starts_at), 422, 'Online cancellation closes 24 hours before the appointment. Please call us for help.');

        $oldStatus = $appointment->status;
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->string('reason')->limit(180)->toString() ?: 'Cancelled by customer.',
        ]);

        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => $oldStatus,
            'to_status' => 'cancelled',
            'actor_type' => 'customer',
            'actor_name' => $appointment->customer->full_name,
            'reason' => $appointment->cancellation_reason,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Your appointment has been cancelled.');
    }
}
