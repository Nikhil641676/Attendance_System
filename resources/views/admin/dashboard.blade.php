{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Admin Dashboard</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Employees</h6>
                            <h2 class="mb-0">{{ $totalEmployees }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Present Today</h6>
                            <h2 class="mb-0">{{ $presentToday }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Branch</h6>
                            <h2 class="mb-0">{{ $totalLocations }}</h2>
                        </div>
                        <i class="fas fa-map-marker-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pending Corrections</h6>
                            <h2 class="mb-0">{{ $pendingCorrections }}</h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Attendances -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Today's Recent Attendances</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->user->name }}</td>
                                        <td>{{ $attendance->clock_in_time ?? '-' }}</td>
                                        <td>{{ $attendance->clock_out_time ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $attendance->clockInLocation->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No attendance records for today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.employees') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Add New Employee
                        </a>
                        <a href="{{ route('admin.locations') }}" class="btn btn-info">
                            <i class="fas fa-map-marker-alt"></i> Add New Location
                        </a>
                        <a href="{{ route('admin.attendance.report') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Generate Report
                        </a>
                        <a href="{{ route('admin.corrections') }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> View Corrections
                            @if($pendingCorrections > 0)
                                <span class="badge bg-danger">{{ $pendingCorrections }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection