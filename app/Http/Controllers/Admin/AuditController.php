<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')
            ->whereIn('action', ['INSERT', 'UPDATE', 'DELETE']) //filter
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('admin.audit_log', compact('logs'));
    }
}