<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $paidAppointmentsThisMonth = Appointment::whereHas('payments', fn ($query) => $query
            ->where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month));

        $upcomingTrend = collect(range(0, 6))->map(function (int $offset): array {
            $date = today()->addDays($offset);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'total' => Appointment::whereDate('starts_at', $date)->count(),
            ];
        });

        $financialTrend = collect(range(5, 0))->map(function (int $monthsAgo): array {
            $month = now()->subMonths($monthsAgo);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $paidAppointments = Appointment::whereHas('payments', fn ($query) => $query
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$start, $end]));

            return [
                'label' => $month->format('M'),
                'revenue' => (int) Payment::where('status', 'paid')->whereBetween('paid_at', [$start, $end])->sum('amount_centavos'),
                'gross' => (int) (clone $paidAppointments)->sum('gross_centavos'),
            ];
        });

        $unitTypeMix = Appointment::query()
            ->selectRaw('unit_type, COUNT(*) as total')
            ->groupBy('unit_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'label' => $appointment->unit_type,
                'total' => (int) $appointment->total,
            ]);

        return view('admin.dashboard', [
            'todayCount' => Appointment::whereDate('starts_at', today())->count(),
            'upcomingCount' => Appointment::whereIn('status', ['confirmed', 'assigned'])->where('starts_at', '>=', now())->count(),
            'pendingCount' => Appointment::whereIn('status', ['pending_payment', 'pending_confirmation'])->count(),
            'unpaidCount' => Appointment::whereIn('payment_status', ['unpaid', 'pending', 'failed'])->count(),
            'revenue' => Payment::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount_centavos'),
            'technicianPayout' => (clone $paidAppointmentsThisMonth)->sum('technician_share_centavos'),
            'gross' => (clone $paidAppointmentsThisMonth)->sum('gross_centavos'),
            'todayAppointments' => Appointment::with(['customer', 'service'])->whereDate('starts_at', today())->orderBy('starts_at')->get(),
            'recentAppointments' => Appointment::with(['customer', 'service'])->latest()->limit(6)->get(),
            'subscriptionCount' => Subscription::whereIn('status', ['pending', 'active'])->count(),
            'upcomingTrend' => $upcomingTrend,
            'financialTrend' => $financialTrend,
            'unitTypeMix' => $unitTypeMix,
            'technicians' => User::where('role', 'technician')->with('technicianLocation')->withCount([
                'technicianAppointments' => fn ($query) => $query->whereDate('starts_at', today()),
            ])->orderBy('name')->get(),
        ]);
    }
}
