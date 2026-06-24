<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    private $maxAttempts = 5;
    private $lockSeconds = 30;

    //checks if there's a user logged in already
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;

        // check if locked
        $attempt = LoginAttempt::where('username', $username)->first();

        if ($attempt && $attempt->locked_until) {
            $lockedUntil = Carbon::parse($attempt->locked_until);
            if (Carbon::now()->lt($lockedUntil)) {
                $secondsLeft = Carbon::now()->diffInSeconds($lockedUntil);

                // log locked attempt
                $this->logAnonymous('LOGIN_BLOCKED', $username,
                    "Blocked login attempt for locked account: $username ({$secondsLeft}s remaining)");

                return back()
                    ->with('error', "Too many failed attempts. Please wait {$secondsLeft} seconds before trying again.")
                    ->withInput();
            } else {
                $attempt->update([
                    'attempts' => 0,
                    'locked_until' => null,
                ]);
            }
        }

        $user = User::where('username', $username)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // reset attempts on success
            if ($attempt) {
                $attempt->update([
                    'attempts' => 0,
                    'locked_until' => null,
                ]);
            }

            //make session for user
            Auth::login($user);
            $request->session()->regenerate();

            // on successful login, create audit log
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'LOGIN',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'changes' => "User logged in: {$username}",
                'timestamp' => now(),
            ]);

            return $this->redirectByRole();
        }

        // login failed increment attempts
        if ($attempt) {
            $newAttempts = $attempt->attempts + 1;
            $lockedUntil = null;

            if ($newAttempts >= $this->maxAttempts) {
                $lockedUntil = Carbon::now()->addSeconds($this->lockSeconds);

                // log account locked
                $this->logAnonymous('LOGIN_LOCKED', $username,
                    "Account locked after {$this->maxAttempts} failed attempts: $username");
            } else {
                // log failed attempt
                $this->logAnonymous('LOGIN_FAILED', $username,
                    "Failed login attempt for: $username (attempt {$newAttempts} of {$this->maxAttempts})");
            }

            $attempt->update([
                'attempts' => $newAttempts,
                'last_attempt' => Carbon::now(),
                'locked_until' => $lockedUntil,
            ]);
        } else {
            // first failed attempt adds record
            $this->logAnonymous('LOGIN_FAILED', $username,
                "Failed login attempt for: $username (attempt 1 of {$this->maxAttempts})");

            LoginAttempt::create([
                'username' => $username,
                'attempts' => 1,
                'last_attempt' => Carbon::now(),
                'locked_until' => null,
            ]);
        }

        $remaining = $this->maxAttempts - ($attempt ? $attempt->attempts + 1 : 1);

        if ($remaining > 0) {
            return back()
                ->with('error', "Invalid username or password! {$remaining} attempts remaining.")
                ->withInput();
        }

        return back()
            ->with('error', "Too many failed attempts. Please wait {$this->lockSeconds} seconds.")
            ->withInput();
    }

    public function logout(Request $request)
    {
        // log logout
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'LOGOUT',
            'table_name' => 'users',
            'record_id' => Auth::id(),
            'changes' => "User logged out: " . Auth::user()->username,
            'timestamp' => now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->with('error', 'Username not found!');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Admin password cannot be reset here. Please contact the system administrator.');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // log password reset
        $this->logAnonymous('PASSWORD_RESET', $request->username,
            "Password reset for: {$request->username}");

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
    }

    // log events that happen before login (no user_id available)
    private function logAnonymous($action, $username, $changes)
    {
        $user = User::where('username', $username)->first();

        AuditLog::create([
            'user_id' => $user ? $user->user_id : 0,
            'action' => $action,
            'table_name' => 'users',
            'record_id' => $user ? $user->user_id : 0,
            'changes' => $changes,
            'timestamp' => now(),
        ]);
    }

    private function redirectByRole()
    {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::user()->role === 'service_advisor') {
            return redirect()->route('advisor.dashboard');
        } else {
            return redirect()->route('customer.dashboard');
        }
    }

    
}