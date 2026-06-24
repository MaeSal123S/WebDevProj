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

        $advisorId = Auth::user()->advisor_id;

        // Unassigned bookings from customers (no advisor yet, still pending)
        $pendingBookings = Appointment::with(['customer', 'vehicle', 'serviceTypes', 'bookedBy'])
            ->whereNull('advisor_id')
            ->where('status', 'pending')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        // This advisor's own accepted appointments
        $myAppointments = Appointment::with(['customer', 'vehicle', 'serviceTypes', 'bookedBy'])
            ->where('advisor_id', $advisorId)
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        $customers     = Customer::orderBy('last_name')->get();
        $service_types = ServiceType::orderBy('service_type_name')->get();

        return view('advisor.appointments', compact(
            'pendingBookings',
            'myAppointments',
            'customers',
            'service_types'
        ));
    }

    // Accept: assign this advisor + set confirmed
    public function accept($id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'edit')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to accept appointments!');
        }

        $appointment = Appointment::whereNull('advisor_id')
            ->where('status', 'pending')
            ->findOrFail($id);

        $advisorId = Auth::user()->advisor_id;

        $appointment->update([
            'advisor_id' => $advisorId,
            'status'     => 'confirmed',
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Advisor #{$advisorId} accepted appointment #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment accepted — you are now the assigned advisor.');
    }

    // Decline: set cancelled, leave advisor_id null
    public function decline($id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'edit')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to decline appointments!');
        }

        $appointment = Appointment::whereNull('advisor_id')
            ->where('status', 'pending')
            ->findOrFail($id);

        $appointment->update(['status' => 'cancelled']);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Advisor declined appointment #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment declined.');
    }

    // Update status (requires appointment.status permission)
    public function updateStatus(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'status')) {
            return redirect()->route('advisor.appointments.index')
                ->with('error', 'You do not have permission to change appointment status!');
        }

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $advisorId   = Auth::user()->advisor_id;
        $appointment = Appointment::where('advisor_id', $advisorId)->findOrFail($id);
        $appointment->update(['status' => $request->status]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Updated appointment #{$id} status to {$request->status}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.appointments.index')
            ->with('success', 'Appointment status updated.');
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
            'service_type_ids' => ['required', 'array', 'min:1'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
        ]);

        $appointment = Appointment::create([
            'customer_id'      => $request->customer_id,
            'vehicle_id'       => $request->vehicle_id,
            'service_type_id'  => $request->service_type_ids[0],
            'advisor_id'       => Auth::user()->advisor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status'           => 'pending',
            'notes'            => $request->notes ?? null,
            'booked_by'        => Auth::id(),
            'created_at'       => now(),
        ]);

        $appointment->serviceTypes()->sync($request->service_type_ids);

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
            'service_type_ids' => ['required', 'array', 'min:1'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'customer_id'      => $request->customer_id,
            'vehicle_id'       => $request->vehicle_id,
            'service_type_id'  => $request->service_type_ids[0],
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'notes'            => $request->notes ?? null,
        ]);

        $appointment->serviceTypes()->sync($request->service_type_ids);

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