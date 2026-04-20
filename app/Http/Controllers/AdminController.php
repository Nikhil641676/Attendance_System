<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin|admin']);
    }

    public function dashboard()
    {
        $totalEmployees = User::role('employee')->count();
        $presentToday = Attendance::where('date', today())->where('status', '!=', 'absent')->count();
        $totalLocations = Location::count();
        $pendingCorrections = AttendanceCorrection::where('status', 'pending')->count();
        
        $recentAttendances = Attendance::with('user')
            ->where('date', today())
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact('totalEmployees', 'presentToday', 'totalLocations', 'pendingCorrections', 'recentAttendances'));
    }

    public function employees()
    {
        $employees = User::role('employee')->with('manager', 'locations')->get();
        $managers = User::role('manager')->get();
        $roles = Role::whereIn('name', ['employee', 'manager'])->get();
        $locations = Location::all();
        
        return view('admin.employees', compact('employees', 'managers', 'roles', 'locations'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'role' => 'required|exists:roles,name',
            'manager_id' => 'nullable|exists:users,id',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'manager_id' => $request->manager_id,
            'gps_tracking_enabled' => $request->has('gps_tracking_enabled'),
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        if ($request->has('locations')) {
            $user->locations()->sync($request->locations);
        }

        return redirect()->back()->with('success', 'Employee created successfully');
    }

    public function updateEmployee(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|exists:roles,name',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'manager_id' => $request->manager_id,
            'gps_tracking_enabled' => $request->has('gps_tracking_enabled'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        if ($request->has('locations')) {
            $user->locations()->sync($request->locations);
        }

        return redirect()->back()->with('success', 'Employee updated successfully');
    }

    public function deleteEmployee(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'Employee deleted successfully');
    }

    public function locations()
    {
        $locations = Location::withCount('employees')->get();
       // dd($locations);
        return view('admin.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:10',
            'address' => 'nullable|string',
        ]);

        Location::create($request->all());

        return redirect()->back()->with('success', 'Location created successfully');
    }

    public function updateLocation(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:10',
            'address' => 'nullable|string',
        ]);

        $location->update($request->all());

        return redirect()->back()->with('success', 'Location updated successfully');
    }

    public function deleteLocation(Location $location)
    {
        $location->delete();
        return redirect()->back()->with('success', 'Location deleted successfully');
    }

    public function attendanceReport(Request $request)
    {
        $query = Attendance::with('user');
        
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->latest('date')->paginate(20);
        $employees = User::role('employee')->get();
        
        return view('admin.attendance-report', compact('attendances', 'employees'));
    }

    public function exportAttendance(Request $request)
    {
        $query = Attendance::with('user');
        
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        $attendances = $query->get();
        
        $csvData = [];
        $csvData[] = ['Employee Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours', 'Status'];
        
        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->user->name,
                $attendance->date,
                $attendance->clock_in_time,
                $attendance->clock_out_time,
                $attendance->total_hours,
                $attendance->status
            ];
        }
        
        $filename = 'attendance_report_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(function() use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function gpsReports()
    {
        $users = User::where('gps_tracking_enabled', true)->get();


        return view('admin.gps-reports', compact('users'));
    }

    public function getUserGpsData(User $user, Request $request)
    {
        
        $date = $request->get('date', today());
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();
        
        if (!$attendance) {
            return response()->json(['error' => 'No attendance found for this date'], 404);
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
                'time' => $attendance->clock_in_time,
                'location' => $attendance->clockInLocation ? $attendance->clockInLocation->name : null
            ],
            'clock_out' => [
                'lat' => $attendance->clock_out_latitude,
                'lng' => $attendance->clock_out_longitude,
                'time' => $attendance->clock_out_time,
                'location' => $attendance->clockOutLocation ? $attendance->clockOutLocation->name : null
            ],
            'route' => $route
        ]);
    }

    public function correctionRequests()
    {
        $requests = AttendanceCorrection::with('user')->latest()->paginate(20);
        return view('admin.correction-requests', compact('requests'));
    }

    public function processCorrection(Request $request, AttendanceCorrection $correction)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string'
        ]);
        
        $correction->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'processed_by' => auth()->id(),
        ]);
        
        if ($request->status === 'approved') {
            $attendance = Attendance::firstOrCreate([
                'user_id' => $correction->user_id,
                'date' => $correction->attendance_date,
            ]);
            
            if ($correction->requested_clock_in) {
                $attendance->clock_in_time = $correction->requested_clock_in;
            }
            
            if ($correction->requested_clock_out) {
                $attendance->clock_out_time = $correction->requested_clock_out;
            }
            
            $attendance->is_corrected = true;
            $attendance->remarks = 'Corrected from request #' . $correction->id;
            $attendance->save();
            $attendance->calculateTotalHours();
            $attendance->calculateStatus();
        }
        
        return redirect()->back()->with('success', 'Correction request ' . $request->status);
    }
}