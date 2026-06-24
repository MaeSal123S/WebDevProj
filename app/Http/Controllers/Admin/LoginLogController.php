<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class LoginLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')
            ->whereIn('action', [              //filter
                'LOGIN',
                'LOGOUT',
                'LOGIN_FAILED',
                'LOGIN_LOCKED',
                'LOGIN_BLOCKED',
                'PASSWORD_RESET'
            ])
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('admin.login_logs', compact('logs'));
    }
}