{{-- resources/views/admin/employees.blade.php --}}
@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Employee Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-plus"></i> Add Employee
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="employeesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Manager</th>
                            <th>GPS Tracking</th>
                            <th>Locations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $employee->roles->first()->name == 'employee' ? 'info' : 'primary' }}">
                                    {{ ucfirst($employee->roles->first()->name ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $employee->manager->name ?? 'N/A' }}</td>
                            <td>
                                @if($employee->gps_tracking_enabled)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>
                                @foreach($employee->locations as $location)
                                    <span class="badge bg-info">{{ $location->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-employee" 
                                        data-id="{{ $employee->id }}"
                                        data-name="{{ $employee->name }}"
                                        data-email="{{ $employee->email }}"
                                        data-phone="{{ $employee->phone }}"
                                        data-address="{{ $employee->address }}"
                                        data-role="{{ $employee->roles->first()->name ?? '' }}"
                                        data-manager="{{ $employee->manager_id }}"
                                        data-gps="{{ $employee->gps_tracking_enabled }}"
                                        data-locations="{{ $employee->locations->pluck('id')->join(',') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-employee" 
                                        data-id="{{ $employee->id }}"
                                        data-name="{{ $employee->name }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.employees.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name<span class="ismandatory">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Enter Full Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="ismandatory">*</span></label>
                            <input type="email" class="form-control" name="email" required placeholder="Enter Email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone <span class="ismandatory">*</span></label>
                            <input type="text" class="form-control" name="phone" required placeholder="Enter Phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role <span class="ismandatory">*</span></label>
                            <select class="form-control" name="role" required>
                                <option value="">Select Role</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_id" class="form-label">Manager</label>
                            <select class="form-control" name="manager_id">
                                <option value="">Select Manager</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="ismandatory">*</span></label>
                            <input type="password" class="form-control" name="password" required placeholder="Enter Password">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="gps_tracking_enabled" value="1">
                                <label class="form-check-label">Enable GPS Tracking</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Assign Locations</label>
                            <select class="form-control" name="locations[]" multiple>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple locations</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editEmployeeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Full Name <spna class="ismandatory">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email <spna class="ismandatory">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone <spna class="ismandatory">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role <spna class="ismandatory">*</span></label>
                            <select class="form-control" id="edit_role" name="role" required>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_manager_id" class="form-label">Manager</label>
                            <select class="form-control" id="edit_manager_id" name="manager_id">
                                <option value="">Select Manager</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">New Password (Optional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_gps_tracking" name="gps_tracking_enabled" value="1">
                                <label class="form-check-label">Enable GPS Tracking</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Assign Locations</label>
                            <select class="form-control" id="edit_locations" name="locations[]" multiple>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple locations</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteEmployeeForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Edit Employee
    $('.edit-employee').click(function() {
        var id = $(this).data('id');
        $('#editEmployeeForm').attr('action', '/admin/employees/' + id);
        $('#edit_name').val($(this).data('name'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_phone').val($(this).data('phone'));
        $('#edit_address').val($(this).data('address'));
        $('#edit_role').val($(this).data('role'));
        $('#edit_manager_id').val($(this).data('manager'));
        $('#edit_gps_tracking').prop('checked', $(this).data('gps') == 1);
        
        var locations = $(this).data('locations').toString().split(',');
        $('#edit_locations').val(locations);
        
        $('#editEmployeeModal').modal('show');
    });
    
    // Delete Employee
    $('.delete-employee').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#deleteEmployeeForm').attr('action', '/admin/employees/' + id);
        $('#deleteEmployeeName').text(name);
        $('#deleteEmployeeModal').modal('show');
    });
});
</script>
@endpush
@endsection