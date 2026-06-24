<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\RepairItem;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\ServiceType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepairOrderController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }
    public function index()
    {
        if (!$this->currentUser()->hasPermission('repair_order', 'view')) {
            return redirect()->route('advisor.dashboard')
                ->with('error', 'You do not have permission to view repair orders!');
        }

        $advisorId = Auth::user()->advisor_id;

        $repair_orders = RepairOrder::with(['customer', 'vehicle', 'serviceTypes'])
            ->where('advisor_id', $advisorId)
            ->orderBy('order_no', 'desc')
            ->get();

        $customers     = Customer::orderBy('last_name')->get();
        $vehicles      = Vehicle::orderBy('model')->get();
        $service_types = ServiceType::orderBy('service_type_name')->get();

        return view('advisor.repair_orders', compact(
            'repair_orders',
            'customers',
            'vehicles',
            'service_types'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('repair_order', 'add')) {
            return redirect()->route('advisor.repair_orders.index')
                ->with('error', 'You do not have permission to add repair orders!');
        }

        $request->validate([
            'date_of_service'  => ['required', 'date'],
            'customer_id'      => ['required'],
            'vehicle_id'       => ['required'],
            'service_type_ids' => ['required', 'array'],
        ]);

        $order = RepairOrder::create([
            'date_of_service' => $request->date_of_service,
            'customer_id'     => $request->customer_id,
            'vehicle_id'      => $request->vehicle_id,
            'advisor_id'      => Auth::user()->advisor_id,
        ]);

        foreach ($request->service_type_ids as $service_type_id) {
            RepairItem::create([
                'order_no'        => $order->order_no,
                'service_type_id' => $service_type_id,
            ]);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'repair_order',
            'record_id'  => $order->order_no,
            'changes'    => "Created repair order #{$order->order_no}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.repair_orders.index')
            ->with('success', 'Repair order created successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('repair_order', 'edit')) {
            return redirect()->route('advisor.repair_orders.index')
                ->with('error', 'You do not have permission to edit repair orders!');
        }

        $request->validate([
            'date_of_service'  => ['required', 'date'],
            'customer_id'      => ['required'],
            'vehicle_id'       => ['required'],
            'service_type_ids' => ['required', 'array'],
        ]);

        $order = RepairOrder::findOrFail($id);
        $order->update([
            'date_of_service' => $request->date_of_service,
            'customer_id'     => $request->customer_id,
            'vehicle_id'      => $request->vehicle_id,
        ]);

        RepairItem::where('order_no', $id)->delete();
        foreach ($request->service_type_ids as $service_type_id) {
            RepairItem::create([
                'order_no'        => $id,
                'service_type_id' => $service_type_id,
            ]);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'repair_order',
            'record_id'  => $id,
            'changes'    => "Updated repair order #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.repair_orders.index')
            ->with('success', 'Repair order updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('repair_order', 'delete')) {
            return redirect()->route('advisor.repair_orders.index')
                ->with('error', 'You do not have permission to delete repair orders!');
        }

        RepairItem::where('order_no', $id)->delete();
        RepairOrder::findOrFail($id)->delete();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'repair_order',
            'record_id'  => $id,
            'changes'    => "Deleted repair order #{$id}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.repair_orders.index')
            ->with('success', 'Repair order deleted successfully!');
    }

    public function getVehiclesByCustomer($customer_id)
    {
        $vehicles = Vehicle::where('customer_id', $customer_id)->get();
        return response()->json($vehicles);
    }

    public function getAppointmentByCustomer($customer_id)
    {
        $appointment = \App\Models\Appointment::with(['vehicle', 'serviceTypes'])
            ->where('customer_id', $customer_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->first();

        if (!$appointment) {
            return response()->json(null);
        }

        return response()->json([
            'vehicle_id'       => $appointment->vehicle_id,
            'plate_number'     => $appointment->vehicle->plate_number ?? null,
            'model'            => $appointment->vehicle->model ?? null,
            'service_type_ids' => $appointment->serviceTypes->pluck('service_type_id'),
        ]);
    }
}
