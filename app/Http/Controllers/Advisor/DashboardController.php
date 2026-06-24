<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $advisorId = Auth::user()->advisor_id;

        // Stat cards
        $advisorOrderCount = RepairOrder::where('advisor_id', $advisorId)->count();

        $advisorTodayCount = RepairOrder::where('advisor_id', $advisorId)
            ->whereDate('date_of_service', today())
            ->count();

        $pendingAppointments = Appointment::where('advisor_id', $advisorId)
            ->where('status', 'pending')
            ->count();

        $todayAppointmentCount = Appointment::where('advisor_id', $advisorId)
            ->whereDate('appointment_date', today())
            ->count();

        // Today's appointments list
        $todayAppointments = Appointment::with(['customer', 'vehicle', 'serviceTypes'])
            ->where('advisor_id', $advisorId)
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Upcoming appointments (future, pending/confirmed)
        $upcomingAppointments = Appointment::with(['customer', 'vehicle', 'serviceTypes'])
            ->where('advisor_id', $advisorId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('appointment_date', '>', today())
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(5)
            ->get();

        // Recent repair orders
        $recentOrders = RepairOrder::with(['customer', 'vehicle'])
            ->where('advisor_id', $advisorId)
            ->orderBy('order_no', 'desc')
            ->take(5)
            ->get();

        return view('advisor.dashboard', compact(
            'advisorOrderCount',
            'advisorTodayCount',
            'pendingAppointments',
            'todayAppointmentCount',
            'todayAppointments',
            'upcomingAppointments',
            'recentOrders'
        ));
    }
}
