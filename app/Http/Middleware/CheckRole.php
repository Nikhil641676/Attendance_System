<?php
// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }
        
        // User doesn't have required role, redirect based on their actual role
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        } elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        } elseif ($user->hasRole('employee')) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        // Fallback
        Auth::logout();
        return redirect()->route('login')
            ->with('error', 'Unauthorized access. Please contact administrator.');
    }
}