<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Super admin has all permissions
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // Check specific permission
        if ($user->can($permission)) {
            return $next($request);
        }
        
        // User doesn't have required permission
        abort(403, 'You do not have permission to perform this action.');
    }
}