<?php
// app/Http/Controllers/EmployeeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\GpsTracking;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:employee']);
    }

    public function dashboard()
    {
     
        $user = auth()->user();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->first();
        
        $currentTime = now();
        $canClockIn = !$todayAttendance || !$todayAttendance->clock_in_time;
        $canClockOut = $todayAttendance && $todayAttendance->clock_in_time && !$todayAttendance->clock_out_time;
        
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
        
           // dd($user);
        $allowedLocations = $user->locations()->where('is_active', true)->get();
    //    dd($allowedLocations);
        
        return view('employee.dashboard', compact('todayAttendance', 'canClockIn', 'canClockOut', 'recentAttendances', 'allowedLocations'));
    }

    public function clockIn(Request $request)
    {
    
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $user = auth()->user();
        $location_id = "1";
        $location = Location::findOrFail($location_id);
        
      
        // Check if user is allowed to clock in at this location
        if (!$user->locations->contains($location->id)) {
            return response()->json(['error' => 'You are not authorized to clock in at this location'], 403);
        }

       
        
        // Check if within geofence
        // $distance = $location->checkDistance($request->latitude, $request->longitude);
        $distance = $location->checkDistance($request->latitude, $request->longitude, $location->latitude,$location->longitude);


        if ($distance > $location->radius) {
            return response()->json(['error' => 'You are outside the allowed geofence. Distance: ' . round($distance) . ' meters'], 403);
        }
        
        // Check if already clocked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->first();
        
        if ($existingAttendance && $existingAttendance->clock_in_time) {
            return response()->json(['error' => 'You have already clocked in today'], 403);
        }
        
        DB::beginTransaction();
        try {
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => today(),
                ],
                [
                    // 'clock_in_time' => now()->format('H:i:s'),
                    'clock_in_time' => now()->format('h:i:s A'),

                    'clock_in_latitude' => $request->latitude,
                    'clock_in_longitude' => $request->longitude,
                    'clock_in_distance' => $distance,
                    'clock_in_location_id' => $location->id,
                ]
            );
            
            $attendance->calculateStatus();
            
            // Start GPS tracking if enabled
            if ($user->gps_tracking_enabled) {
                $this->startGpsTracking($attendance);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Clock in successful',
                'time' => now()->format('H:i:s'),
                'distance' => round($distance) . ' meters'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to clock in: ' . $e->getMessage()], 500);
        }
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $user = auth()->user();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->first();
        
        if (!$attendance) {
            return response()->json(['error' => 'No active clock-in session found'], 403);
        }
        
        // Find nearest location for clock out
        $nearestLocation = null;
        $minDistance = PHP_INT_MAX;
        
        foreach ($user->locations as $location) {
            // $distance = $location->checkDistance($request->latitude, $request->longitude);
                        $distance = $location->checkDistance($request->latitude, $request->longitude, $location->latitude,$location->longitude);

            if ($distance <= $location->radius && $distance < $minDistance) {
                $minDistance = $distance;
                $nearestLocation = $location;
            }
        }
        
        DB::beginTransaction();
        try {
            $attendance->update([
                'clock_out_time' => now()->format('h:i:s A'),
                'clock_out_latitude' => $request->latitude,
                'clock_out_longitude' => $request->longitude,
                'clock_out_distance' => $minDistance != PHP_INT_MAX ? $minDistance : null,
                'clock_out_location_id' => $nearestLocation ? $nearestLocation->id : null,
            ]);
            
            $attendance->calculateTotalHours();
            
            // Stop GPS tracking
            if ($user->gps_tracking_enabled) {
                $this->stopGpsTracking($attendance);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Clock out successful',
                'time' => now()->format('H:i:s'),
                'total_hours' => $attendance->total_hours
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to clock out: ' . $e->getMessage()], 500);
        }
    }

    private function startGpsTracking($attendance)
    {
        // This would typically be handled by a background job
        // For now, we'll just mark that tracking should start
        session(['gps_tracking_active' => true, 'current_attendance_id' => $attendance->id]);
    }

    private function stopGpsTracking($attendance)
    {
        session()->forget(['gps_tracking_active', 'current_attendance_id']);
    }

    public function saveGpsLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);
        
        $user = auth()->user();
        
        if (!session('gps_tracking_active')) {
            return response()->json(['error' => 'GPS tracking not active'], 403);
        }
        
        if (!$user->gps_tracking_enabled) {
            return response()->json(['error' => 'GPS tracking not enabled for this user'], 403);
        }
        
        $attendanceId = session('current_attendance_id');
        
        GpsTracking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'tracked_at' => now(),
        ]);
        
        return response()->json(['success' => true]);
    }

    // public function history()
    // {
    //     $user = auth()->user();
    //     $attendances = Attendance::where('user_id', $user->id)
    //         ->latest()
    //         ->paginate(20);
        
    //     return view('employee.history', compact('attendances'));
    // }

    public function history(Request $request)
    {
        $user = auth()->user();
        
        $query = Attendance::where('user_id', $user->id);
        
        // Apply filters
        if ($request->from_date) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('date', '<=', $request->to_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->latest('date')
            ->paginate(20)
            ->withQueryString();

            // dd($attendances);
        
        return view('employee.history', compact('attendances'));
    }


   
    

    public function requestCorrection(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'requested_clock_in' => 'nullable|date_format:H:i',
            'requested_clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:10',
        ]);
        
        AttendanceCorrection::create([
            'user_id' => auth()->id(),
            'attendance_date' => $request->attendance_date,
            'requested_clock_in' => $request->requested_clock_in,
            'requested_clock_out' => $request->requested_clock_out,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);
        
        return redirect()->back()->with('success', 'Correction request submitted successfully');
    }

    public function getLocationStatus(Request $request)
    {
       
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $user = auth()->user();
        $allowedLocations = $user->locations()->where('is_active', true)->get();

        
        
        $isAllowed = false;
        $nearestLocation = null;
        $minDistance = PHP_INT_MAX;
        
        foreach ($allowedLocations as $location) {
            $distance = $location->checkDistance($request->latitude, $request->longitude, $location->latitude,$location->longitude);
            //  dd($distance);
            if ($distance <= $location->radius) {
               
                $isAllowed = true;
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestLocation = $location;
                }
            }
        }

 
    
        return response()->json([
            'allowed' => $isAllowed,
            'nearest_location' => $nearestLocation ? [
                'name' => $nearestLocation->name,
                'distance' => round($minDistance),
                'radius' => $nearestLocation->radius
            ] : null,
          'message' => $isAllowed ? 'You are within allowed geofence' : "You are outside allowed geofence"
        ]);
    }
}