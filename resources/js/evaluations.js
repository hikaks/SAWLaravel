/**
 * Evaluations Management JavaScript
 */

$(document).ready(function() {
    initializeEvaluationsPage();
});

function initializeEvaluationsPage() {
    // Initialize pagination variables
    window.currentPage = 1;
    window.perPage = 10;
    
    // Load initial data
    loadEvaluations();
    
    // Per page select change
    $('#perPageSelect').change(function() {
        window.perPage = $(this).val();
        window.currentPage = 1;
        loadEvaluations();
    });
}

function loadEvaluations() {
    console.log('Loading evaluations...');

    const requestData = {
        ajax: true,
        page: window.currentPage,
        per_page: window.perPage
    };

    const evaluationsUrl = window.appRoutes?.evaluations?.index || '/evaluations';

    $.ajax({
        url: evaluationsUrl,
        type: 'GET',
        data: requestData,
        dataType: 'json',
        success: function(response) {
            console.log('AJAX Success Response:', response);
            if (response.data && response.data.length > 0) {
                displayEvaluations(response.data);
                updateStatistics(response.data);
                updatePagination(response.pagination);
            } else {
                $('#evaluationsTableBody').html('<tr><td colspan="9" class="text-center py-4">No evaluations found</td></tr>');
                updateStatistics([]);
                updatePagination(null);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#evaluationsTableBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Error loading data</td></tr>');
            updatePagination(null);
        }
    });
}

function displayEvaluations(evaluations) {
    console.log('Displaying evaluations:', evaluations);
    const tbody = $('#evaluationsTableBody');
    tbody.empty();

    evaluations.forEach(function(evaluation, index) {
        const safeEmployeeName = window.utils?.escapeHtml(evaluation.employee?.name) || evaluation.employee?.name || 'Unknown';
        const safeEmployeeCode = window.utils?.escapeHtml(evaluation.employee?.employee_code) || evaluation.employee?.employee_code || '-';
        const safeCriteriaName = window.utils?.escapeHtml(evaluation.criteria?.name) || evaluation.criteria?.name || 'Unknown';
        const safeScore = evaluation.score || 0;
        const safePeriod = window.utils?.escapeHtml(evaluation.evaluation_period) || evaluation.evaluation_period || '-';
        
        const scoreClass = safeScore >= 90 ? 'text-success' : safeScore >= 80 ? 'text-warning' : safeScore >= 70 ? 'text-info' : 'text-danger';
        const scoreBadge = safeScore >= 90 ? 'bg-success' : safeScore >= 80 ? 'bg-warning' : safeScore >= 70 ? 'bg-info' : 'bg-danger';
        
        const row = `
            <tr>
                <td class="text-center">${(window.currentPage - 1) * window.perPage + index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="employee-avatar bg-primary me-2">${safeEmployeeName.substring(0, 2).toUpperCase()}</div>
                        <div>
                            <div class="fw-semibold">${safeEmployeeName}</div>
                            <small class="text-muted">${safeEmployeeCode}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${safeCriteriaName}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${scoreBadge}">${safeScore}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary">${safePeriod}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info" onclick="viewEvaluation(${evaluation.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="editEvaluation(${evaluation.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteEvaluation(${evaluation.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateStatistics(evaluations) {
    if (evaluations.length === 0) {
        $('#totalEvaluations').text('0');
        $('#avgScore').text('0');
        $('#completionRate').text('0%');
        $('#lastUpdated').text('-');
        return;
    }

    // Calculate statistics
    const totalEvaluations = evaluations.length;
    const totalScore = evaluations.reduce((sum, eval) => sum + (eval.score || 0), 0);
    const avgScore = totalEvaluations > 0 ? Math.round(totalScore / totalEvaluations) : 0;
    
    // Update UI
    $('#totalEvaluations').text(totalEvaluations);
    $('#avgScore').text(avgScore);
    $('#completionRate').text('100%'); // Assuming loaded evaluations are complete
    $('#lastUpdated').text(new Date().toLocaleDateString());
}

function refreshTable() {
    loadEvaluations();
    
    if (window.utils?.showSuccessToast) {
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

function updatePagination(pagination) {
    if (!pagination) {
        $('#paginationContainer').html('');
        $('#currentPage').text('1');
        $('#totalPages').text('1');
        $('#showingInfo').text('0-0 of 0');
        return;
    }

    // Update page info
    $('#currentPage').text(pagination.current_page);
    $('#totalPages').text(pagination.last_page);
    $('#showingInfo').text(pagination.from + '-' + pagination.to + ' of ' + pagination.total);

    // Generate pagination buttons
    let paginationHtml = '';

    // Previous button
    if (pagination.has_previous_page) {
        paginationHtml += '<li class="page-item">' +
            '<a class="page-link" href="#" onclick="goToPage(' + (pagination.current_page - 1) + ')">' +
                '<i class="fas fa-chevron-left"></i>' +
            '</a>' +
        '</li>';
    } else {
        paginationHtml += '<li class="page-item disabled">' +
            '<span class="page-link"><i class="fas fa-chevron-left"></i></span>' +
        '</li>';
    }

    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    // First page
    if (startPage > 1) {
        paginationHtml += '<li class="page-item">' +
            '<a class="page-link" href="#" onclick="goToPage(1)">1</a>' +
        '</li>';
        if (startPage > 2) {
            paginationHtml += '<li class="page-item disabled">' +
                '<span class="page-link">...</span>' +
            '</li>';
        }
    }

    // Page numbers around current page
    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            paginationHtml += '<li class="page-item active">' +
                '<span class="page-link">' + i + '</span>' +
            '</li>';
        } else {
            paginationHtml += '<li class="page-item">' +
                '<a class="page-link" href="#" onclick="goToPage(' + i + ')">' + i + '</a>' +
            '</li>';
        }
    }

    // Last page
    if (endPage < pagination.last_page) {
        if (endPage < pagination.last_page - 1) {
            paginationHtml += '<li class="page-item disabled">' +
                '<span class="page-link">...</span>' +
            '</li>';
        }
        paginationHtml += '<li class="page-item">' +
            '<a class="page-link" href="#" onclick="goToPage(' + pagination.last_page + ')">' + pagination.last_page + '</a>' +
        '</li>';
    }

    // Next button
    if (pagination.has_next_page) {
        paginationHtml += '<li class="page-item">' +
            '<a class="page-link" href="#" onclick="goToPage(' + (pagination.current_page + 1) + ')">' +
                '<i class="fas fa-chevron-right"></i>' +
            '</a>' +
        '</li>';
    } else {
        paginationHtml += '<li class="page-item disabled">' +
            '<span class="page-link"><i class="fas fa-chevron-right"></i></span>' +
        '</li>';
    }

    $('#paginationContainer').html(paginationHtml);
}

function goToPage(page) {
    window.currentPage = page;
    loadEvaluations();
}

// Restore functionality
function showRestoreModal() {
    $('#restoreModal').modal('show');
    loadDeletedEvaluations();
}

function loadDeletedEvaluations() {
    const evaluationsUrl = window.appRoutes?.evaluations?.index || '/evaluations';
    $.get(evaluationsUrl, { get_deleted: true })
        .done(function(response) {
            if (response.deleted_evaluations && response.deleted_evaluations.length > 0) {
                displayDeletedEvaluations(response.deleted_evaluations);
                $('#noDeletedEvaluations').hide();
                $('#deletedEvaluationsCards').show();
            } else {
                $('#deletedEvaluationsCards').hide();
                $('#noDeletedEvaluations').show();
            }
        })
        .fail(function() {
            console.log('Failed to load deleted evaluations');
            $('#deletedEvaluationsCards').hide();
            $('#noDeletedEvaluations').show();
        });
}

function displayDeletedEvaluations(evaluations) {
    const cardsContainer = $('#deletedEvaluationsCards');
    cardsContainer.empty();

    evaluations.forEach(function(evaluation) {
        const safeEmployeeName = window.utils?.escapeHtml(evaluation.employee_name) || evaluation.employee_name || 'Unknown';
        const safeEmployeeCode = window.utils?.escapeHtml(evaluation.employee_code) || evaluation.employee_code || '-';
        const safeCriteriaName = window.utils?.escapeHtml(evaluation.criteria_name) || evaluation.criteria_name || 'Unknown';
        const safeScore = evaluation.score || 0;
        const safePeriod = window.utils?.escapeHtml(evaluation.evaluation_period) || evaluation.evaluation_period || '-';
        const safeDeletedAt = window.utils?.escapeHtml(evaluation.deleted_at) || evaluation.deleted_at;
        
        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card deleted-evaluation-card" data-evaluation-id="${evaluation.id}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input evaluation-checkbox"
                                   value="${evaluation.id}" id="eval_${evaluation.id}"
                                   onchange="updateRestoreAllButton()">
                            <label class="form-check-label small" for="eval_${evaluation.id}">
                                Select
                            </label>
                        </div>
                        <span class="badge bg-danger">
                            <i class="fas fa-trash me-1"></i>Deleted
                        </span>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="evaluation-icon bg-secondary me-3">
                                <i class="fas fa-clipboard-check text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">${safeEmployeeName}</h6>
                                <small class="text-muted">${safeEmployeeCode}</small>
                            </div>
                        </div>

                        <div class="evaluation-info mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Criteria</small>
                                    <span class="fw-medium">${safeCriteriaName}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Score</small>
                                    <span class="fw-medium">${safeScore}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Period</small>
                                    <span class="fw-medium">${safePeriod}</span>
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
                                    onclick="restoreEvaluations([${evaluation.id}])"
                                    title="Restore Evaluation">
                                <i class="fas fa-undo me-1"></i>Restore
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="forceDeleteEvaluations([${evaluation.id}])"
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
    const checkedBoxes = $('.evaluation-checkbox:checked');
    const restoreAllBtn = $('#restoreAllBtn');

    if (checkedBoxes.length > 0) {
        restoreAllBtn.prop('disabled', false);
        restoreAllBtn.text(`Restore All (${checkedBoxes.length})`);
    } else {
        restoreAllBtn.prop('disabled', true);
        restoreAllBtn.text('Restore All');
    }
}

function restoreEvaluations(evaluationIds) {
    const restoreUrl = window.appRoutes?.evaluations?.restore || '/evaluations/restore';
    
    $.ajax({
        url: restoreUrl,
        type: 'POST',
        data: {
            _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content'),
            evaluation_ids: evaluationIds
        },
        success: function(response) {
            if (response.success) {
                if (window.utils?.showSuccessToast) {
                    window.utils.showSuccessToast(response.message || `Successfully restored ${evaluationIds.length} evaluation(s)`);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restored!',
                        text: response.message || `Successfully restored ${evaluationIds.length} evaluation(s)`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }

                // Refresh data
                loadDeletedEvaluations();
                loadEvaluations();

                // Close modal if no more deleted evaluations
                if (response.deleted_evaluations && response.deleted_evaluations.length === 0) {
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
                let message = xhr.responseJSON?.message || 'Error occurred while restoring evaluations';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            }
        }
    });
}

function viewEvaluation(id) {
    // Navigate to evaluation details
    window.location.href = `/evaluations/${id}`;
}

function editEvaluation(id) {
    // Navigate to evaluation edit
    window.location.href = `/evaluations/${id}/edit`;
}

function deleteEvaluation(id) {
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
                window.buildRoute(window.appRoutes.evaluations.destroy, {id: id}) : 
                `/evaluations/${id}`;
                
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadEvaluations();
                        
                        if (window.utils?.showSuccessToast) {
                            window.utils.showSuccessToast(response.message || 'Evaluation deleted successfully');
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Evaluation deleted successfully',
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
                        let message = xhr.responseJSON?.message || 'Error occurred while deleting evaluation';
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