@extends('layouts.main')

@section('title', __('Employee Management') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Employee Management'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">{{ __('Manage and organize your employee data efficiently') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            variant="outline-secondary" 
            size="sm" 
            icon="fas fa-sync-alt"
            onclick="refreshTable()" 
            data-bs-toggle="tooltip" 
            title="{{ __('Refresh Data') }}"
            id="refreshBtn">
            {{ __('Refresh') }}
        </x-ui.button>
        
        <div class="relative inline-block">
            <x-ui.button 
                variant="success" 
                size="sm" 
                icon="fas fa-file-import"
                data-bs-toggle="dropdown" 
                aria-expanded="false"
                class="dropdown-toggle">
                {{ __('Import') }}
            </x-ui.button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item flex items-center" href="{{ route('employees.import-template') }}">
                        <i class="fas fa-download mr-2 text-success-600"></i>
                        {{ __('Download Template') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item flex items-center" href="#" onclick="showImportModal()">
                        <i class="fas fa-upload mr-2 text-primary-600"></i>
                        {{ __('Upload Data') }}
                    </a>
                </li>
            </ul>
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
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
            <div class="stats-content">
                <div class="stats-number" id="totalEmployees">-</div>
                <div class="stats-label">{{ __('Total Employees') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stats-content">
                <div class="stats-number" id="evaluatedEmployees">-</div>
                <div class="stats-label">{{ __('Evaluated') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stats-content">
                <div class="stats-number" id="totalDepartments">-</div>
                <div class="stats-label">{{ __('Departments') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-building"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
            <div class="stats-content">
                <div class="stats-number" id="activeEmployees">-</div>
                <div class="stats-label">{{ __('Active') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold d-flex align-items-center">
                <i class="fas fa-list me-2 text-primary"></i>
                {{ __('Employee Directory') }}
            </h6>
            <div class="flex gap-2">
                <x-ui.button 
                    variant="outline-primary" 
                    size="sm" 
                    icon="fas fa-download"
                    onclick="exportData()" 
                    data-bs-toggle="tooltip" 
                    title="{{ __('Export Data') }}"
                    id="exportBtn">
                </x-ui.button>
                <x-ui.button 
                    variant="outline-warning" 
                    size="sm" 
                    icon="fas fa-undo"
                    onclick="showRestoreModal()" 
                    data-bs-toggle="tooltip" 
                    title="{{ __('Restore Deleted Employees') }}">
                </x-ui.button>
                <x-ui.button 
                    variant="outline-secondary" 
                    size="sm" 
                    icon="fas fa-filter"
                    onclick="toggleFilters()" 
                    data-bs-toggle="tooltip" 
                    title="{{ __('Toggle Filters') }}">
                </x-ui.button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="collapse" id="filtersCollapse">
            <div class="alert alert-info p-3 mb-4">
                <h6 class="mb-3 fw-semibold">
                    <i class="fas fa-filter me-2"></i>{{ __('Advanced Filters') }}
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">{{ __('Department') }}</label>
                        <select class="form-select" id="departmentFilter">
                            <option value="">{{ __('All Departments') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">{{ __('Position') }}</label>
                        <select class="form-select" id="positionFilter">
                            <option value="">{{ __('All Positions') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">{{ __('Evaluation Status') }}</label>
                        <select class="form-select" id="evaluationFilter">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="evaluated">{{ __('Evaluated') }}</option>
                            <option value="not_evaluated">{{ __('Not Evaluated') }}</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>{{ __('Clear Filters') }}
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="applyFilters()">
                            <i class="fas fa-search me-1"></i>{{ __('Apply Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="employeesTable">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th width="10%">{{ __('Code') }}</th>
                        <th width="20%">{{ __('Employee') }}</th>
                        <th width="15%">{{ __('Position') }}</th>
                        <th width="12%">{{ __('Department') }}</th>
                        <th width="15%">{{ __('Contact') }}</th>
                        <th width="10%">{{ __('Last Evaluation') }}</th>
                        <th width="8%">{{ __('Ranking') }}</th>
                        <th class="text-center" width="5%">{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Restore Deleted Employees Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">
                    <i class="fas fa-undo me-2 text-warning"></i>
                    {{ __('Restore Deleted Employees') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('This will show all employees that have been deleted. You can restore them one by one or restore all at once.') }}
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">{{ __('Deleted Employees') }}</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-warning btn-sm" onclick="restoreAllEmployees()" id="restoreAllBtn" disabled>
                            <i class="fas fa-undo me-1"></i>
                            {{ __('Restore All') }}
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshRestoreList()">
                            <i class="fas fa-sync-alt me-1"></i>
                            {{ __('Refresh') }}
                        </button>
                    </div>
                </div>

                <div id="deletedEmployeesCards" class="row g-3">
                    <!-- Deleted employees will be displayed as cards here -->
                </div>

                <div id="noDeletedEmployees" class="text-center py-4" style="display: none;">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6 class="text-success">{{ __('No Deleted Employees') }}</h6>
                    <p class="text-muted">{{ __('All employees are currently active.') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-file-import me-2"></i>{{ __('Import Employees') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_file" class="form-label">{{ __('Select Excel/CSV File') }}</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" 
                               accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            {{ __('Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Maximum file size: 10MB') }}
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('Important:') }}</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('Download the template first to see the required format') }}</li>
                            <li>{{ __('Employee codes must be unique') }}</li>
                            <li>{{ __('Email addresses must be valid and unique') }}</li>
                            <li>{{ __('Existing employees will be updated if found') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-2"></i>{{ __('Import Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let employeesTable;
let currentEmployeeId = null;

$(document).ready(function() {
    // Initialize components
    initializeDataTable();
    loadFilterOptions();
    loadStatistics();
    initializeTooltips();

    // Filter change events
    $('#departmentFilter, #positionFilter, #evaluationFilter').change(function() {
        employeesTable.ajax.reload();
    });
});

function initializeDataTable() {
    employeesTable = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('employees.index') }}",
            data: function (d) {
                d.department = $('#departmentFilter').val();
                d.position = $('#positionFilter').val();
                d.evaluation_status = $('#evaluationFilter').val();
            }
        },
        columns: [
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
                    return `<code class="badge bg-light text-dark">${data}</code>`;
                }
            },
                        {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const avatar = row.name.substring(0, 2).toUpperCase();
                    return `
                        <div class="d-flex align-items-center">
                            <div class="employee-avatar bg-primary me-3">${avatar}</div>
                            <div>
                                <div class="fw-semibold">
                                    <a href="/employees/${row.id}" class="text-decoration-none text-primary fw-bold" data-bs-toggle="tooltip" title="{{ __('View Employee Details') }}">
                                        ${data}
                                    </a>
                                </div>
                                <small class="text-muted">${row.employee_code}</small>
                            </div>
                        </div>
                    `;
                }
            },
            {data: 'position', name: 'position'},
            {
                data: 'department',
                name: 'department',
                render: function(data) {
                    return `<span class="badge bg-light text-dark">${data}</span>`;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data) {
                    return `<a href="mailto:${data}" class="text-decoration-none">${data}</a>`;
                }
            },
            {
                data: 'latest_evaluation',
                name: 'latest_evaluation',
                orderable: false,
                render: function(data) {
                    if (data) {
                        return `<small class="text-success"><i class="fas fa-check-circle me-1"></i>${data}</small>`;
                    }
                    return `<small class="text-muted"><i class="fas fa-minus-circle me-1"></i>{{ __('Not evaluated') }}</small>`;
                }
            },
            {
                data: 'latest_ranking',
                name: 'latest_ranking',
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    if (data) {
                        const badgeClass = data <= 3 ? 'bg-success' : data <= 10 ? 'bg-warning' : 'bg-secondary';
                        return `<span class="badge ${badgeClass}">#${data}</span>`;
                    }
                    return `<span class="text-muted">-</span>`;
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/employees/${row.id}" data-bs-toggle="tooltip" title="{{ __('View Full Details') }}">
                                        <i class="fas fa-eye text-info me-2"></i>
                                        {{ __('View Details') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('evaluations.create') }}?employee_id=${row.id}" data-bs-toggle="tooltip" title="{{ __('Start Evaluation') }}">
                                        <i class="fas fa-clipboard-check text-success me-2"></i>
                                        {{ __('Start Evaluation') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/employees/${row.id}/edit" data-bs-toggle="tooltip" title="{{ __('Edit Employee') }}">
                                        <i class="fas fa-edit text-warning me-2"></i>
                                        {{ __('Edit Employee') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteEmployee(${row.id})" data-bs-toggle="tooltip" title="{{ __('Delete Employee') }}">
                                        <i class="fas fa-trash me-2"></i>
                                        {{ __('Delete Employee') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 15,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            processing: '<div class="text-center"><div class="spinner-border text-primary me-2"></div>{{ __("Processing...") }}</div>',
            search: "{{ __('Search') }}:",
            lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
            info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
            infoEmpty: "{{ __('Showing') }} 0 {{ __('to') }} 0 {{ __('of') }} 0 {{ __('entries') }}",
            infoFiltered: "({{ __('filtered from') }} _MAX_ {{ __('total entries') }})",
            loadingRecords: "{{ __('Loading...') }}",
            zeroRecords: "{{ __('No matching records found') }}",
            emptyTable: "{{ __('No data available in table') }}",
            paginate: {
                first: "{{ __('First') }}",
                previous: "{{ __('Previous') }}",
                next: "{{ __('Next') }}",
                last: "{{ __('Last') }}"
            }
        },
        drawCallback: function() {
            // Initialize tooltips after table draw
            initializeTooltips();
        }
    });
}

function loadFilterOptions() {
    // Load departments
    $.get("{{ route('employees.index') }}", {get_departments: true})
        .done(function(response) {
            let departmentSelect = $('#departmentFilter');
            departmentSelect.empty().append('<option value="">{{ __("All Departments") }}</option>');
            if (response.departments) {
                response.departments.forEach(function(dept) {
                    departmentSelect.append(`<option value="${dept}">${dept}</option>`);
                });
            }
        })
        .fail(function() {
            console.log('Could not load departments');
        });

    // Load positions
    $.get("{{ route('employees.index') }}", {get_positions: true})
        .done(function(response) {
            let positionSelect = $('#positionFilter');
            positionSelect.empty().append('<option value="">{{ __("All Positions") }}</option>');
            if (response.positions) {
                response.positions.forEach(function(pos) {
                    positionSelect.append(`<option value="${pos}">${pos}</option>`);
                });
            }
        })
        .fail(function() {
            console.log('Could not load positions');
        });
}

function loadStatistics() {
    $.get("{{ route('employees.index') }}", {get_stats: true})
        .done(function(response) {
            if (response.stats) {
                $('#totalEmployees').text(response.stats.total || 0);
                $('#evaluatedEmployees').text(response.stats.evaluated || 0);
                $('#totalDepartments').text(response.stats.departments || 0);
                $('#activeEmployees').text(response.stats.active || 0);
            }
        })
        .fail(function() {
            console.log('Could not load statistics');
        });
}

function initializeTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
}

function toggleFilters() {
    $('#filtersCollapse').collapse('toggle');
}

function clearFilters() {
    $('#departmentFilter, #positionFilter, #evaluationFilter').val('').trigger('change');
}

function applyFilters() {
    employeesTable.ajax.reload();
}

function refreshTable() {
    employeesTable.ajax.reload(null, false);
    loadStatistics();

    Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        icon: 'success',
        title: '{{ __("Data refreshed successfully") }}'
    });
}

function showImportModal() {
    $('#importModal').modal('show');
}

// Handle import form submission
$('#importForm').on('submit', function(e) {
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Importing...") }}');
    
    // Reset on form reset
    $('#importModal').on('hidden.bs.modal', function() {
        submitBtn.prop('disabled', false).html(originalText);
        $('#import_file').val('');
    });
});

function deleteEmployee(id) {
    Swal.fire({
        title: '{{ __("Are you sure?") }}',
        text: '{{ __("This action cannot be undone") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("Yes, delete it!") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/employees/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        employeesTable.ajax.reload(null, false);
                        loadStatistics();

                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Deleted!") }}',
                            text: response.message || '{{ __("Employee has been deleted successfully") }}',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: response.message || '{{ __("Something went wrong") }}'
                        });
                    }
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || '{{ __("Error occurred while deleting employee") }}';
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: message
                    });
                }
            });
        }
    });
}

function viewEmployee(id) {
    // Navigate directly to employee details page
    window.location.href = `/employees/${id}`;
}

function editEmployee(id) {
    window.location.href = `/employees/${id}/edit`;
}

function quickEvaluate(employeeId) {
    window.location.href = `{{ route('evaluations.create') }}?employee_id=${employeeId}`;
}

function viewPerformance(employeeId) {
    window.location.href = `/employees/${employeeId}/performance`;
}

function exportData() {
    // Show export options
    Swal.fire({
        title: '{{ __("Export Data") }}',
        text: '{{ __("Choose export format") }}',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf me-1"></i>{{ __("PDF") }}',
        denyButtonText: '<i class="fas fa-file-excel me-1"></i>{{ __("Excel") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: '#dc3545',
        denyButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export as PDF
            window.location.href = "{{ route('employees.export') }}?format=pdf";
        } else if (result.isDenied) {
            // Export as Excel
            window.location.href = "{{ route('employees.export') }}?format=excel";
        }
    });
}

// Restore functionality
function showRestoreModal() {
    $('#restoreModal').modal('show');
    loadDeletedEmployees();
}

function loadDeletedEmployees() {
    $.get("{{ route('employees.index') }}", { get_deleted: true })
        .done(function(response) {
            if (response.deleted_employees && response.deleted_employees.length > 0) {
                displayDeletedEmployees(response.deleted_employees);
                $('#noDeletedEmployees').hide();
                $('#deletedEmployeesCards').show();
            } else {
                $('#deletedEmployeesCards').hide();
                $('#noDeletedEmployees').show();
            }
        })
        .fail(function() {
            console.log('Failed to load deleted employees');
            $('#deletedEmployeesCards').hide();
            $('#noDeletedEmployees').show();
        });
}

function displayDeletedEmployees(employees) {
    const cardsContainer = $('#deletedEmployeesCards');
    cardsContainer.empty();

    employees.forEach(function(employee) {
        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card deleted-employee-card" data-employee-id="${employee.id}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input employee-checkbox"
                                   value="${employee.id}" id="emp_${employee.id}"
                                   onchange="updateRestoreAllButton()">
                            <label class="form-check-label small" for="emp_${employee.id}">
                                {{ __('Select') }}
                            </label>
                        </div>
                        <span class="badge bg-danger">
                            <i class="fas fa-trash me-1"></i>{{ __('Deleted') }}
                        </span>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="employee-avatar bg-secondary me-3">${employee.name.substring(0, 2).toUpperCase()}</div>
                            <div>
                                <h6 class="mb-1 fw-bold">${employee.name}</h6>
                                <code class="badge bg-light text-dark">${employee.employee_code}</code>
                            </div>
                        </div>

                        <div class="employee-info mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Position') }}</small>
                                    <span class="fw-medium">${employee.position || '-'}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">{{ __('Department') }}</small>
                                    <span class="fw-medium">${employee.department || '-'}</span>
                                </div>
                            </div>
                        </div>

                        <div class="deleted-info mb-3">
                            <small class="text-muted d-block">{{ __('Deleted Date') }}</small>
                            <span class="text-danger">
                                <i class="fas fa-calendar me-1"></i>
                                ${employee.deleted_at}
                            </span>
                        </div>

                        <div class="card-actions d-flex gap-2">
                            <button class="btn btn-outline-warning btn-sm flex-fill"
                                    onclick="restoreEmployees([${employee.id}])"
                                    title="{{ __('Restore Employee') }}">
                                <i class="fas fa-undo me-1"></i>{{ __('Restore') }}
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="forceDeleteEmployees([${employee.id}])"
                                    title="{{ __('Delete Permanently') }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        cardsContainer.append(card);
    });

    updateRestoreAllButton();
}

function updateRestoreAllButton() {
    const checkedBoxes = $('.employee-checkbox:checked');
    const restoreAllBtn = $('#restoreAllBtn');

    if (checkedBoxes.length > 0) {
        restoreAllBtn.prop('disabled', false);
        restoreAllBtn.text(`Restore All (${checkedBoxes.length})`);
    } else {
        restoreAllBtn.prop('disabled', true);
        restoreAllBtn.text('Restore All');
    }
}

function toggleSelectAllDeleted() {
    const selectAll = $('#selectAllDeleted');
    const checkboxes = $('.employee-checkbox');

    checkboxes.prop('checked', selectAll.is(':checked'));
    updateRestoreAllButton();
}

function restoreAllEmployees() {
    const checkedBoxes = $('.employee-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const employeeIds = checkedBoxes.map(function() {
        return $(this).val();
    }).get();

    Swal.fire({
        title: '{{ __("Restore Employees") }}',
        text: `{{ __("Are you sure you want to restore") }} ${employeeIds.length} {{ __("employee(s)") }}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("Yes, restore them!") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreEmployees(employeeIds);
        }
    });
}

function restoreEmployees(employeeIds) {
    $.ajax({
        url: "{{ route('employees.restore') }}",
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            employee_ids: employeeIds
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("Restored!") }}',
                    text: response.message || `{{ __("Successfully restored") }} ${employeeIds.length} {{ __("employee(s)") }}`,
                    timer: 3000,
                    showConfirmButton: false
                });

                // Refresh data
                loadDeletedEmployees();
                employeesTable.ajax.reload();
                loadStatistics();

                // Close modal if no more deleted employees
                if (response.deleted_employees && response.deleted_employees.length === 0) {
                    $('#restoreModal').modal('hide');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    text: response.message || '{{ __("Something went wrong") }}'
                });
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || '{{ __("Error occurred while restoring employees") }}';
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: message
            });
        }
    });
}

function refreshRestoreList() {
    loadDeletedEmployees();
}

function forceDeleteEmployees(employeeIds) {
    if (employeeIds.length === 0) return;

    Swal.fire({
        title: '{{ __("Permanently Delete Employees") }}',
        text: `{{ __("This action cannot be undone! Are you sure you want to permanently delete") }} ${employeeIds.length} {{ __("employee(s)") }}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("Yes, delete permanently!") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        showDenyButton: true,
        denyButtonText: '{{ __("No, keep them") }}',
        denyButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('employees.force-delete') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    employee_ids: employeeIds
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Permanently Deleted!") }}',
                            text: response.message || `{{ __("Successfully permanently deleted") }} ${employeeIds.length} {{ __("employee(s)") }}`,
                            timer: 3000,
                            showConfirmButton: false
                        });

                        // Refresh data
                        loadDeletedEmployees();
                        employeesTable.ajax.reload();
                        loadStatistics();

                        // Close modal if no more deleted employees
                        if (response.deleted_employees && response.deleted_employees.length === 0) {
                            $('#restoreModal').modal('hide');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: response.message || '{{ __("Something went wrong") }}'
                        });
                    }
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || '{{ __("Error occurred while permanently deleting employees") }}';
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: message
                    });
                }
            });
        }
    });
}


</script>
@endpush

@push('styles')
<style>
.employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 0.875rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Dropdown Styling */
.dropdown-toggle::after {
    display: none;
}

.dropdown-menu {
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    padding: 0.5rem 0;
    min-width: 200px;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8fafc;
    color: #1e293b;
}

.dropdown-item.text-danger:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

.dropdown-divider {
    margin: 0.5rem 0;
    border-color: #e5e7eb;
}

/* Action button styling */
.btn-outline-secondary {
    border-color: #d1d5db;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #374151;
}

/* Deleted Employee Cards Styling */
.deleted-employee-card {
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.deleted-employee-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    border-color: #d1d5db;
}

.deleted-employee-card .card-header {
    background-color: #fef2f2;
    border-bottom: 1px solid #fecaca;
}

.deleted-employee-card .employee-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.deleted-employee-card .employee-info .row {
    margin: 0;
}

.deleted-employee-card .employee-info .col-6 {
    padding: 0.25rem;
}

.deleted-employee-card .deleted-info {
    padding: 0.5rem;
    background-color: #fef2f2;
    border-radius: 0.375rem;
    border-left: 3px solid #dc2626;
}

.deleted-employee-card .card-actions {
    margin-top: auto;
}

.deleted-employee-card .form-check-input:checked {
    background-color: #f59e0b;
    border-color: #f59e0b;
}

.deleted-employee-card .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
}
</style>
@endpush
