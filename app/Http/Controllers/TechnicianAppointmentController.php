<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TechnicianAppointmentController extends Controller
{
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->technician_id === $request->user('technician')->id, 403);
        $data = $request->validate(['status' => ['required', Rule::in(['assigned', 'in_progress', 'completed'])]]);
        $oldStatus = $appointment->status;
        $updates = ['status' => $data['status']];
        if ($data['status'] === 'completed') {
            $updates['completed_at'] = now();
        }
        $appointment->update($updates);
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => $oldStatus,
            'to_status' => $data['status'],
            'actor_type' => 'technician',
            'actor_name' => $request->user('technician')->name,
            'reason' => 'Updated from technician mobile account.',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Job status updated.');
    }
}
