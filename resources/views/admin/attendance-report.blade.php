{{-- resources/views/admin/attendance-report.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Attendance Report</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-excel"></i> Export to CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendance.report') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ request('date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Employee</label>
                    <select class="form-control" name="user_id">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="">All</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d-m-Y') }}</td>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->clock_in_time ?? '-' }}</td>
                            <td>{{ $attendance->clock_out_time ?? '-' }}</td>
                            <td>{{ $attendance->total_hours ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>{{ $attendance->clockInLocation->name ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-details" data-id="{{ $attendance->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                             </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No attendance records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $attendances->links() }}
        </div>
    </div>
</div>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attendanceDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-details').click(function() {
        var id = $(this).data('id');
        // Fetch attendance details via AJAX
        $.ajax({
            url: '/admin/attendance/' + id,
            method: 'GET',
            success: function(response) {
                $('#attendanceDetails').html(response);
                $('#attendanceModal').modal('show');
            }
        });
    });
    
    $('#exportBtn').click(function() {
        var params = new URLSearchParams(window.location.search);
        window.location.href = '/admin/export-attendance?' + params.toString();
    });
});
</script>
@endpush
@endsection