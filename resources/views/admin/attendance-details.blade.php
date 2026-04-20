{{-- resources/views/admin/attendance-details.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered">
            <tr>
                <th>Employee Name:</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>Date:</th>
                <td>{{ $attendance->date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Clock In Time:</th>
                <td>{{ $attendance->clock_in_time ?? 'Not clocked in' }}</td>
            </tr>
            <tr>
                <th>Clock Out Time:</th>
                <td>{{ $attendance->clock_out_time ?? 'Not clocked out' }}</td>
            </tr>
            <tr>
                <th>Total Hours:</th>
                <td>{{ $attendance->total_hours ?? '0' }} hours</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }}">
                        {{ ucfirst($attendance->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Clock In Location:</th>
                <td>{{ $attendance->clockInLocation->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Clock Out Location:</th>
                <td>{{ $attendance->clockOutLocation->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Distance from Location (In):</th>
                <td>{{ round($attendance->clock_in_distance ?? 0) }} meters</td>
            </tr>
            <tr>
                <th>Distance from Location (Out):</th>
                <td>{{ round($attendance->clock_out_distance ?? 0) }} meters</td>
            </tr>
            @if($attendance->remarks)
            <tr>
                <th>Remarks:</th>
                <td>{{ $attendance->remarks }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>