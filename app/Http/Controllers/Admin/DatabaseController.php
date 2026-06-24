<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function index()
    {
        $tables = [
            'customer',
            'vehicle',
            'service_type',
            'service_advisor',
            'repair_order',
            'repair_item',
            'users',
            'audit_log',
        ];

        $data = [];
        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get();
        }

        return view('admin.database', compact('data'));
    }
}