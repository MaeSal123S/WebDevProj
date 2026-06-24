<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }

    public function index()
    {
        if (!$this->currentUser()->hasPermission('customer', 'view')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to view customers!');
        }

        $customers = Customer::orderBy('customer_id', 'desc')->get();
        return view('admin.customers', compact('customers'));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('customer', 'add')) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'You do not have permission to add customers!');
        }

        $request->validate([
            'last_name'  => ['required'],
            'first_name' => ['required'],
        ]);

        $customer = Customer::create([
            'last_name'  => $request->last_name,
            'first_name' => $request->first_name,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'customer',
            'record_id'  => $customer->customer_id,
            'changes'    => "Created customer: {$request->first_name} {$request->last_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('customer', 'edit')) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'You do not have permission to edit customers!');
        }

        $request->validate([
            'last_name'  => ['required'],
            'first_name' => ['required'],
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update([
            'last_name'  => $request->last_name,
            'first_name' => $request->first_name,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'customer',
            'record_id'  => $id,
            'changes'    => "Updated customer: {$request->first_name} {$request->last_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('customer', 'delete')) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'You do not have permission to delete customers!');
        }

        $customer = Customer::findOrFail($id);

        Vehicle::where('customer_id', $id)->each(function ($vehicle) {
            $vehicle->delete();
        });

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'customer',
            'record_id'  => $id,
            'changes'    => "Deleted customer: {$customer->first_name} {$customer->last_name}",
            'timestamp'  => now(),
        ]);

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}