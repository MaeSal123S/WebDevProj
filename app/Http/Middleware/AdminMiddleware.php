<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'admin') {
            if (Auth::user()->role === 'service_advisor') {
                return redirect()->route('advisor.dashboard');
            }
            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}