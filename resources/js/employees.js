/**
 * Employee Management JavaScript
 */

let employeesTable;

$(document).ready(function() {
    // Initialize page
    initializeEmployeesPage();
});

function initializeEmployeesPage() {
    initializeDataTable();
    loadFilterOptions();
    loadStatistics();
    initializeTooltips();
    
    // Filter change events
    $('#departmentFilter, #positionFilter, #evaluationFilter').change(function() {
        employeesTable.ajax.reload();
    });
}

function initializeDataTable() {
    employeesTable = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appRoutes?.employees?.index || '/employees',
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
                    return `<code class="badge bg-light text-dark">${window.utils?.escapeHtml(data) || data}</code>`;
                }
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const safeName = window.utils?.escapeHtml(data) || data;
                    const safeCode = window.utils?.escapeHtml(row.employee_code) || row.employee_code;
                    const avatar = data.substring(0, 2).toUpperCase();
                    const showUrl = window.buildRoute ? window.buildRoute(window.appRoutes.employees.show, {id: row.id}) : `/employees/${row.id}`;
                    
                    return `
                        <div class="d-flex align-items-center">
                            <div class="employee-avatar bg-primary me-3">${avatar}</div>
                            <div>
                                <div class="fw-semibold">
                                    <a href="${showUrl}" class="text-decoration-none text-primary fw-bold" data-bs-toggle="tooltip" title="View Employee Details">
                                        ${safeName}
                                    </a>
                                </div>
                                <small class="text-muted">${safeCode}</small>
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
                    const safeDept = window.utils?.escapeHtml(data) || data;
                    return `<span class="badge bg-light text-dark">${safeDept}</span>`;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data) {
                    const safeEmail = window.utils?.escapeHtml(data) || data;
                    return `<a href="mailto:${safeEmail}" class="text-decoration-none">${safeEmail}</a>`;
                }
            },
            {
                data: 'latest_evaluation',
                name: 'latest_evaluation',
                orderable: false,
                render: function(data) {
                    if (data) {
                        const safeData = window.utils?.escapeHtml(data) || data;
                        return `<small class="text-success"><i class="fas fa-check-circle me-1"></i>${safeData}</small>`;
                    }
                    return `<small class="text-muted"><i class="fas fa-minus-circle me-1"></i>Not evaluated</small>`;
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
                    const showUrl = window.buildRoute ? window.buildRoute(window.appRoutes.employees.show, {id: row.id}) : `/employees/${row.id}`;
                    const editUrl = window.buildRoute ? window.buildRoute(window.appRoutes.employees.edit, {id: row.id}) : `/employees/${row.id}/edit`;
                    const evaluateUrl = window.appRoutes?.evaluations?.create || '/evaluations/create';
                    
                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="${showUrl}" data-bs-toggle="tooltip" title="View Full Details">
                                        <i class="fas fa-eye text-info me-2"></i>
                                        View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${evaluateUrl}?employee_id=${row.id}" data-bs-toggle="tooltip" title="Start Evaluation">
                                        <i class="fas fa-clipboard-check text-success me-2"></i>
                                        Start Evaluation
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${editUrl}" data-bs-toggle="tooltip" title="Edit Employee">
                                        <i class="fas fa-edit text-warning me-2"></i>
                                        Edit Employee
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteEmployee(${row.id})" data-bs-toggle="tooltip" title="Delete Employee">
                                        <i class="fas fa-trash me-2"></i>
                                        Delete Employee
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
            processing: '<div class="text-center"><div class="spinner-border text-primary me-2"></div>Processing...</div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            loadingRecords: "Loading...",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                previous: "Previous",
                next: "Next",
                last: "Last"
            }
        },
        drawCallback: function() {
            initializeTooltips();
        }
    });
}

function loadFilterOptions() {
    // Load departments
    const departmentsUrl = window.appRoutes?.employees?.index || '/employees';
    $.get(departmentsUrl, {get_departments: true})
        .done(function(response) {
            let departmentSelect = $('#departmentFilter');
            departmentSelect.empty().append('<option value="">All Departments</option>');
            if (response.departments) {
                response.departments.forEach(function(dept) {
                    const safeDept = window.utils?.escapeHtml(dept) || dept;
                    departmentSelect.append(`<option value="${dept}">${safeDept}</option>`);
                });
            }
        })
        .fail(function() {
            console.log('Could not load departments');
        });

    // Load positions
    $.get(departmentsUrl, {get_positions: true})
        .done(function(response) {
            let positionSelect = $('#positionFilter');
            positionSelect.empty().append('<option value="">All Positions</option>');
            if (response.positions) {
                response.positions.forEach(function(pos) {
                    const safePos = window.utils?.escapeHtml(pos) || pos;
                    positionSelect.append(`<option value="${pos}">${safePos}</option>`);
                });
            }
        })
        .fail(function() {
            console.log('Could not load positions');
        });
}

function loadStatistics() {
    const statsUrl = window.appRoutes?.employees?.index || '/employees';
    $.get(statsUrl, {get_stats: true})
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
    const refreshBtn = document.getElementById('refreshBtn');
    
    // Set loading state
    if (window.uiHelpers && refreshBtn) {
        window.uiHelpers.setButtonLoading(refreshBtn, true, 'Refreshing...');
    }
    
    // Reload table and statistics
    employeesTable.ajax.reload(null, false);
    loadStatistics();
    
    // Reset loading state after a short delay
    setTimeout(() => {
        if (window.uiHelpers && refreshBtn) {
            window.uiHelpers.setButtonLoading(refreshBtn, false);
        }
        
        // Show success notification
        if (window.uiHelpers) {
            window.uiHelpers.showNotification('Data refreshed successfully', 'success');
        } else if (window.utils?.showSuccessToast) {
            window.utils.showSuccessToast('Data refreshed successfully');
        } else {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                icon: 'success',
                title: 'Data refreshed successfully'
            });
        }
    }, 1000);
}

function showImportModal() {
    $('#importModal').modal('show');
}

// Handle import form submission
$('#importForm').on('submit', function(e) {
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Importing...');
    
    // Reset on form reset
    $('#importModal').on('hidden.bs.modal', function() {
        submitBtn.prop('disabled', false).html(originalText);
        $('#import_file').val('');
    });
});

function deleteEmployee(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteUrl = window.buildRoute ? 
                window.buildRoute(window.appRoutes.employees.destroy, {id: id}) : 
                `/employees/${id}`;
                
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        employeesTable.ajax.reload(null, false);
                        loadStatistics();

                        if (window.utils?.showSuccessToast) {
                            window.utils.showSuccessToast(response.message || 'Employee has been deleted successfully');
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Employee has been deleted successfully',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (window.utils?.showErrorToast) {
                            window.utils.showErrorToast(response.message || 'Something went wrong');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Something went wrong'
                            });
                        }
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Error occurred while deleting employee';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        }
    });
}

function viewEmployee(id) {
    const showUrl = window.buildRoute ? 
        window.buildRoute(window.appRoutes.employees.show, {id: id}) : 
        `/employees/${id}`;
    window.location.href = showUrl;
}

function editEmployee(id) {
    const editUrl = window.buildRoute ? 
        window.buildRoute(window.appRoutes.employees.edit, {id: id}) : 
        `/employees/${id}/edit`;
    window.location.href = editUrl;
}

function quickEvaluate(employeeId) {
    const evaluateUrl = window.appRoutes?.evaluations?.create || '/evaluations/create';
    window.location.href = `${evaluateUrl}?employee_id=${employeeId}`;
}

function viewPerformance(employeeId) {
    const showUrl = window.buildRoute ? 
        window.buildRoute(window.appRoutes.employees.show, {id: employeeId}) : 
        `/employees/${employeeId}`;
    window.location.href = `${showUrl}#performance`;
}

function exportData() {
    const exportBtn = document.getElementById('exportBtn');
    
    Swal.fire({
        title: 'Export Data',
        text: 'Choose export format',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf me-1"></i>PDF',
        denyButtonText: '<i class="fas fa-file-excel me-1"></i>Excel',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        denyButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'rounded-lg',
            confirmButton: 'rounded-lg',
            denyButton: 'rounded-lg',
            cancelButton: 'rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed || result.isDenied) {
            // Set loading state
            if (window.uiHelpers && exportBtn) {
                window.uiHelpers.setButtonLoading(exportBtn, true, 'Exporting...');
            }
            
            const exportUrl = window.appRoutes?.employees?.export || '/employees/export';
            const format = result.isConfirmed ? 'pdf' : 'excel';
            
            // Show notification
            if (window.uiHelpers) {
                window.uiHelpers.showNotification(`Preparing ${format.toUpperCase()} export...`, 'info');
            }
            
            // Trigger download
            window.location.href = `${exportUrl}?format=${format}`;
            
            // Reset loading state after delay
            setTimeout(() => {
                if (window.uiHelpers && exportBtn) {
                    window.uiHelpers.setButtonLoading(exportBtn, false);
                }
            }, 3000);
        }
    });
}

// Restore functionality
function showRestoreModal() {
    $('#restoreModal').modal('show');
    loadDeletedEmployees();
}

function loadDeletedEmployees() {
    const employeesUrl = window.appRoutes?.employees?.index || '/employees';
    $.get(employeesUrl, { get_deleted: true })
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
        const safeName = window.utils?.escapeHtml(employee.name) || employee.name;
        const safeCode = window.utils?.escapeHtml(employee.employee_code) || employee.employee_code;
        const safePosition = window.utils?.escapeHtml(employee.position) || employee.position || '-';
        const safeDepartment = window.utils?.escapeHtml(employee.department) || employee.department || '-';
        const safeDeletedAt = window.utils?.escapeHtml(employee.deleted_at) || employee.deleted_at;
        
        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card deleted-employee-card" data-employee-id="${employee.id}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input employee-checkbox"
                                   value="${employee.id}" id="emp_${employee.id}"
                                   onchange="updateRestoreAllButton()">
                            <label class="form-check-label small" for="emp_${employee.id}">
                                Select
                            </label>
                        </div>
                        <span class="badge bg-danger">
                            <i class="fas fa-trash me-1"></i>Deleted
                        </span>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="employee-avatar bg-secondary me-3">${safeName.substring(0, 2).toUpperCase()}</div>
                            <div>
                                <h6 class="mb-1 fw-bold">${safeName}</h6>
                                <code class="badge bg-light text-dark">${safeCode}</code>
                            </div>
                        </div>

                        <div class="employee-info mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Position</small>
                                    <span class="fw-medium">${safePosition}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Department</small>
                                    <span class="fw-medium">${safeDepartment}</span>
                                </div>
                            </div>
                        </div>

                        <div class="deleted-info mb-3">
                            <small class="text-muted d-block">Deleted Date</small>
                            <span class="text-danger">
                                <i class="fas fa-calendar me-1"></i>
                                ${safeDeletedAt}
                            </span>
                        </div>

                        <div class="card-actions d-flex gap-2">
                            <button class="btn btn-outline-warning btn-sm flex-fill"
                                    onclick="restoreEmployees([${employee.id}])"
                                    title="Restore Employee">
                                <i class="fas fa-undo me-1"></i>Restore
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="forceDeleteEmployees([${employee.id}])"
                                    title="Delete Permanently">
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
        title: 'Restore Employees',
        text: `Are you sure you want to restore ${employeeIds.length} employee(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, restore them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreEmployees(employeeIds);
        }
    });
}

function restoreEmployees(employeeIds) {
    const restoreUrl = window.appRoutes?.employees?.restore || '/employees/restore';
    
    $.ajax({
        url: restoreUrl,
        type: 'POST',
        data: {
            _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content'),
            employee_ids: employeeIds
        },
        success: function(response) {
            if (response.success) {
                if (window.utils?.showSuccessToast) {
                    window.utils.showSuccessToast(response.message || `Successfully restored ${employeeIds.length} employee(s)`);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restored!',
                        text: response.message || `Successfully restored ${employeeIds.length} employee(s)`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }

                // Refresh data
                loadDeletedEmployees();
                employeesTable.ajax.reload();
                loadStatistics();

                // Close modal if no more deleted employees
                if (response.deleted_employees && response.deleted_employees.length === 0) {
                    $('#restoreModal').modal('hide');
                }
            } else {
                if (window.utils?.showErrorToast) {
                    window.utils.showErrorToast(response.message || 'Something went wrong');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Something went wrong'
                    });
                }
            }
        },
        error: function(xhr) {
            if (window.utils?.handleAjaxError) {
                window.utils.handleAjaxError(xhr);
            } else {
                let message = xhr.responseJSON?.message || 'Error occurred while restoring employees';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        }
    });
}

function refreshRestoreList() {
    loadDeletedEmployees();
}

function forceDeleteEmployees(employeeIds) {
    if (employeeIds.length === 0) return;

    Swal.fire({
        title: 'Permanently Delete Employees',
        text: `This action cannot be undone! Are you sure you want to permanently delete ${employeeIds.length} employee(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete permanently!',
        cancelButtonText: 'Cancel',
        showDenyButton: true,
        denyButtonText: 'No, keep them',
        denyButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            const forceDeleteUrl = window.appRoutes?.employees?.forceDelete || '/employees/force-delete';
            
            $.ajax({
                url: forceDeleteUrl,
                type: 'POST',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content'),
                    employee_ids: employeeIds
                },
                success: function(response) {
                    if (response.success) {
                        if (window.utils?.showSuccessToast) {
                            window.utils.showSuccessToast(response.message || `Successfully permanently deleted ${employeeIds.length} employee(s)`);
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Permanently Deleted!',
                                text: response.message || `Successfully permanently deleted ${employeeIds.length} employee(s)`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }

                        // Refresh data
                        loadDeletedEmployees();
                        employeesTable.ajax.reload();
                        loadStatistics();

                        // Close modal if no more deleted employees
                        if (response.deleted_employees && response.deleted_employees.length === 0) {
                            $('#restoreModal').modal('hide');
                        }
                    } else {
                        if (window.utils?.showErrorToast) {
                            window.utils.showErrorToast(response.message || 'Something went wrong');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Something went wrong'
                            });
                        }
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Error occurred while permanently deleting employees';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        }
    });
}