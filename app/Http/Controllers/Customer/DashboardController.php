<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = User::find(Auth::id());
        $customer = $user->customer;

        $appointments = Appointment::with(['serviceType', 'advisor'])
            ->where('customer_id', $customer->customer_id)
            ->orderBy('appointment_date', 'desc')
            ->get();

        $pending   = $appointments->where('status', 'pending')->count();
        $confirmed = $appointments->where('status', 'confirmed')->count();
        $completed = $appointments->where('status', 'completed')->count();

        $upcoming = $appointments
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', today()->toDateString())
            ->sortBy('appointment_date')
            ->take(5);

        return view('customer.dashboard', compact(
            'customer',
            'appointments',
            'pending',
            'confirmed',
            'completed',
            'upcoming'
        ));
    }
}
