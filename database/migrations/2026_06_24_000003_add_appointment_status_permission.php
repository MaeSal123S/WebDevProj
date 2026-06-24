<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert the new permission if it doesn't already exist
        $exists = DB::table('permissions')
            ->where('module', 'appointment')
            ->where('action', 'status')
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'module'       => 'appointment',
                'action'       => 'status',
                'display_name' => 'Change Appointment Status',
            ]);
        }

        // Grant it to all admins automatically
        $permission = DB::table('permissions')
            ->where('module', 'appointment')
            ->where('action', 'status')
            ->first();

        $admins = DB::table('users')->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            DB::table('user_permissions')->updateOrInsert(
                ['user_id' => $admin->user_id, 'permission_id' => $permission->permission_id],
                ['is_granted' => 1]
            );
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('module', 'appointment')
            ->where('action', 'status')
            ->first();

        if ($permission) {
            DB::table('user_permissions')
                ->where('permission_id', $permission->permission_id)
                ->delete();
            DB::table('permissions')
                ->where('permission_id', $permission->permission_id)
                ->delete();
        }
    }
};
