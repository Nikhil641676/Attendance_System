<?php
// app/Http/Controllers/ManagerController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\GpsTracking;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:manager']);
    }

    public function dashboard()
    {
        $manager = auth()->user();
        $team = $manager->team;
        
        $todayAttendance = Attendance::whereIn('user_id', $team->pluck('id'))
            ->where('date', today())
            ->get();
        
        $presentCount = $todayAttendance->where('status', '!=', 'absent')->count();
        $absentCount = $team->count() - $presentCount;
        
        $recentActivities = Attendance::whereIn('user_id', $team->pluck('id'))
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        
        return view('manager.dashboard', compact('team', 'todayAttendance', 'presentCount', 'absentCount', 'recentActivities'));
    }

    public function teamAttendance(Request $request)
    {
        $manager = auth()->user();
        $team = $manager->team;
        
        $query = Attendance::with('user')
            ->whereIn('user_id', $team->pluck('id'));
        
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $attendances = $query->latest('date')->paginate(20);
        
        return view('manager.team-attendance', compact('attendances', 'team'));
    }

    public function teamGpsTracking(Request $request)
    {
        $manager = auth()->user();
        $team = $manager->team()->where('gps_tracking_enabled', true)->get();
        
        $user = null;
        $trackings = collect();
        
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user && $user->manager_id === $manager->id) {
                $date = $request->get('date', today());
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', $date)
                    ->first();
                
                if ($attendance) {
                    $trackings = $attendance->gpsTrackings()
                        ->orderBy('tracked_at')
                        ->get();
                }
            }
        }
        
        return view('manager.gps-tracking', compact('team', 'user', 'trackings'));
    }

    public function getTeamGpsData(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date'
        ]);
        
        $manager = auth()->user();
        $user = User::find($request->user_id);
        
        if ($user->manager_id !== $manager->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $request->date)
            ->first();
        
        if (!$attendance) {
            return response()->json(['error' => 'No attendance found'], 404);
        }
        
        $trackings = $attendance->gpsTrackings()
            ->orderBy('tracked_at')
            ->get();
        
        $route = [];
        foreach ($trackings as $tracking) {
            $route[] = [
                'lat' => $tracking->latitude,
                'lng' => $tracking->longitude,
                'time' => $tracking->tracked_at->format('H:i:s')
            ];
        }
        
        return response()->json([
            'clock_in' => [
                'lat' => $attendance->clock_in_latitude,
                'lng' => $attendance->clock_in_longitude,
                'time' => $attendance->clock_in_time
            ],
            'clock_out' => [
                'lat' => $attendance->clock_out_latitude,
                'lng' => $attendance->clock_out_longitude,
                'time' => $attendance->clock_out_time
            ],
            'route' => $route
        ]);
    }
}