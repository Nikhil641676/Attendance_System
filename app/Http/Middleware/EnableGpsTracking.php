<?php
// app/Http/Middleware/EnableGpsTracking.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnableGpsTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Check if GPS tracking is enabled for this user
        if ($user && $user->gps_tracking_enabled) {
            // Add GPS tracking headers or data
            $request->attributes->set('gps_tracking_enabled', true);
            
            // You can also start GPS tracking session here
            if (!session('gps_tracking_active')) {
                session(['gps_tracking_active' => true]);
            }
        }
        
        return $next($request);
    }
}