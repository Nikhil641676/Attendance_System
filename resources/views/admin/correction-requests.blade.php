{{-- resources/views/admin/correction-requests.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance Correction Requests')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Attendance Correction Requests</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Requested Clock In</th>
                            <th>Requested Clock Out</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->attendance_date->format('d-m-Y') }}</td>
                            <td>{{ $request->requested_clock_in ?? '-' }}</td>
                            <td>{{ $request->requested_clock_out ?? '-' }}</td>
                            <td>{{ Str::limit($request->reason, 50) }}</td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($request->status == 'pending')
                                    <button class="btn btn-sm btn-success approve-btn" 
                                            data-id="{{ $request->id }}"
                                            data-name="{{ $request->user->name }}"
                                            data-date="{{ $request->attendance_date->format('d-m-Y') }}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-btn" 
                                            data-id="{{ $request->id }}"
                                            data-name="{{ $request->user->name }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                @else
                                    <span class="text-muted">Processed by: {{ $request->processedBy->name ?? 'N/A' }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No correction requests found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Correction Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this correction request for <strong id="approveName"></strong> on <strong id="approveDate"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" name="admin_remarks" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="status" value="approved">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Correction Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this correction request for <strong id="rejectName"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" name="admin_remarks" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="status" value="rejected">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var date = $(this).data('date');
        
        $('#approveForm').attr('action', '/admin/correction-requests/' + id);
        $('#approveName').text(name);
        $('#approveDate').text(date);
        $('#approveModal').modal('show');
    });
    
    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#rejectForm').attr('action', '/admin/correction-requests/' + id);
        $('#rejectName').text(name);
        $('#rejectModal').modal('show');
    });
});
</script>
@endpush
@endsection