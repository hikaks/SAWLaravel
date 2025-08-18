@extends('layouts.main')

@section('title', __('User Management') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('User Management'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('User Management') }}</h1>
        <p class="text-gray-600">{{ __('Manage system users and their permissions') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button variant="outline-secondary" size="sm" icon="fas fa-sync-alt" onclick="refreshTable()">{{ __('Refresh') }}</x-ui.button>
        <x-ui.button href="{{ route('users.create') }}" variant="primary" icon="fas fa-user-plus">{{ __('Add User') }}</x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Total Users') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-users"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['active'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Active Users') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-user-check"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['admins'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Administrators') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-user-shield"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-info-500 to-info-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['recent'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Recent Logins') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-clock"></i></div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h6 class="flex items-center gap-2 font-semibold text-gray-900">
            <i class="fas fa-table text-primary-500"></i>{{ __('User List') }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="usersTable" class="table dataTable">
                <thead>
                    <tr>
                        <th width="50">{{ __('No') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Last Login') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let usersTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeUsersTable();
});

function initializeUsersTable() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("users.index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { 
                data: 'role', 
                name: 'role',
                render: function(data) {
                    return data === 'admin' 
                        ? '<span class="badge badge-danger"><i class="fas fa-shield-alt mr-1"></i>Admin</span>'
                        : '<span class="badge badge-primary"><i class="fas fa-user mr-1"></i>User</span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    return data === 'active'
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-warning">Inactive</span>';
                }
            },
            { data: 'last_login_at', name: 'last_login_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        pageLength: 25,
        responsive: true
    });
}

function refreshTable() {
    usersTable.ajax.reload();
}
</script>
@endpush