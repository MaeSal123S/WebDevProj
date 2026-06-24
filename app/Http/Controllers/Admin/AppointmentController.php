<?php

namespace App\Http\Controllers\Admin;

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
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to view appointments!');
        }

        $appointments  = Appointment::with(['customer', 'vehicle', 'serviceTypes', 'advisor', 'bookedBy'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();
        $customers     = Customer::orderBy('last_name')->get();
        $vehicles      = Vehicle::orderBy('model')->get();
        $service_types = ServiceType::orderBy('service_type_name')->get();
        $advisors      = ServiceAdvisor::orderBy('last_name')->get();

        return view('admin.appointments', compact(
            'appointments',
            'customers',
            'vehicles',
            'service_types',
            'advisors'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'add')) {
            return redirect()->route('admin.appointments.index')
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
            'service_type_id'  => $request->service_type_ids[0], // keep legacy column
            'advisor_id'       => $request->advisor_id ?? null,
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

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment created successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'edit')) {
            return redirect()->route('admin.appointments.index')
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
            'advisor_id'       => $request->advisor_id ?? null,
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

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $request->status]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'appointments',
            'record_id'  => $id,
            'changes'    => "Updated appointment #{$id} status to {$request->status}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment status updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('appointment', 'delete')) {
            return redirect()->route('admin.appointments.index')
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

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }

    public function calendar()
    {
        return view('admin.calendar');
    }

    public function calendarData()
    {
        $appointments = Appointment::with(['customer', 'serviceTypes', 'advisor'])
            ->get()
            ->map(function ($appointment) {
                // Vivid solid colors that pop on dark calendar cells
                $statusColors = [
                    'pending'   => '#b86e00',  // warm amber
                    'confirmed' => '#0e6b38',  // strong green
                    'cancelled' => '#8a1515',  // deep red
                    'completed' => '#3d2480',  // rich purple
                ];

                $serviceNames = $appointment->serviceTypes->isNotEmpty()
                    ? $appointment->serviceTypes->pluck('service_type_name')->join(', ')
                    : ($appointment->serviceType->service_type_name ?? '—');

                return [
                    'id'    => $appointment->appointment_id,
                    'title' => $appointment->customer
                        ? $appointment->customer->first_name . ' ' . $appointment->customer->last_name
                        : 'Unknown',
                    'start' => $appointment->appointment_date . 'T' . $appointment->appointment_time,
                    'color' => $statusColors[$appointment->status] ?? '#2d1a5c',
                    'extendedProps' => [
                        'service'  => $serviceNames,
                        'advisor'  => $appointment->advisor
                            ? $appointment->advisor->first_name . ' ' . $appointment->advisor->last_name
                            : '—',
                        'status'   => $appointment->status,
                        'notes'    => $appointment->notes ?? '—',
                    ],
                ];
            });

        return response()->json($appointments);
    }
}