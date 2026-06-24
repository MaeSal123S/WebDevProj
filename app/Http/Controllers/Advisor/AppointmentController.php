<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceType;
use App\Models\ServiceAdvisor;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }

    public function index()
    {
        if (!$this->currentUser()->hasPermission('appointment', 'view')) {
            return redirect()->route('advisor.dashboard')
                ->with('error', 'You do not have permission to view appointments!');
        }

        $advisorId     = Auth::user()->advisor_id;
        $appointments  = Appointment::with(['customer', 'vehicle', 'serviceType', 'bookedBy'])
            ->where('advisor_id', $advisorId)
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();
        $customers     = Customer::orderBy('last_name')->get();
        $service_types = ServiceType::orderBy('service_type_name')->get();

        return view('advisor.appointments', compact(
            'appointments',
            'customers',
            'service_types'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'add')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to add appointments!');
        }

        $request->validate([
            'customer_id'      => ['required'],
            'vehicle_id'       => ['required'],
            'service_type_id'  => ['required'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
        ]);

        $appointment = Appointment::create([
            'customer_id'      => $request->customer_id,
            'vehicle_id'       => $request->vehicle_id,
            'service_type_id'  => $request->service_type_id,
            'advisor_id'       => Auth::user()->advisor_id,
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
            'changes'    => "Created appointment for customer #{$request->customer_id} on {$request->appointment_date}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment created successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'edit')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to edit appointments!');
        }

        $request->validate([
            'customer_id'      => ['required'],
            'vehicle_id'       => ['required'],
            'service_type_id'  => ['required'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'customer_id'      => $request->customer_id,
            'vehicle_id'       => $request->vehicle_id,
            'service_type_id'  => $request->service_type_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'notes'            => $request->notes ?? null,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Updated appointment #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'delete')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to delete appointments!');
        }

        $appointment = Appointment::findOrFail($id);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Deleted appointment #{$id}",
            'timestamp'  => now(),
        ]);

        $appointment->delete();

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }
}