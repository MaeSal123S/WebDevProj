<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'username'   => 'required|string|max:50|unique:users,username',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        // Create customer record
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
        ]);

        // Create linked user account
        $user = User::create([
            'username'    => $request->username,
            'password'    => Hash::make($request->password),
            'role'        => 'customer',
            'customer_id' => $customer->customer_id,
        ]);

        AuditLog::create([
            'user_id'    => $user->user_id,
            'action'     => 'INSERT',
            'table_name' => 'users',
            'record_id'  => $user->user_id,
            'changes'    => "New customer registered: {$request->username}",
            'timestamp'  => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Account created successfully! Welcome, ' . $request->first_name . '!');
    }
}
