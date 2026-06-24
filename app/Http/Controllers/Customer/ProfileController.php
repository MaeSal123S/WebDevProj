<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private function customer()
    {
        return User::find(Auth::id())->customer;
    }

    public function index()
    {
        $customer = $this->customer();
        $vehicles = Vehicle::where('customer_id', $customer->customer_id)->get();
        return view('customer.profile', compact('customer', 'vehicles'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
        ]);

        $customer = $this->customer();
        $customer->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'customer',
            'record_id'  => $customer->customer_id,
            'changes'    => "Customer updated profile: {$request->first_name} {$request->last_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('customer.profile')->with('success', 'Profile updated successfully!');
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20',
            'model'        => 'required|string|max:50',
        ]);

        $customer = $this->customer();

        $vehicle = Vehicle::create([
            'plate_number' => strtoupper($request->plate_number),
            'model'        => $request->model,
            'customer_id'  => $customer->customer_id,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'vehicle',
            'record_id'  => $vehicle->vehicle_id,
            'changes'    => "Customer added vehicle: {$request->plate_number}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('customer.profile')->with('success', 'Vehicle added successfully!');
    }

    public function updateVehicle(Request $request, $id)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20',
            'model'        => 'required|string|max:50',
        ]);

        $customer = $this->customer();
        $vehicle  = Vehicle::where('vehicle_id', $id)
            ->where('customer_id', $customer->customer_id)
            ->firstOrFail();

        $vehicle->update([
            'plate_number' => strtoupper($request->plate_number),
            'model'        => $request->model,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'vehicle',
            'record_id'  => $id,
            'changes'    => "Customer updated vehicle: {$request->plate_number}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('customer.profile')->with('success', 'Vehicle updated successfully!');
    }

    public function destroyVehicle($id)
    {
        $customer = $this->customer();
        $vehicle  = Vehicle::where('vehicle_id', $id)
            ->where('customer_id', $customer->customer_id)
            ->firstOrFail();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'vehicle',
            'record_id'  => $id,
            'changes'    => "Customer deleted vehicle: {$vehicle->plate_number}",
            'timestamp'  => now(),
        ]);

        $vehicle->delete();

        return redirect()->route('customer.profile')->with('success', 'Vehicle removed successfully!');
    }
}
