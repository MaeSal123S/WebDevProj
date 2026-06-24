<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\SupplyUsage;
use App\Models\RepairOrder;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    private function currentUser()
    {
        return User::find(Auth::id());
    }

    public function index()
    {
        if (!$this->currentUser()->hasPermission('inventory', 'view')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to view inventory!');
        }

        $supplies    = Supply::orderBy('supply_name', 'asc')->get();
        $lowStock    = Supply::where('current_stock', '<=', \DB::raw('minimum_stock'))
                        ->orderBy('supply_name')
                        ->get();
        $repairOrders = RepairOrder::orderBy('order_no', 'desc')->get();

        return view('admin.inventory', compact('supplies', 'lowStock', 'repairOrders'));
    }

    public function store(Request $request)
    {
        if (!$this->currentUser()->hasPermission('inventory', 'add')) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'You do not have permission to add supplies!');
        }

        $request->validate([
            'supply_name'   => ['required', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:20'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $supply = Supply::create([
            'supply_name'    => $request->supply_name,
            'unit'           => $request->unit,
            'current_stock'  => $request->current_stock,
            'minimum_stock'  => $request->minimum_stock,
            'price_per_unit' => $request->price_per_unit,
            'created_at'     => now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'supplies',
            'record_id'  => $supply->supply_id,
            'changes'    => "Added supply: {$request->supply_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Supply added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!$this->currentUser()->hasPermission('inventory', 'edit')) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'You do not have permission to edit supplies!');
        }

        $request->validate([
            'supply_name'    => ['required', 'string', 'max:100'],
            'unit'           => ['required', 'string', 'max:20'],
            'current_stock'  => ['required', 'numeric', 'min:0'],
            'minimum_stock'  => ['required', 'numeric', 'min:0'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $supply = Supply::findOrFail($id);
        $supply->update([
            'supply_name'    => $request->supply_name,
            'unit'           => $request->unit,
            'current_stock'  => $request->current_stock,
            'minimum_stock'  => $request->minimum_stock,
            'price_per_unit' => $request->price_per_unit,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'supplies',
            'record_id'  => $id,
            'changes'    => "Updated supply: {$request->supply_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Supply updated successfully!');
    }

    public function destroy($id)
    {
        if (!$this->currentUser()->hasPermission('inventory', 'delete')) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'You do not have permission to delete supplies!');
        }

        $supply = Supply::findOrFail($id);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'supplies',
            'record_id'  => $id,
            'changes'    => "Deleted supply: {$supply->supply_name}",
            'timestamp'  => now(),
        ]);

        $supply->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Supply deleted successfully!');
    }

    public function restock(Request $request, $id)
    {
        $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $supply = Supply::findOrFail($id);
        $supply->update([
            'current_stock' => $supply->current_stock + $request->quantity,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'supplies',
            'record_id'  => $id,
            'changes'    => "Restocked {$request->quantity} {$supply->unit} of {$supply->supply_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Stock updated successfully!');
    }

    public function recordUsage(Request $request)
    {
        $request->validate([
            'supply_id'      => ['required'],
            'quantity_used'  => ['required', 'numeric', 'min:0.01'],
        ]);

        $supply = Supply::findOrFail($request->supply_id);

        if ($supply->current_stock < $request->quantity_used) {
            return redirect()->route('admin.inventory.index')
                ->with('error', "Insufficient stock! Available: {$supply->current_stock} {$supply->unit}");
        }

        SupplyUsage::create([
            'supply_id'     => $request->supply_id,
            'order_no'      => $request->order_no ?? null,
            'quantity_used' => $request->quantity_used,
            'used_at'       => now(),
            'notes'         => $request->notes ?? null,
        ]);

        $supply->update([
            'current_stock' => $supply->current_stock - $request->quantity_used,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'supplies',
            'record_id'  => $supply->supply_id,
            'changes'    => "Used {$request->quantity_used} {$supply->unit} of {$supply->supply_name}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Usage recorded successfully!');
    }
}