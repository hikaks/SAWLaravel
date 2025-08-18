@extends('layouts.main')

@section('title', __('Employee Management') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Employee Management'))

@section('content')
<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-gray-600">{{ __('Manage and organize your employee data efficiently') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            variant="outline-secondary" 
            size="sm" 
            icon="fas fa-sync-alt"
            onclick="refreshTable()" 
            title="{{ __('Refresh Data') }}"
            id="refreshBtn">
            {{ __('Refresh') }}
        </x-ui.button>
        
        <div class="dropdown" x-data="{ open: false }">
            <x-ui.button 
                variant="success" 
                size="sm" 
                icon="fas fa-file-import"
                @click="open = !open"
                class="dropdown-toggle">
                {{ __('Import') }}
            </x-ui.button>
            <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                <a class="dropdown-item dropdown-item-icon" href="{{ route('employees.import-template') }}">
                    <i class="fas fa-download text-success-600"></i>
                    {{ __('Download Template') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item dropdown-item-icon" href="#" onclick="showImportModal()">
                    <i class="fas fa-upload text-primary-600"></i>
                    {{ __('Upload Data') }}
                </a>
            </div>
        </div>
        
        <x-ui.button 
            href="{{ route('employees.create') }}" 
            variant="primary" 
            icon="fas fa-user-plus">
            {{ __('Add Employee') }}
        </x-ui.button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number" id="totalEmployees">-</div>
            <div class="stats-label">{{ __('Total Employees') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-users"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number" id="evaluatedEmployees">-</div>
            <div class="stats-label">{{ __('Evaluated') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number" id="totalDepartments">-</div>
            <div class="stats-label">{{ __('Departments') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-building"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600">
        <div class="stats-content">
            <div class="stats-number" id="activeEmployees">-</div>
            <div class="stats-label">{{ __('Active') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-user-check"></i>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card mb-6">
    <div class="card-header">
        <h6 class="flex items-center gap-2 font-semibold text-gray-900">
            <i class="fas fa-filter text-primary-500"></i>
            {{ __('Filters & Search') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="form-group">
                <label class="form-label">{{ __('Department') }}</label>
                <select id="departmentFilter" class="form-select">
                    <option value="">{{ __('All Departments') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Position') }}</label>
                <select id="positionFilter" class="form-select">
                    <option value="">{{ __('All Positions') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Evaluation Status') }}</label>
                <select id="evaluationFilter" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="evaluated">{{ __('Evaluated') }}</option>
                    <option value="not_evaluated">{{ __('Not Evaluated') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Status') }}</label>
                <select id="statusFilter" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-table text-primary-500"></i>
                {{ __('Employee List') }}
            </h6>
            <div class="flex items-center gap-2">
                <div class="dropdown" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-outline-secondary btn-sm dropdown-toggle">
                        <i class="fas fa-download mr-2"></i>
                        {{ __('Export') }}
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('employees.export-excel') }}">
                            <i class="fas fa-file-excel text-success-600"></i>
                            {{ __('Excel Format') }}
                        </a>
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('employees.export-pdf') }}">
                            <i class="fas fa-file-pdf text-danger-600"></i>
                            {{ __('PDF Format') }}
                        </a>
                    </div>
                </div>
                <button onclick="toggleBulkActions()" class="btn btn-outline-primary btn-sm" id="bulkActionToggle" style="display: none;">
                    <i class="fas fa-tasks mr-2"></i>
                    {{ __('Bulk Actions') }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="employeesTable" class="table dataTable">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th width="50">{{ __('No') }}</th>
                        <th>{{ __('Employee Code') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Evaluation Status') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div x-data="{ showImportModal: false }" x-show="showImportModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Employees') }}</h5>
                <button @click="showImportModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">{{ __('Select Excel File') }}</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <div class="text-sm text-gray-500 mt-2">
                            {{ __('Accepted formats: .xlsx, .xls, .csv') }}
                        </div>
                    </div>
                    <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-info-600"></i>
                            <div>
                                <h6 class="font-semibold text-info-800">{{ __('Import Instructions') }}</h6>
                                <ul class="text-sm text-info-700 mt-2 space-y-1">
                                    <li>• {{ __('Download the template first') }}</li>
                                    <li>• {{ __('Fill in the employee data') }}</li>
                                    <li>• {{ __('Make sure all required fields are filled') }}</li>
                                    <li>• {{ __('Employee codes must be unique') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showImportModal = false" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="fas fa-upload mr-2"></i>
                        {{ __('Import Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div x-data="{ showBulkModal: false }" x-show="showBulkModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Actions') }}</h5>
                <button @click="showBulkModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <p class="text-gray-700">
                        {{ __('Selected employees: ') }}<span id="selectedCount" class="font-semibold">0</span>
                    </p>
                </div>
                <div class="space-y-3">
                    <button onclick="bulkAction('activate')" class="btn btn-success w-full justify-start">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ __('Activate Selected') }}
                    </button>
                    <button onclick="bulkAction('deactivate')" class="btn btn-warning w-full justify-start">
                        <i class="fas fa-pause-circle mr-2"></i>
                        {{ __('Deactivate Selected') }}
                    </button>
                    <button onclick="bulkAction('delete')" class="btn btn-danger w-full justify-start">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" @click="showBulkModal = false" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let employeesTable;
let selectedEmployees = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeEmployeesPage();
});

function initializeEmployeesPage() {
    initializeDataTable();
    loadFilterOptions();
    loadStatistics();
    bindEvents();
}

function initializeDataTable() {
    employeesTable = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("employees.index") }}',
            data: function (d) {
                d.department = $('#departmentFilter').val();
                d.position = $('#positionFilter').val();
                d.evaluation_status = $('#evaluationFilter').val();
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            {
                data: 'checkbox',
                name: 'checkbox',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'employee_code',
                name: 'employee_code',
                render: function(data) {
                    return `<code class="badge badge-secondary">${data}</code>`;
                }
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    return `<div class="font-medium">${data}</div>`;
                }
            },
            {
                data: 'department',
                name: 'department'
            },
            {
                data: 'position',
                name: 'position'
            },
            {
                data: 'email',
                name: 'email',
                render: function(data) {
                    return `<a href="mailto:${data}" class="text-primary-600 hover:text-primary-700">${data}</a>`;
                }
            },
            {
                data: 'evaluation_status',
                name: 'evaluation_status',
                render: function(data) {
                    if (data === 'evaluated') {
                        return '<span class="badge badge-success">{{ __("Evaluated") }}</span>';
                    } else {
                        return '<span class="badge badge-warning">{{ __("Not Evaluated") }}</span>';
                    }
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    if (data === 'active') {
                        return '<span class="badge badge-success">{{ __("Active") }}</span>';
                    } else {
                        return '<span class="badge badge-danger">{{ __("Inactive") }}</span>';
                    }
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="flex items-center gap-2"><div class="loading-spinner w-5 h-5"></div>{{ __("Loading...") }}</div>',
            search: '{{ __("Search:") }}',
            lengthMenu: '{{ __("Show _MENU_ entries") }}',
            info: '{{ __("Showing _START_ to _END_ of _TOTAL_ entries") }}',
            infoEmpty: '{{ __("No entries found") }}',
            infoFiltered: '{{ __("(filtered from _MAX_ total entries)") }}',
            paginate: {
                first: '{{ __("First") }}',
                last: '{{ __("Last") }}',
                next: '{{ __("Next") }}',
                previous: '{{ __("Previous") }}'
            }
        }
    });
}

function bindEvents() {
    // Filter changes
    $('#departmentFilter, #positionFilter, #evaluationFilter, #statusFilter').change(function() {
        employeesTable.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').change(function() {
        const isChecked = $(this).is(':checked');
        $('.employee-checkbox').prop('checked', isChecked);
        updateSelectedEmployees();
    });

    // Individual checkboxes
    $(document).on('change', '.employee-checkbox', function() {
        updateSelectedEmployees();
    });

    // Import form
    $('#importForm').submit(function(e) {
        e.preventDefault();
        handleImport();
    });
}

function loadFilterOptions() {
    // Load departments
    fetch('{{ route("api.departments") }}')
        .then(response => response.json())
        .then(data => {
            const departmentSelect = $('#departmentFilter');
            data.forEach(dept => {
                departmentSelect.append(`<option value="${dept}">${dept}</option>`);
            });
        });

    // Load positions (you can implement this endpoint)
    // Similar implementation for positions
}

function loadStatistics() {
    fetch('{{ route("employees.index") }}?stats=1')
        .then(response => response.json())
        .then(data => {
            $('#totalEmployees').text(data.total || 0);
            $('#evaluatedEmployees').text(data.evaluated || 0);
            $('#totalDepartments').text(data.departments || 0);
            $('#activeEmployees').text(data.active || 0);
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

function refreshTable() {
    const btn = $('#refreshBtn');
    btn.prop('disabled', true);
    btn.find('i').addClass('fa-spin');
    
    employeesTable.ajax.reload(() => {
        btn.prop('disabled', false);
        btn.find('i').removeClass('fa-spin');
        loadStatistics();
    });
}

function updateSelectedEmployees() {
    selectedEmployees = [];
    $('.employee-checkbox:checked').each(function() {
        selectedEmployees.push($(this).val());
    });
    
    $('#selectedCount').text(selectedEmployees.length);
    $('#bulkActionToggle').toggle(selectedEmployees.length > 0);
}

function showImportModal() {
    Alpine.store('modals', { showImportModal: true });
}

function toggleBulkActions() {
    Alpine.store('modals', { showBulkModal: true });
}

function handleImport() {
    const formData = new FormData($('#importForm')[0]);
    const btn = $('#importBtn');
    
    btn.prop('disabled', true);
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("Importing...") }}');
    
    fetch('{{ route("employees.import") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '{{ __("Success") }}',
                text: data.message
            });
            Alpine.store('modals', { showImportModal: false });
            employeesTable.ajax.reload();
            loadStatistics();
        } else {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Import error:', error);
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: '{{ __("An error occurred during import") }}'
        });
    })
    .finally(() => {
        btn.prop('disabled', false);
        btn.html('<i class="fas fa-upload mr-2"></i>{{ __("Import Data") }}');
    });
}

function bulkAction(action) {
    if (selectedEmployees.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Warning") }}',
            text: '{{ __("Please select at least one employee") }}'
        });
        return;
    }
    
    let title, text, confirmText;
    
    switch(action) {
        case 'activate':
            title = '{{ __("Activate Employees") }}';
            text = '{{ __("Are you sure you want to activate the selected employees?") }}';
            confirmText = '{{ __("Activate") }}';
            break;
        case 'deactivate':
            title = '{{ __("Deactivate Employees") }}';
            text = '{{ __("Are you sure you want to deactivate the selected employees?") }}';
            confirmText = '{{ __("Deactivate") }}';
            break;
        case 'delete':
            title = '{{ __("Delete Employees") }}';
            text = '{{ __("Are you sure you want to delete the selected employees? This action cannot be undone.") }}';
            confirmText = '{{ __("Delete") }}';
            break;
    }
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction(action);
        }
    });
}

function performBulkAction(action) {
    fetch('{{ route("employees.bulk-action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({
            action: action,
            employees: selectedEmployees
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '{{ __("Success") }}',
                text: data.message
            });
            Alpine.store('modals', { showBulkModal: false });
            employeesTable.ajax.reload();
            loadStatistics();
            selectedEmployees = [];
            updateSelectedEmployees();
        } else {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Bulk action error:', error);
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: '{{ __("An error occurred") }}'
        });
    });
}

// Initialize Alpine.js stores
document.addEventListener('alpine:init', () => {
    Alpine.store('modals', {
        showImportModal: false,
        showBulkModal: false
    });
});
</script>
@endpush