<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with(['customer', 'service', 'airconUnitType']);

        $query->when($request->filled('status'), function ($q) use ($request) {
            // Historical assignments remain confirmed bookings, without an assignment workflow.
            $statuses = $request->input('status') === 'confirmed'
                ? ['confirmed', 'assigned']
                : [$request->input('status')];
            $q->whereIn('status', $statuses);
        })
            ->when($request->filled('payment'), fn ($q) => $q->where('payment_status', $request->string('payment')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhere('phone', 'like', $term));
                });
            });

        return view('admin.appointments.index', ['appointments' => $query->orderBy('starts_at')->paginate(15)->withQueryString()]);
    }

    public function show(Appointment $appointment): View
    {
        return view('admin.appointments.show', [
            'appointment' => $appointment->load(['customer', 'service', 'airconUnitType', 'payments', 'histories']),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending_confirmation', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $oldStatus = $appointment->status;
        $updates = ['status' => $data['status']];
        if ($data['status'] === 'confirmed') $updates['confirmed_at'] = now();
        if ($data['status'] === 'completed') $updates['completed_at'] = now();
        if ($data['status'] === 'cancelled') {
            $updates['cancelled_at'] = now();
            $updates['cancellation_reason'] = $data['reason'] ?? 'Cancelled by admin.';
        }
        $appointment->update($updates);

        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => $oldStatus,
            'to_status' => $data['status'],
            'actor_type' => 'admin',
            'actor_name' => $request->user()->name,
            'reason' => $data['reason'] ?? null,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Appointment status updated.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', Rule::in(config('scheduling.arrival_times'))],
        ]);

        $startsAt = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['appointment_date'].' '.$data['appointment_time'], config('app.timezone'));
        $minutes = $appointment->service->duration_minutes + (($appointment->quantity - 1) * 45) + $appointment->service->buffer_minutes;
        $endsAt = $startsAt->addMinutes($minutes);
        $conflict = Appointment::whereKeyNot($appointment->id)->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt)->exists();

        if ($conflict) return back()->withErrors(['appointment_date' => 'That schedule is already occupied.']);

        $appointment->update(['starts_at' => $startsAt, 'ends_at' => $endsAt]);
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => $appointment->status,
            'to_status' => $appointment->status,
            'actor_type' => 'admin',
            'actor_name' => $request->user()->name,
            'reason' => 'Rescheduled to '.$startsAt->format('M j, Y g:i A'),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Appointment rescheduled.');
    }

}
