/**
 * Criteria Management JavaScript
 */

let criteriasTable;

$(document).ready(function() {
    initializeCriteriasPage();
});

function initializeCriteriasPage() {
    initializeDataTable();
    initializeCriteriaChart();
    initializeTooltips();
    
    // Filter change events
    $('#typeFilter, #weightFilter, #sortFilter').change(function() {
        criteriasTable.ajax.reload();
    });
}

function initializeDataTable() {
    criteriasTable = $('#criteriasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appRoutes?.criterias?.index || '/criterias',
            data: function (d) {
                d.type = $('#typeFilter').val();
                d.weight_range = $('#weightFilter').val();
                d.sort_by = $('#sortFilter').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'weight_percentage', name: 'weight', searchable: false},
            {data: 'type_badge', name: 'type', orderable: false, searchable: false},
            {data: 'evaluations_count', name: 'evaluations_count', orderable: false, searchable: false},
            {
                data: 'weight',
                name: 'weight',
                render: function(data, type, row) {
                    if (data >= 20) {
                        return '<span class="badge bg-success">High</span>';
                    } else if (data >= 10) {
                        return '<span class="badge bg-warning">Medium</span>';
                    } else {
                        return '<span class="badge bg-secondary">Low</span>';
                    }
                },
                searchable: false
            },
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[2, 'desc']], // Sort by weight descending
        pageLength: 25,
        responsive: true,
        language: {
            processing: "Processing...",
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
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
        },
        deferRender: true,
        scroller: false
    });
}

function initializeCriteriaChart() {
    if (typeof Chart !== 'undefined') {
        loadCriteriaChartInfo();
    }
}

function loadCriteriaChartInfo() {
    const criteriaUrl = window.appRoutes?.criterias?.index || '/criterias';
    $.get(criteriaUrl, { get_chart_info: true })
        .done(function(response) {
            updateWeightChart(response);
        })
        .fail(function() {
            console.log('Failed to load criteria chart info');
        });
}

function updateWeightChart(data) {
    if (!data || typeof Chart === 'undefined') return;
    
    const ctx = document.getElementById('weightChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.criteriaChart) {
        window.criteriaChart.destroy();
    }
    
    window.criteriaChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Used Weight', 'Remaining Weight'],
            datasets: [{
                data: [data.total_weight, Math.max(0, 100 - data.total_weight)],
                backgroundColor: [
                    data.total_weight === 100 ? '#10b981' : 
                    data.total_weight > 100 ? '#ef4444' : '#f59e0b',
                    '#e5e7eb'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '70%'
        }
    });
}

function refreshTable() {
    criteriasTable.ajax.reload(function() {
        showSuccess('Data successfully refreshed');
    });
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

// Restore functionality for criteria
function showRestoreModal() {
    $('#restoreModal').modal('show');
    loadDeletedCriteria();
}

function loadDeletedCriteria() {
    const criteriaUrl = window.appRoutes?.criterias?.index || '/criterias';
    $.get(criteriaUrl, { get_deleted: true })
        .done(function(response) {
            if (response.deleted_criteria && response.deleted_criteria.length > 0) {
                displayDeletedCriteria(response.deleted_criteria);
                $('#noDeletedCriteria').hide();
                $('#deletedCriteriaCards').show();
            } else {
                $('#deletedCriteriaCards').hide();
                $('#noDeletedCriteria').show();
            }
        })
        .fail(function() {
            console.log('Failed to load deleted criteria');
            $('#deletedCriteriaCards').hide();
            $('#noDeletedCriteria').show();
        });
}

function displayDeletedCriteria(criteria) {
    const cardsContainer = $('#deletedCriteriaCards');
    cardsContainer.empty();

    criteria.forEach(function(criterion) {
        const safeName = window.utils?.escapeHtml(criterion.name) || criterion.name;
        const safeType = window.utils?.escapeHtml(criterion.type) || criterion.type;
        const safeWeight = criterion.weight || 0;
        const safeDeletedAt = window.utils?.escapeHtml(criterion.deleted_at) || criterion.deleted_at;
        
        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card deleted-criteria-card" data-criteria-id="${criterion.id}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input criteria-checkbox"
                                   value="${criterion.id}" id="crit_${criterion.id}"
                                   onchange="updateRestoreAllButton()">
                            <label class="form-check-label small" for="crit_${criterion.id}">
                                Select
                            </label>
                        </div>
                        <span class="badge bg-danger">
                            <i class="fas fa-trash me-1"></i>Deleted
                        </span>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="criteria-icon bg-secondary me-3">
                                <i class="fas fa-list-check text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">${safeName}</h6>
                                <span class="badge bg-${safeType === 'benefit' ? 'success' : 'warning'}">${safeType}</span>
                            </div>
                        </div>

                        <div class="criteria-info mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Weight</small>
                                    <span class="fw-medium">${safeWeight}%</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Type</small>
                                    <span class="fw-medium">${safeType}</span>
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
                                    onclick="restoreCriteria([${criterion.id}])"
                                    title="Restore Criteria">
                                <i class="fas fa-undo me-1"></i>Restore
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="forceDeleteCriteria([${criterion.id}])"
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
    const checkedBoxes = $('.criteria-checkbox:checked');
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
    const checkboxes = $('.criteria-checkbox');

    checkboxes.prop('checked', selectAll.is(':checked'));
    updateRestoreAllButton();
}

function restoreAllCriteria() {
    const checkedBoxes = $('.criteria-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const criteriaIds = checkedBoxes.map(function() {
        return $(this).val();
    }).get();

    Swal.fire({
        title: 'Restore Criteria',
        text: `Are you sure you want to restore ${criteriaIds.length} criteria?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, restore them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreCriteria(criteriaIds);
        }
    });
}

function restoreCriteria(criteriaIds) {
    const restoreUrl = window.appRoutes?.criterias?.restore || '/criterias/restore';
    
    $.ajax({
        url: restoreUrl,
        type: 'POST',
        data: {
            _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content'),
            criteria_ids: criteriaIds
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message || `Successfully restored ${criteriaIds.length} criteria`);

                // Refresh data
                loadDeletedCriteria();
                criteriasTable.ajax.reload();

                // Close modal if no more deleted criteria
                if (response.deleted_criteria && response.deleted_criteria.length === 0) {
                    $('#restoreModal').modal('hide');
                }
            } else {
                showError(response.message || 'Something went wrong');
            }
        },
        error: function(xhr) {
            if (window.utils?.handleAjaxError) {
                window.utils.handleAjaxError(xhr);
            } else {
                let message = xhr.responseJSON?.message || 'Error occurred while restoring criteria';
                showError(message);
            }
        }
    });
}

function forceDeleteCriteria(criteriaIds) {
    if (criteriaIds.length === 0) return;

    Swal.fire({
        title: 'Permanently Delete Criteria',
        text: `This action cannot be undone! Are you sure you want to permanently delete ${criteriaIds.length} criteria?`,
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
            const forceDeleteUrl = window.appRoutes?.criterias?.forceDelete || '/criterias/force-delete';
            
            $.ajax({
                url: forceDeleteUrl,
                type: 'POST',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content'),
                    criteria_ids: criteriaIds
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message || `Successfully permanently deleted ${criteriaIds.length} criteria`);

                        // Refresh data
                        loadDeletedCriteria();
                        criteriasTable.ajax.reload();

                        // Close modal if no more deleted criteria
                        if (response.deleted_criteria && response.deleted_criteria.length === 0) {
                            $('#restoreModal').modal('hide');
                        }
                    } else {
                        showError(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Error occurred while permanently deleting criteria';
                        showError(message);
                    }
                }
            });
        }
    });
}

function refreshRestoreList() {
    loadDeletedCriteria();
}

// Utility functions for success/error messages
function showSuccess(message) {
    if (window.utils?.showSuccessToast) {
        window.utils.showSuccessToast(message);
    } else {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            icon: 'success',
            title: message
        });
    }
}

function showError(message) {
    if (window.utils?.showErrorToast) {
        window.utils.showErrorToast(message);
    } else {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            icon: 'error',
            title: message
        });
    }
}

function showLoading() {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

function deleteCriteria(id) {
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
                window.buildRoute(window.appRoutes.criterias.destroy, {id: id}) : 
                `/criterias/${id}`;
                
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        criteriasTable.ajax.reload();
                        showSuccess(response.message);
                    } else {
                        showError(response.message);
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Error occurred while deleting criteria';
                        showError(message);
                    }
                }
            });
        }
    });
}

function viewCriteria(id) {
    showLoading();

    const showUrl = window.buildRoute ? 
        window.buildRoute(window.appRoutes.criterias.show, {id: id}) : 
        `/criterias/${id}`;

    $.get(showUrl, function(data) {
        $('#criteriaModalBody').html(data);
        $('#criteriaModal').modal('show');
        hideLoading();
    }).fail(function() {
        hideLoading();
        showError('Failed to load criteria details');
    });
}

function showWeightAdjustmentTips() {
    Swal.fire({
        icon: 'info',
        title: 'Weight Adjustment Tips',
        html: `
            <div class="text-start">
                <h6>How to adjust criteria weights:</h6>
                <ul class="text-muted mt-3">
                    <li>Total weight must equal 100%</li>
                    <li>Edit existing criteria to reduce weights</li>
                    <li>Delete unnecessary criteria</li>
                    <li>Redistribute weights proportionally</li>
                </ul>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Changes will affect existing evaluations.
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Got it!',
        confirmButtonColor: '#0d6efd'
    });
}

function initializeTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });
}