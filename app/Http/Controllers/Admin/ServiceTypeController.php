<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceTypeController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }
    public function index()
    {
        if (!$this->currentUser()->hasPermission('service_type', 'view')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to view service types!');
        }

        $service_types = ServiceType::orderBy('service_type_id', 'desc')->get();
        return view('admin.service_types', compact('service_types'));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('service_type', 'add')) {
            return redirect()->route('admin.service_types.index')
                ->with('error', 'You do not have permission to add service types!');
        }

        $request->validate([
            'service_type_name'   => ['required'],
            'predetermined_hours' => ['required', 'numeric'],
            'book_rate'           => ['required', 'numeric'],
        ]);

        $serviceType = ServiceType::create([
            'service_type_name'   => $request->service_type_name,
            'predetermined_hours' => $request->predetermined_hours,
            'book_rate'           => $request->book_rate,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'service_type',
            'record_id'  => $serviceType->service_type_id,
            'changes'    => "Created service type: {$request->service_type_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.service_types.index')
            ->with('success', 'Service type added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('service_type', 'edit')) {
            return redirect()->route('admin.service_types.index')
                ->with('error', 'You do not have permission to edit service types!');
        }

        $request->validate([
            'service_type_name'   => ['required'],
            'predetermined_hours' => ['required', 'numeric'],
            'book_rate'           => ['required', 'numeric'],
        ]);

        $serviceType = ServiceType::findOrFail($id);
        $serviceType->update([
            'service_type_name'   => $request->service_type_name,
            'predetermined_hours' => $request->predetermined_hours,
            'book_rate'           => $request->book_rate,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'service_type',
            'record_id'  => $id,
            'changes'    => "Updated service type: {$request->service_type_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.service_types.index')
            ->with('success', 'Service type updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('service_type', 'delete')) {
            return redirect()->route('admin.service_types.index')
                ->with('error', 'You do not have permission to delete service types!');
        }

        $serviceType = ServiceType::findOrFail($id);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'service_type',
            'record_id'  => $id,
            'changes'    => "Deleted service type: {$serviceType->service_type_name}",
            'timestamp'  => now(),
        ]);

        $serviceType->delete();

        return redirect()->route('admin.service_types.index')
            ->with('success', 'Service type deleted successfully!');
    }
}
