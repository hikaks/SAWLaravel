<?php $__env->startSection('title', 'Evaluation List'); ?>
<?php $__env->startSection('page-title', 'Evaluation List'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-check text-primary me-3"></i>
                        Employee Evaluation Management
                    </h1>
                    <p class="text-muted mb-0">Manage and monitor employee performance evaluations</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-warning" onclick="showRestoreModal()">
                        <i class="fas fa-undo me-2"></i>
                        Restore Deleted
                    </button>
                    <button class="btn btn-outline-secondary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-import me-1"></i>
                            Import
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo e(route('evaluations.import-template')); ?>">
                                <i class="fas fa-download me-2"></i>Download Template
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showImportModal()">
                                <i class="fas fa-upload me-2"></i>Upload Data
                            </a></li>
                        </ul>
                    </div>
                    <a href="<?php echo e(route('evaluations.create')); ?>" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Add Evaluation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Evaluations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalEvaluations">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Average Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgScore">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Completion Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="completionRate">0%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                SAW Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="sawStatus">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>
                        Evaluation Data
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary" id="totalRecords">0 records</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Pagination Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <label class="form-label mb-0 fw-bold">Show:</label>
                            <select class="form-select form-select-sm" id="perPageSelect" style="width: 80px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-muted">entries per page</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">Showing</span>
                            <span id="showingInfo" class="fw-bold">0-0 of 0</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="evaluationsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Period</th>
                                    <th>Employee</th>
                                    <th>Code</th>
                                    <th>Criteria</th>
                                    <th class="text-center">Weight</th>
                                    <th class="text-center">Score</th>
                                    <th>Date</th>
                                    <th class="text-center" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="evaluationsTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Navigation -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            <small>Page <span id="currentPage">1</span> of <span id="totalPages">1</span></small>
                        </div>
                        <nav aria-label="Evaluation pagination">
                            <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Deleted Evaluations Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="restoreModalLabel">
                    <i class="fas fa-undo me-2"></i>
                    Restore Deleted Evaluations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    This will show all evaluations that have been deleted. You can restore them one by one or restore all at once.
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Deleted Evaluations</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAllDeleted()">
                            <i class="fas fa-check-square me-1"></i>
                            Select All
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="restoreAllBtn" onclick="restoreAllEvaluations()" disabled>
                            <i class="fas fa-undo me-1"></i>
                            Restore All
                        </button>
                    </div>
                </div>

                <div id="deletedEvaluationsCards" class="row g-3">
                    <!-- Deleted evaluations will be displayed as cards here -->
                </div>

                <div id="noDeletedEvaluations" class="text-center py-4" style="display: none;">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6 class="text-success">No Deleted Evaluations</h6>
                    <p class="text-muted">All evaluations are currently active.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
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
                    <i class="fas fa-file-import me-2"></i>Import Evaluations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('evaluations.import')); ?>" method="POST" enctype="multipart/form-data" id="importForm">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Select Excel/CSV File</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" 
                               accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Maximum file size: 10MB
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download the template first to see the required format</li>
                            <li>Employee codes and criteria names must exist in the system</li>
                            <li>Score must be between 0 and 100</li>
                            <li>Evaluation period format: YYYY-MM (e.g., <?php echo e(date('Y-m')); ?>)</li>
                            <li>Existing evaluations will be updated if found</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-2"></i>Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    console.log('Document ready, loading evaluations...');

    // Initialize pagination variables
    window.currentPage = 1;
    window.perPage = 10;

    loadEvaluations();

    // Per page change event
    $('#perPageSelect').change(function() {
        window.perPage = parseInt($(this).val());
        window.currentPage = 1; // Reset to first page
        loadEvaluations();
    });
});

function loadEvaluations() {
    console.log('Loading evaluations...');

    const requestData = {
        ajax: true,
        page: window.currentPage,
        per_page: window.perPage
    };

    $.ajax({
        url: "<?php echo e(route('evaluations.index')); ?>",
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
        const row = '<tr>' +
            '<td class="text-center">' + (index + 1) + '</td>' +
            '<td><span class="badge bg-primary">' + (evaluation.evaluation_period || 'N/A') + '</span></td>' +
            '<td><strong>' + (evaluation.employee_name || 'N/A') + '</strong></td>' +
            '<td><code>' + (evaluation.employee_code || 'N/A') + '</code></td>' +
            '<td>' + (evaluation.criteria_name || 'N/A') + '</td>' +
            '<td class="text-center"><span class="badge bg-info">' + (evaluation.criteria_weight || 'N/A') + '%</span></td>' +
            '<td class="text-center">' + (evaluation.score_badge || 'N/A') + '</td>' +
            '<td><small>' + (evaluation.created_at || 'N/A') + '</small></td>' +
            '<td class="text-center">' + (evaluation.action || 'N/A') + '</td>' +
        '</tr>';
        tbody.append(row);
    });

    $('#totalRecords').text(evaluations.length + ' records');
}

function updateStatistics(evaluations) {
    if (evaluations.length === 0) {
        $('#totalEvaluations').text('0');
        $('#avgScore').text('0');
        $('#completionRate').text('0%');
        $('#sawStatus').text('-');
        return;
    }

    $('#totalEvaluations').text(evaluations.length);

    // Calculate average score
    const scores = evaluations.map(e => parseFloat(e.score) || 0).filter(s => s > 0);
    const avgScore = scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : 0;
    $('#avgScore').text(avgScore);

    // Calculate completion rate (assuming 100% if data exists)
    $('#completionRate').text('100%');

    // SAW status
    $('#sawStatus').text('Ready');
}

function refreshTable() {
    window.currentPage = 1; // Reset to first page
    loadEvaluations();
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

    // Scroll to top of table
    $('#evaluationsTable').get(0).scrollIntoView({ behavior: 'smooth' });
}

function showRestoreModal() {
    $('#restoreModal').modal('show');
    loadDeletedEvaluations();
}

function loadDeletedEvaluations() {
    $.ajax({
        url: '<?php echo e(route("evaluations.index")); ?>',
        type: 'GET',
        data: { get_deleted: true },
        success: function(response) {
            if (response.deleted_evaluations && response.deleted_evaluations.length > 0) {
                displayDeletedEvaluations(response.deleted_evaluations);
                $('#noDeletedEvaluations').hide();
                $('#deletedEvaluationsCards').show();
            } else {
                $('#deletedEvaluationsCards').hide();
                $('#noDeletedEvaluations').show();
            }
        },
        error: function() {
            console.error('Failed to load deleted evaluations');
        }
    });
}

function displayDeletedEvaluations(evaluations) {
    const cardsContainer = $('#deletedEvaluationsCards');
    cardsContainer.empty();

    evaluations.forEach(function(evaluation) {
        const card = '<div class="col-md-6 col-lg-4">' +
            '<div class="card deleted-evaluation-card h-100" data-evaluation-id="' + evaluation.id + '">' +
                '<div class="card-header d-flex justify-content-between align-items-center py-2">' +
                    '<div class="form-check">' +
                        '<input type="checkbox" class="form-check-input evaluation-checkbox"' +
                               'value="' + evaluation.id + '" id="eval_' + evaluation.id + '"' +
                               'onchange="updateRestoreAllButton()">' +
                        '<label class="form-check-label small" for="eval_' + evaluation.id + '">' +
                            'Select' +
                        '</label>' +
                    '</div>' +
                    '<span class="badge bg-danger">' +
                        '<i class="fas fa-trash me-1"></i>Deleted' +
                    '</span>' +
                '</div>' +
                '<div class="card-body py-3">' +
                    '<div class="d-flex align-items-center mb-3">' +
                        '<div class="evaluation-icon bg-secondary me-3">' +
                            '<i class="fas fa-clipboard-check text-white"></i>' +
                        '</div>' +
                        '<div>' +
                            '<h6 class="mb-1 fw-bold">' + evaluation.employee_name + '</h6>' +
                            '<small class="text-muted">' + evaluation.employee_code + '</small>' +
                        '</div>' +
                    '</div>' +

                    '<div class="evaluation-info mb-3">' +
                        '<div class="row g-2">' +
                            '<div class="col-6">' +
                                '<small class="text-muted d-block">Criteria</small>' +
                                '<span class="fw-medium">' + evaluation.criteria_name + '</span>' +
                            '</div>' +
                            '<div class="col-6">' +
                                '<small class="text-muted d-block">Score</small>' +
                                '<span class="badge bg-' + (evaluation.score >= 80 ? 'success' : (evaluation.score >= 60 ? 'warning' : 'danger')) + '">' + evaluation.score + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="deleted-info mb-3">' +
                        '<small class="text-muted d-block">Period</small>' +
                        '<span class="fw-medium">' + evaluation.evaluation_period + '</span>' +
                    '</div>' +

                    '<div class="deleted-info mb-3">' +
                        '<small class="text-muted d-block">Deleted Date</small>' +
                        '<span class="text-danger">' +
                            '<i class="fas fa-calendar me-1"></i>' +
                            evaluation.deleted_at +
                        '</span>' +
                    '</div>' +

                    '<div class="card-actions d-flex gap-2">' +
                        '<button class="btn btn-outline-warning btn-sm flex-fill"' +
                                'onclick="restoreEvaluation([' + evaluation.id + '])"' +
                                'title="Restore Evaluation">' +
                            '<i class="fas fa-undo me-1"></i>Restore' +
                        '</button>' +
                        '<button class="btn btn-outline-danger btn-sm"' +
                                'onclick="forceDeleteEvaluation([' + evaluation.id + '])"' +
                                'title="Delete Permanently">' +
                            '<i class="fas fa-trash-alt"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        cardsContainer.append(card);
    });
    updateRestoreAllButton();
}

function updateRestoreAllButton() {
    const checkedBoxes = $('.evaluation-checkbox:checked');
    const restoreAllBtn = $('#restoreAllBtn');

    if (checkedBoxes.length > 0) {
        restoreAllBtn.prop('disabled', false);
        restoreAllBtn.html('<i class="fas fa-undo me-1"></i>Restore All (' + checkedBoxes.length + ')');
    } else {
        restoreAllBtn.prop('disabled', true);
        restoreAllBtn.html('<i class="fas fa-undo me-1"></i>Restore All');
    }
}

function toggleSelectAllDeleted() {
    const checkboxes = $('.evaluation-checkbox:checked');
    const selectAllBtn = $('button[onclick="toggleSelectAllDeleted()"]');

    if (checkboxes.length === checkboxes.filter(':checked').length) {
        // Uncheck all
        checkboxes.prop('checked', false);
        selectAllBtn.html('<i class="fas fa-square me-1"></i>Select All');
    } else {
        // Check all
        checkboxes.prop('checked', true);
        selectAllBtn.html('<i class="fas fa-check-square me-1"></i>Unselect All');
    }
    updateRestoreAllButton();
}

function restoreAllEvaluations() {
    const checkedBoxes = $('.evaluation-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const evaluationIds = checkedBoxes.map(function() {
        return $(this).val();
    }).get();

    if (confirm('Are you sure you want to restore ' + evaluationIds.length + ' evaluation(s)?')) {
        restoreEvaluations(evaluationIds);
    }
}

function restoreEvaluations(evaluationIds) {
    $.ajax({
        url: '<?php echo e(route("evaluations.restore")); ?>',
        type: 'POST',
        data: {
            evaluation_ids: evaluationIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Refresh data
                loadDeletedEvaluations();
                loadEvaluations();

                // Close modal if no more deleted evaluations
                if (response.deleted_evaluations && response.deleted_evaluations.length === 0) {
                    $('#restoreModal').modal('hide');
                }
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || 'Error occurred while restoring evaluations';
            alert('Error: ' + message);
        }
    });
}

function forceDeleteEvaluation(evaluationIds) {
    if (evaluationIds.length === 0) return;

    if (confirm('This action cannot be undone. Are you sure?')) {
        $.ajax({
            url: '<?php echo e(route("evaluations.force-delete")); ?>',
            type: 'POST',
            data: {
                evaluation_ids: evaluationIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Refresh data
                    loadDeletedEvaluations();
                    loadEvaluations();

                    // Close modal if no more deleted evaluations
                    if (response.deleted_evaluations && response.deleted_evaluations.length === 0) {
                        $('#restoreModal').modal('hide');
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Error occurred while deleting evaluations';
                alert('Error: ' + message);
            }
        });
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Page Header Styling */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.page-header h1 {
    color: white;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 0;
}

/* Statistics Cards */
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

/* Card Styling */
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border-radius: 0.75rem;
}

.card-header {
    background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
    border-bottom: 1px solid #e3e6f0;
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

.card-header h6 {
    color: #5a5c69;
    font-weight: 700;
}

/* Table Styling */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

/* Badge Styling */
.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
    border-radius: 0.5rem;
}

/* Button Styling */
.btn {
    border-radius: 0.5rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.modal-header {
    border-radius: 1rem 1rem 0 0;
    border-bottom: 1px solid #e3e6f0;
}

.modal-footer {
    border-radius: 0 0 1rem 1rem;
    border-top: 1px solid #e3e6f0;
}

/* Deleted Evaluation Cards */
.deleted-evaluation-card {
    border: 1px solid #e3e6f0;
    transition: all 0.2s ease;
}

.deleted-evaluation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.deleted-evaluation-card .card-header {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-bottom: 1px solid #ffeaa7;
}

.deleted-evaluation-card .evaluation-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.deleted-evaluation-card .evaluation-info .row {
    margin: 0;
}

.deleted-evaluation-card .evaluation-info .col-6 {
    padding: 0;
}

.deleted-evaluation-card .deleted-info {
    border-top: 1px solid #f8f9fc;
    padding-top: 1rem;
}

.deleted-evaluation-card .card-actions {
    border-top: 1px solid #f8f9fc;
    padding-top: 1rem;
}

.deleted-evaluation-card .form-check-input:checked {
    background-color: #f6c23e;
    border-color: #f6c23e;
}

.deleted-evaluation-card .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(246, 194, 62, 0.25);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        padding: 1rem;
        text-align: center;
    }

    .page-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.5s ease-out;
}

/* Pagination Styling */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #4e73df;
    border: 1px solid #e3e6f0;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    margin: 0 0.125rem;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(78, 115, 223, 0.3);
}

.pagination .page-item.active .page-link {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
    font-weight: 600;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    cursor: not-allowed;
}

.pagination .page-item.disabled .page-link:hover {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    color: #6c757d;
    transform: none;
    box-shadow: none;
}

/* Per Page Select Styling */
#perPageSelect {
    border-radius: 0.375rem;
    border: 1px solid #d1d3e2;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    background-color: white;
}

#perPageSelect:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Showing Info Styling */
#showingInfo {
    color: #5a5c69;
    font-size: 0.875rem;
}

/* Hover Effects */
.table tbody tr:hover {
    background-color: #f8f9fc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/evaluations/index.blade.php ENDPATH**/ ?>