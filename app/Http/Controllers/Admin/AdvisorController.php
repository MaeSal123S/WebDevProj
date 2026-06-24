<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceAdvisor;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvisorController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }
    public function index()
    {
        if (!$this->currentUser()->hasPermission('service_advisor', 'view')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to view advisors!');
        }

        $advisors = ServiceAdvisor::orderBy('advisor_id', 'desc')->get();
        return view('admin.advisors', compact('advisors'));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('service_advisor', 'add')) {
            return redirect()->route('admin.advisors.index')
                ->with('error', 'You do not have permission to add advisors!');
        }

        $request->validate([
            'last_name'  => ['required'],
            'first_name' => ['required'],
        ]);

        $advisor = ServiceAdvisor::create([
            'last_name'  => $request->last_name,
            'first_name' => $request->first_name,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'service_advisor',
            'record_id'  => $advisor->advisor_id,
            'changes'    => "Created advisor: {$request->first_name} {$request->last_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.advisors.index')
            ->with('success', 'Advisor added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('service_advisor', 'edit')) {
            return redirect()->route('admin.advisors.index')
                ->with('error', 'You do not have permission to edit advisors!');
        }

        $request->validate([
            'last_name'  => ['required'],
            'first_name' => ['required'],
        ]);

        $advisor = ServiceAdvisor::findOrFail($id);
        $advisor->update([
            'last_name'  => $request->last_name,
            'first_name' => $request->first_name,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'service_advisor',
            'record_id'  => $id,
            'changes'    => "Updated advisor: {$request->first_name} {$request->last_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.advisors.index')
            ->with('success', 'Advisor updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('service_advisor', 'delete')) {
            return redirect()->route('admin.advisors.index')
                ->with('error', 'You do not have permission to delete advisors!');
        }

        $advisor = ServiceAdvisor::findOrFail($id);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'service_advisor',
            'record_id'  => $id,
            'changes'    => "Deleted advisor: {$advisor->first_name} {$advisor->last_name}",
            'timestamp'  => now(),
        ]);

        $advisor->delete();

        return redirect()->route('admin.advisors.index')
            ->with('success', 'Advisor deleted successfully!');
    }
}