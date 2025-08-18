@extends('layouts.main')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-users text-primary me-2"></i>User Management
                    </h1>
                    <p class="text-muted mt-1">Manage system users, roles, and permissions</p>
                </div>
                <div>
                    <x-ui.button 
                        href="{{ route('users.create') }}" 
                        variant="primary" 
                        icon="fas fa-plus">
                        Add New User
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['total_users'] }}</div>
                    <div class="stats-label">Total Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['active_users'] }}</div>
                    <div class="stats-label">Active Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['admin_users'] }}</div>
                    <div class="stats-label">Admin Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['verified_users'] }}</div>
                    <div class="stats-label">Verified Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['unverified_users'] ?? 0 }}</div>
                    <div class="stats-label">Unverified Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                <div class="stats-content">
                    <div class="stats-number">{{ $stats['verification_rate'] ?? 0 }}%</div>
                    <div class="stats-label">Verification Rate</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center">
                    <h5 class="card-title mb-2">
                        <i class="fas fa-envelope me-2"></i>Email Verification Status
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-check-circle text-success me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <div class="h4 mb-0">{{ $stats['verified_users'] }}</div>
                                    <small>Verified</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-clock text-warning me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <div class="h4 mb-0">{{ $stats['unverified_users'] ?? 0 }}</div>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Users List
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-1"></i>Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('activate')">
                        <i class="fas fa-check text-success me-2"></i>Activate Selected
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('deactivate')">
                        <i class="fas fa-ban text-warning me-2"></i>Deactivate Selected
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-2"></i>Delete Selected
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th width="3%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="5%">#</th>
                            <th width="20%">Name</th>
                            <th width="20%">Email</th>
                            <th width="10%">Role</th>
                            <th width="10%">Status</th>
                            <th width="10%">Verified</th>
                            <th width="12%">Last Activity</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables Ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Enhanced styling for user management */
.stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    color: white;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stats-content {
    position: relative;
    z-index: 2;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.stats-label {
    font-size: 0.875rem;
    font-weight: 500;
    opacity: 0.9;
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.5rem;
    opacity: 0.3;
    z-index: 1;
}

/* Table enhancements */
.table th {
    border-top: none;
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: scale(1.01);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Badge enhancements */
.badge {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
}

/* Button group enhancements */
.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.btn-group .btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

/* Card enhancements */
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.25rem 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn-sm {
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
let usersTable;

$(document).ready(function() {
    // Initialize DataTable
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('users.index') }}",
            type: 'GET'
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<input type="checkbox" class="form-check-input user-checkbox" value="' + row.id + '">';
                }
            },
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'role_badge', name: 'role', orderable: false},
            {data: 'status_badge', name: 'is_active', orderable: false},
            {data: 'verified_badge', name: 'email_verified_at', orderable: false},
            {data: 'last_login', name: 'updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No users found",
            zeroRecords: "No matching users found"
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.user-checkbox').prop('checked', this.checked);
    });

    // Update select all checkbox when individual checkboxes change
    $(document).on('change', '.user-checkbox', function() {
        const totalCheckboxes = $('.user-checkbox').length;
        const checkedCheckboxes = $('.user-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
});

// Delete user function
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Delete User?',
        html: `
            <div class="text-start">
                <p class="mb-3">Are you sure you want to delete user <strong>${userName}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash me-1"></i>Delete User',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'animated fadeInDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting User...',
                html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Send delete request
            $.ajax({
                url: `/users/${userId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    usersTable.ajax.reload();
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: response.message || 'An error occurred while deleting the user.'
                    });
                }
            });
        }
    });
}

// Bulk action function
function bulkAction(action) {
    const selectedUsers = $('.user-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (selectedUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Users Selected',
            text: 'Please select users to perform bulk action.'
        });
        return;
    }

    const actionText = action.charAt(0).toUpperCase() + action.slice(1);

    Swal.fire({
        title: `${actionText} Selected Users?`,
        html: `
            <div class="text-start">
                <p class="mb-3">You are about to <strong>${action}</strong> ${selectedUsers.length} user(s).</p>
                ${action === 'delete' ? '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><strong>Warning:</strong> This action cannot be undone.</div>' : ''}
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `<i class="fas fa-check me-1"></i>${actionText}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#0d6efd',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: `Processing ${actionText}...`,
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Send bulk action request
            $.ajax({
                url: "{{ route('users.bulk-action') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    action: action,
                    user_ids: selectedUsers
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    usersTable.ajax.reload();
                    $('#selectAll').prop('checked', false);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Action Failed',
                        text: response.message || 'An error occurred while performing the bulk action.'
                    });
                }
            });
        }
    });
}
</script>
@endpush


