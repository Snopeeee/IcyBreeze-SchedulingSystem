<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\RouteOptimizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechnicianDashboardController extends Controller
{
    public function __invoke(Request $request, RouteOptimizer $optimizer): View
    {
        $coordinates = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
        ]);

        $technician = $request->user('technician');
        $appointments = Appointment::with(['customer', 'service', 'airconUnitType'])
            ->where('technician_id', $technician->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [today()->startOfDay(), today()->addDays(14)->endOfDay()])
            ->orderBy('starts_at')->get();

        $lastLocation = $technician->technicianLocation;
        $originLatitude = isset($coordinates['lat']) ? (float) $coordinates['lat'] : (float) ($lastLocation?->latitude ?? 8.2280);
        $originLongitude = isset($coordinates['lng']) ? (float) $coordinates['lng'] : (float) ($lastLocation?->longitude ?? 124.2452);
        $todayAppointments = $appointments->filter(fn ($appointment) => $appointment->starts_at->isToday());
        $optimizedRoute = $optimizer->optimize($todayAppointments, $originLatitude, $originLongitude);

        return view('technician.dashboard', [
            'technician' => $technician,
            'appointments' => $appointments,
            'todayAppointments' => $todayAppointments,
            'optimizedRoute' => $optimizedRoute,
            'directionsUrl' => $optimizer->directionsUrl($optimizedRoute, $originLatitude, $originLongitude),
            'lastLocation' => $lastLocation,
        ]);
    }
}
