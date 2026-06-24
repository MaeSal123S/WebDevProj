<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceAdvisor;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('advisor')->orderBy('user_id', 'desc')->get();
        $advisors = ServiceAdvisor::orderBy('last_name')->get();
        $permissions = Permission::orderBy('module')
            ->orderBy('action')
            ->get()
            ->unique('permission_id')
            ->groupBy('module');

        return view('admin.users', compact('users', 'advisors', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'unique:users,username'],
            'password' => ['required', 'min:6'],
            'role'     => ['required'],
        ]);

        $user = User::create([
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'advisor_id' => $request->advisor_id ?? null,
        ]);

        // assign default permissions based on role
        $this->assignDefaultPermissions($user);

        // override with selected permissions if provided
        if ($request->has('permissions')) {
            $this->updatePermissions($user, $request->permissions);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'INSERT',
            'table_name' => 'users',
            'record_id'  => $user->user_id,
            'changes'    => "Created user: {$request->username} with role {$request->role}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // only allow admin to edit their own account
        if ($user->role === 'admin' && $id != Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit other admin accounts!');
        }

        // if admin account only allow password update
        if ($user->role === 'admin') {
            if (!empty($request->password)) {
                $request->validate([
                    'password' => ['min:6'],
                ]);
                $user->update([
                    'password' => Hash::make($request->password),
                ]);

                AuditLog::create([
                    'user_id'    => Auth::id(),
                    'action'     => 'UPDATE',
                    'table_name' => 'users',
                    'record_id'  => $id,
                    'changes'    => "Updated password for admin: {$user->username}",
                    'timestamp'  => now(),
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'Password updated successfully!');
        }

        // service advisor / customer full update
        $request->validate([
            'username' => ['required'],
            'role'     => ['required'],
        ]);

        $user->update([
            'username'   => $request->username,
            'role'       => $request->role,
            'advisor_id' => $request->advisor_id ?? null,
        ]);

        if (!empty($request->password)) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // update permissions
        if ($request->has('permissions')) {
            $this->updatePermissions($user, $request->permissions);
        } else {
            // if no permissions selected set all to 0
            UserPermission::where('user_id', $id)->update(['is_granted' => 0]);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'users',
            'record_id'  => $id,
            'changes'    => "Updated user: {$request->username} with role {$request->role}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Admin accounts cannot be deleted!');
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'DELETE',
            'table_name' => 'users',
            'record_id'  => $id,
            'changes'    => "Deleted user: {$user->username}",
            'timestamp'  => now(),
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    // assign default permissions based on role
    public function assignDefaultPermissions(User $user)
    {
        $permissions = Permission::all();
        $defaultPermissions = $this->getDefaultPermissions($user->role);

        foreach ($permissions as $permission) {
            $isGranted = in_array($permission->permission_id, $defaultPermissions) ? 1 : 0;
            UserPermission::create([
                'user_id'       => $user->user_id,
                'permission_id' => $permission->permission_id,
                'is_granted'    => $isGranted,
            ]);
        }
    }

    // get default permission IDs based on role
    public function getDefaultPermissions($role)
    {
        if ($role === 'admin') {
            return Permission::all()->pluck('permission_id')->toArray();
        }

        if ($role === 'service_advisor') {
            return Permission::whereIn('module', ['customer', 'vehicle', 'repair_order', 'appointment'])
                ->whereIn('action', ['view', 'add', 'edit'])
                ->orWhere(function ($q) {
                    $q->where('module', 'appointment')->where('action', 'delete');
                })
                ->orWhere(function ($q) {
                    $q->whereIn('module', ['service_type', 'service_advisor', 'inventory'])
                        ->where('action', 'view');
                })
                ->pluck('permission_id')
                ->toArray();
        }

        if ($role === 'customer') {
            return Permission::whereIn('module', ['appointment', 'repair_order'])
                ->where('action', 'view')
                ->orWhere(function ($q) {
                    $q->where('module', 'appointment')
                        ->whereIn('action', ['add', 'edit']);
                })
                ->pluck('permission_id')
                ->toArray();
        }

        return [];
    }

    // keep this as a protected helper used internally
    protected function updatePermissions(User $user, array $selectedPermissions)
    {
        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $isGranted = in_array($permission->permission_id, $selectedPermissions) ? 1 : 0;

            UserPermission::updateOrCreate(
                [
                    'user_id'       => $user->user_id,
                    'permission_id' => $permission->permission_id,
                ],
                ['is_granted' => $isGranted]
            );
        }
    }

    public function getPermissions($id)
    {
        $userPermissions = UserPermission::where('user_id', $id)
            ->where('is_granted', 1)
            ->pluck('permission_id');

        return response()->json($userPermissions);
    }

    public function savePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $selectedPermissions = $request->permissions ?? [];

        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $isGranted = in_array($permission->permission_id, $selectedPermissions) ? 1 : 0;

            UserPermission::updateOrCreate(
                [
                    'user_id'       => $user->user_id,
                    'permission_id' => $permission->permission_id,
                ],
                ['is_granted' => $isGranted]
            );
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'UPDATE',
            'table_name' => 'user_permissions',
            'record_id'  => $id,
            'changes'    => "Updated permissions for user: {$user->username}",
            'timestamp'  => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Permissions updated successfully!');
    }
}
