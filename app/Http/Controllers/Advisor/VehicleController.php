<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }
    public function index()
    {
        if (!$this->currentUser()->hasPermission('vehicle', 'view')) {
            return redirect()->route('advisor.dashboard')
                ->with('error', 'You do not have permission to view vehicles!');
        }

        $vehicles  = Vehicle::with('customer')->orderBy('vehicle_id', 'desc')->get();
        $customers = Customer::orderBy('last_name')->get();
        return view('advisor.vehicles', compact('vehicles', 'customers'));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('vehicle', 'add')) {
            return redirect()->route('advisor.vehicles.index')
                ->with('error', 'You do not have permission to add vehicles!');
        }

        $request->validate([
            'plate_number' => ['required'],
            'model'        => ['required'],
            'customer_id'  => ['required'],
        ]);

        $vehicle = Vehicle::create([
            'plate_number' => $request->plate_number,
            'model'        => $request->model,
            'customer_id'  => $request->customer_id,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'vehicle',
            'record_id'  => $vehicle->vehicle_id,
            'changes'    => "Created vehicle: {$request->plate_number} - {$request->model}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.vehicles.index')
            ->with('success', 'Vehicle added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('vehicle', 'edit')) {
            return redirect()->route('advisor.vehicles.index')
                ->with('error', 'You do not have permission to edit vehicles!');
        }

        $request->validate([
            'plate_number' => ['required'],
            'model'        => ['required'],
            'customer_id'  => ['required'],
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update([
            'plate_number' => $request->plate_number,
            'model'        => $request->model,
            'customer_id'  => $request->customer_id,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'vehicle',
            'record_id'  => $id,
            'changes'    => "Updated vehicle: {$request->plate_number} - {$request->model}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('advisor.vehicles.index')
            ->with('success', 'Vehicle updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('vehicle', 'delete')) {
            return redirect()->route('advisor.vehicles.index')
                ->with('error', 'You do not have permission to delete vehicles!');
        }

        $vehicle = Vehicle::findOrFail($id);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'vehicle',
            'record_id'  => $id,
            'changes'    => "Deleted vehicle: {$vehicle->plate_number} - {$vehicle->model}",
            'timestamp'  => now(),
        ]);

        $vehicle->delete();

        return redirect()->route('advisor.vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }
}
