{{-- resources/views/employee/history.blade.php --}}
@extends('layouts.app')

@section('title', 'My Attendance History')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-history text-primary me-2"></i>
                My Attendance History
            </h2>
            <p class="text-muted">View your complete attendance records</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Present</h6>
                            <h2 class="mb-0">{{ $attendances->where('status', 'present')->count() }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">On Time</h6>
                            <h2 class="mb-0">{{ $attendances->where('status', 'on_time')->count() }}</h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Late Days</h6>
                            <h2 class="mb-0">{{ $attendances->where('status', 'late')->count() }}</h2>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Hours</h6>
                            <h2 class="mb-0">{{ number_format($attendances->sum('total_hours'), 1) }}</h2>
                        </div>
                        <i class="fas fa-hourglass-end fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employee.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i> Filter
                        </button>
                        <a href="{{ route('employee.history') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Attendance Records
                </h5>
                <div>
                    <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i> Export
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="attendanceTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Remarks</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</strong>
                                    @if($attendance->is_corrected)
                                        <span class="badge bg-warning ms-1" title="Corrected by Admin">
                                            <i class="fas fa-edit"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('l') }}</td>
                                <td>
                                    @if($attendance->clock_in_time)
                                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('h:i A') }}
                                        <br>
                                        <small class="text-muted">
                                            @if($attendance->clock_in_location_id)
                                                <i class="fas fa-building"></i> Office
                                            @elseif($attendance->clock_in_latitude)
                                                <i class="fas fa-map-marker-alt"></i> Remote
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">Not clocked in</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->clock_out_time)
                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('h:i A') }}
                                    @else
                                        <span class="text-muted">Not clocked out</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->total_hours)
                                        <span class="badge bg-info">
                                            <i class="fas fa-hourglass-half me-1"></i>
                                            {{ number_format($attendance->total_hours, 2) }} hrs
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'half_day' => 'info',
                                            'absent' => 'danger'
                                        ];
                                        $statusIcons = [
                                            'present' => 'fa-check-circle',
                                            'late' => 'fa-hourglass-half',
                                            'half_day' => 'fa-sun',
                                            'absent' => 'fa-times-circle'
                                        ];
                                        $color = $statusColors[$attendance->status] ?? 'secondary';
                                        $icon = $statusIcons[$attendance->status] ?? 'fa-question-circle';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        <i class="fas {{ $icon }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $attendance->remarks ?? '-' }}
                                </td>
                                
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $attendances->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No attendance records found</h5>
                    <p class="text-muted">Your attendance history will appear here once you start marking attendance.</p>
                   
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Attendance Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .badge {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    @media print {
        .btn, .pagination, .card-header .btn, form {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Function to view detailed information
// function viewDetails(attendanceId) {
//     fetch(`attendance/${attendanceId}/details`)
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 displayDetailsModal(data);
//             } else {
//                 alert('Details not available');
//             }
//         })
//         .catch(error => {
//             console.error('Error:', error);
//             alert('Error loading details');
//         });
// }






function exportToExcel() {
    const table = document.getElementById('attendanceTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Attendance History" });
    XLSX.writeFile(wb, `attendance_history_${new Date().toISOString().split('T')[0]}.xlsx`);
}
</script>

<!-- SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
@endpush
@endsection