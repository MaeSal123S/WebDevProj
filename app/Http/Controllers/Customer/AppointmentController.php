<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    private function customer()
    {
        return User::find(Auth::id())->customer;
    }

    public function index()
    {
        $customer = $this->customer();

        $appointments = Appointment::with(['serviceType', 'advisor', 'vehicle'])
            ->where('customer_id', $customer->customer_id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        $service_types = ServiceType::orderBy('service_type_name')->get();
        $vehicles      = Vehicle::where('customer_id', $customer->customer_id)->get();

        return view('customer.appointments', compact('appointments', 'service_types', 'vehicles', 'customer'));
    }

    public function store(Request $request)
    {
        $customer = $this->customer();

        $request->validate([
            'service_type_id'  => 'required',
            'vehicle_id'       => 'required',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Make sure the vehicle belongs to this customer
        $vehicle = Vehicle::where('vehicle_id', $request->vehicle_id)
            ->where('customer_id', $customer->customer_id)
            ->firstOrFail();

        $appointment = Appointment::create([
            'customer_id'      => $customer->customer_id,
            'vehicle_id'       => $vehicle->vehicle_id,
            'service_type_id'  => $request->service_type_id,
            'advisor_id'       => null,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status'           => 'pending',
            'notes'            => $request->notes ?? null,
            'booked_by'        => Auth::id(),
            'created_at'       => now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'appointments',
            'record_id'  => $appointment->appointment_id,
            'changes'    => "Customer booked appointment on {$request->appointment_date}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('customer.appointments.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function destroy($id)
    {
        $customer    = $this->customer();
        $appointment = Appointment::where('appointment_id', $id)
            ->where('customer_id', $customer->customer_id)
            ->firstOrFail();

        // Only allow cancelling pending appointments
        if (!in_array($appointment->status, ['pending'])) {
            return redirect()->route('customer.appointments.index')
                ->with('error', 'Only pending appointments can be cancelled.');
        }

        $appointment->update(['status' => 'cancelled']);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Customer cancelled appointment #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('customer.appointments.index')
            ->with('success', 'Appointment cancelled.');
    }
}
