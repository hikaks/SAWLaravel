<?php $__env->startSection('title', 'Evaluation Criteria - SAW Employee Evaluation'); ?>
<?php $__env->startSection('page-title', 'Evaluation Criteria'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <!-- Weight Status Card -->
        <div class="card mb-4 border-0" style="background: linear-gradient(135deg, <?php echo e($totalWeight == 100 ? '#10b981' : ($totalWeight < 100 ? '#f59e0b' : '#ef4444')); ?> 0%, <?php echo e($totalWeight == 100 ? '#059669' : ($totalWeight < 100 ? '#d97706' : '#dc2626')); ?> 100%);">
            <div class="card-body text-white">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-20"
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-<?php echo e($totalWeight == 100 ? 'check-circle' : ($totalWeight < 100 ? 'exclamation-triangle' : 'times-circle')); ?> fa-2x"></i>
                        </div>
                    </div>
                    <div class="col">
                        <div class="row">
                            <div class="col-md-8">
                                                <h5 class="mb-2 fw-bold">Criteria Weight Status</h5>
                <h2 class="mb-1 fw-bold"><?php echo e($totalWeight); ?>/100</h2>
                <?php if($totalWeight == 100): ?>
                    <p class="mb-0 opacity-90">
                        <i class="fas fa-star me-2"></i>
                        <strong>Perfect!</strong> Ready for SAW calculation
                    </p>
                <?php elseif($totalWeight < 100): ?>
                    <p class="mb-0 opacity-90">
                        <i class="fas fa-plus-circle me-2"></i>
                        <strong><?php echo e(100 - $totalWeight); ?> points more needed</strong> to reach 100%
                    </p>
                <?php else: ?>
                    <p class="mb-0 opacity-90">
                        <i class="fas fa-minus-circle me-2"></i>
                        <strong>Exceeds <?php echo e($totalWeight - 100); ?> points!</strong> Need to reduce
                    </p>
                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="position-relative d-inline-block mb-3">
                                        <svg width="80" height="80" class="circular-progress">
                                            <circle cx="40" cy="40" r="35" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="8"/>
                                            <circle cx="40" cy="40" r="35" fill="none" stroke="white" stroke-width="8"
                                                    stroke-dasharray="<?php echo e(2 * 3.14159 * 35); ?>"
                                                    stroke-dashoffset="<?php echo e(2 * 3.14159 * 35 * (1 - min($totalWeight, 100) / 100)); ?>"
                                                    stroke-linecap="round"
                                                    transform="rotate(-90 40 40)"/>
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <div class="fs-5 fw-bold"><?php echo e($totalWeight); ?>%</div>
                                        </div>
                                    </div>
                                    <div class="small opacity-75">
                                        Remaining: <?php echo e(max(0, 100 - $totalWeight)); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($totalWeight != 100): ?>
                    <div class="col-auto">
                        <?php if($totalWeight < 100): ?>
                        <a href="<?php echo e(route('criterias.create')); ?>" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Add Criteria
                        </a>
                        <?php else: ?>
                        <button class="btn btn-outline-light btn-lg" onclick="showWeightAdjustmentTips()">
                            <i class="fas fa-balance-scale me-2"></i>
                            Adjust Weight
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Progress Bar Alternative (Hidden on larger screens) -->
                <div class="d-md-none mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="opacity-75">Progress</small>
                        <small class="opacity-75"><?php echo e($totalWeight); ?>%</small>
                    </div>
                    <div class="progress bg-white bg-opacity-20" style="width: 8px;">
                        <div class="progress-bar bg-white" style="width: <?php echo e(min($totalWeight, 100)); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-check text-primary me-2"></i>
                    Evaluation Criteria List
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-warning btn-sm" onclick="showRestoreModal()" data-bs-toggle="tooltip" title="<?php echo e(__('Restore Deleted Criteria')); ?>">
                        <i class="fas fa-undo"></i>
                    </button>
                    <a href="<?php echo e(route('criterias.create')); ?>" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>
                        Add New Criteria
                    </a>
                    <button class="btn btn-outline-info" onclick="checkWeightStatus()">
                        <i class="fas fa-balance-scale me-1"></i>
                        Check Weight
                    </button>
                    <button class="btn btn-outline-secondary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-import me-1"></i>
                            Import
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo e(route('criterias.import-template')); ?>">
                                <i class="fas fa-download me-2"></i>Download Template
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showImportModal()">
                                <i class="fas fa-upload me-2"></i>Upload Data
                            </a></li>
                        </ul>
                    </div>
                    <?php if($totalWeight < 100): ?>
                    <a href="<?php echo e(route('criterias.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Add Criteria
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Type:</label>
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="benefit">Benefit (Higher is better)</option>
                            <option value="cost">Cost (Lower is better)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter Weight:</label>
                        <select class="form-select" id="weightFilter">
                            <option value="">All Weights</option>
                            <option value="high">High (≥ 20%)</option>
                            <option value="medium">Medium (10-19%)</option>
                            <option value="low">Low (< 10%)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sort By:</label>
                        <select class="form-select" id="sortFilter">
                            <option value="name">By Name</option>
                            <option value="weight_desc">Highest Weight</option>
                            <option value="weight_asc">Lowest Weight</option>
                            <option value="created_at">Latest</option>
                        </select>
                    </div>
                </div>

                <!-- DataTables -->
                <div class="table-responsive">
                    <table class="table table-hover" id="criteriasTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Criteria Name</th>
                                <th>Weight (%)</th>
                                <th>Type</th>
                                <th>Evaluation Count</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Criteria Information Chart -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Criteria Information & Usage Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="criteriaInfoChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="criteria-stats">
                            <div class="stat-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3">
                                        <i class="fas fa-list-ul fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="totalCriteria">0</h6>
                                        <small class="text-muted">Total Criteria</small>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3">
                                        <i class="fas fa-balance-scale fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="totalWeight">0%</h6>
                                        <small class="text-muted">Total Weight</small>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3">
                                        <i class="fas fa-arrow-up fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="benefitCriteria">0</h6>
                                        <small class="text-muted">Benefit Criteria</small>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3">
                                        <i class="fas fa-arrow-down fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="costCriteria">0</h6>
                                        <small class="text-muted">Cost Criteria</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    How to Use Criteria
                </h6>
            </div>
            <div class="card-body">
                <div class="usage-guide">
                    <div class="guide-step mb-3">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6 class="mb-1">Define Criteria</h6>
                            <p class="text-muted small mb-0">Create evaluation criteria with clear names and types</p>
                        </div>
                    </div>

                    <div class="guide-step mb-3">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6 class="mb-1">Set Weights</h6>
                            <p class="text-muted small mb-0">Assign importance percentage (total must equal 100%)</p>
                        </div>
                    </div>

                    <div class="guide-step mb-3">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6 class="mb-1">Choose Type</h6>
                            <p class="text-muted small mb-0">Benefit: Higher is better, Cost: Lower is better</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h6 class="mb-1">Ready for SAW</h6>
                            <p class="text-muted small mb-0">When total weight reaches 100%, start evaluations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Deleted Criteria Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">
                    <i class="fas fa-undo me-2 text-warning"></i>
                    <?php echo e(__('Restore Deleted Criteria')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo e(__('This will show all criteria that have been deleted. You can restore them one by one or restore all at once.')); ?>

                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><?php echo e(__('Deleted Criteria')); ?></h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-warning btn-sm" onclick="restoreAllCriteria()" id="restoreAllBtn" disabled>
                            <i class="fas fa-undo me-1"></i>
                            <?php echo e(__('Restore All')); ?>

                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshRestoreList()">
                            <i class="fas fa-sync-alt me-1"></i>
                            <?php echo e(__('Refresh')); ?>

                        </button>
                    </div>
                </div>

                <div id="deletedCriteriaCards" class="row g-3">
                    <!-- Deleted criteria will be displayed as cards here -->
                </div>

                <div id="noDeletedCriteria" class="text-center py-4" style="display: none;">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6 class="text-success"><?php echo e(__('No Deleted Criteria')); ?></h6>
                    <p class="text-muted"><?php echo e(__('All criteria are currently active.')); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo e(__('Close')); ?>

                </button>
            </div>
        </div>
    </div>
</div>

<!-- Criteria Detail Modal -->
<div class="modal fade" id="criteriaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criteria Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="criteriaModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    <i class="fas fa-file-import me-2"></i>Import Criteria
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('criterias.import')); ?>" method="POST" enctype="multipart/form-data" id="importForm">
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
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download the template first to see the required format</li>
                            <li>Total weight of all criteria must equal 100%</li>
                            <li>Type must be either "benefit" or "cost"</li>
                            <li>Existing criteria will be updated if found</li>
                            <li><strong>This will replace ALL existing criteria!</strong></li>
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
let criteriasTable;

$(document).ready(function() {
    // Wait for DataTables to be loaded
    $(document).on('datatables-ready', function() {
        // Initialize DataTable
        criteriasTable = $('#criteriasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?php echo e(route('criterias.index')); ?>",
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
            // Initialize tooltips for dropdown items
            $('[data-bs-toggle="tooltip"]').tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        },
        // Disable animations and effects
        deferRender: true,
        scroller: false
    });

    // Filter change events
    $('#typeFilter, #weightFilter, #sortFilter').change(function() {
        criteriasTable.ajax.reload();
    });

    // Initialize criteria info chart
    if (typeof Chart !== 'undefined') {
        window.criteriaInfoChart = initCriteriaInfoChart();
        loadCriteriaInfo();

        // Refresh chart when table is updated
        criteriasTable.on('draw', function() {
            loadCriteriaInfo();
        });
    }
    });
});

// Chart functions removed - table functionality restored

// Initialize criteria info chart
function initCriteriaInfoChart() {
    const ctx = document.getElementById('criteriaInfoChart');
    if (!ctx) return;

    // Create simple doughnut chart
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Benefit Criteria', 'Cost Criteria'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#10b981', '#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' criteria';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 1000
            }
        }
    });

    return chart;
}

// Load criteria information
function loadCriteriaInfo() {
    $.get("<?php echo e(route('criterias.index')); ?>", { get_chart_info: true })
        .done(function(data) {
            // Update chart
            if (window.criteriaInfoChart) {
                window.criteriaInfoChart.data.datasets[0].data = [data.benefit_criteria, data.cost_criteria];
                window.criteriaInfoChart.update();
            }

            // Update statistics
            $('#totalCriteria').text(data.total_criteria);
            $('#totalWeight').text(data.total_weight + '%');
            $('#benefitCriteria').text(data.benefit_criteria);
            $('#costCriteria').text(data.cost_criteria);

            // Update chart colors based on status
            updateChartColors(data.status);
        })
        .fail(function() {
            console.log('Failed to load criteria info');
        });
}

// Update chart colors based on status
function updateChartColors(status) {
    if (!window.criteriaInfoChart) return;

    let colors = ['#10b981', '#f59e0b']; // Default: green, yellow

    if (status === 'Complete') {
        colors = ['#10b981', '#10b981']; // All green
    } else if (status === 'Overweight') {
        colors = ['#ef4444', '#ef4444']; // All red
    }

    window.criteriaInfoChart.data.datasets[0].backgroundColor = colors;
    window.criteriaInfoChart.update();
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
    $.get("<?php echo e(route('criterias.index')); ?>", { get_deleted: true })
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
        const card = `
            <div class="col-md-6 col-lg-4">
                <div class="card deleted-criteria-card" data-criteria-id="${criterion.id}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input criteria-checkbox"
                                   value="${criterion.id}" id="crit_${criterion.id}"
                                   onchange="updateRestoreAllButton()">
                            <label class="form-check-label small" for="crit_${criterion.id}">
                                <?php echo e(__('Select')); ?>

                            </label>
                        </div>
                        <span class="badge bg-danger">
                            <i class="fas fa-trash me-1"></i><?php echo e(__('Deleted')); ?>

                        </span>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="criteria-icon bg-secondary me-3">
                                <i class="fas fa-list-check text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">${criterion.name}</h6>
                                <span class="badge bg-${criterion.type === 'benefit' ? 'success' : 'warning'}">${criterion.type}</span>
                            </div>
                        </div>

                        <div class="criteria-info mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block"><?php echo e(__('Weight')); ?></small>
                                    <span class="fw-medium">${criterion.weight}%</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block"><?php echo e(__('Type')); ?></small>
                                    <span class="fw-medium">${criterion.type}</span>
                                </div>
                            </div>
                        </div>

                        <div class="deleted-info mb-3">
                            <small class="text-muted d-block"><?php echo e(__('Deleted Date')); ?></small>
                            <span class="text-danger">
                                <i class="fas fa-calendar me-1"></i>
                                ${criterion.deleted_at}
                            </span>
                        </div>

                        <div class="card-actions d-flex gap-2">
                            <button class="btn btn-outline-warning btn-sm flex-fill"
                                    onclick="restoreCriteria([${criterion.id}])"
                                    title="<?php echo e(__('Restore Criteria')); ?>">
                                <i class="fas fa-undo me-1"></i><?php echo e(__('Restore')); ?>

                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="forceDeleteCriteria([${criterion.id}])"
                                    title="<?php echo e(__('Delete Permanently')); ?>">
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

function restoreAllCriteria() {
    const checkedBoxes = $('.criteria-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const criteriaIds = checkedBoxes.map(function() {
        return $(this).val();
    }).get();

    Swal.fire({
        title: '<?php echo e(__("Restore Criteria")); ?>',
        text: `<?php echo e(__("Are you sure you want to restore")); ?> ${criteriaIds.length} <?php echo e(__("criteria")); ?>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<?php echo e(__("Yes, restore them!")); ?>',
        cancelButtonText: '<?php echo e(__("Cancel")); ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreCriteria(criteriaIds);
        }
    });
}

function restoreCriteria(criteriaIds) {
    $.ajax({
        url: "<?php echo e(route('criterias.restore')); ?>",
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            criteria_ids: criteriaIds
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '<?php echo e(__("Restored!")); ?>',
                    text: response.message || `<?php echo e(__("Successfully restored")); ?> ${criteriaIds.length} <?php echo e(__("criteria")); ?>`,
                    timer: 3000,
                    showConfirmButton: false
                });

                // Refresh data
                loadDeletedCriteria();
                criteriasTable.ajax.reload();

                // Close modal if no more deleted criteria
                if (response.deleted_criteria && response.deleted_criteria.length === 0) {
                    $('#restoreModal').modal('hide');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<?php echo e(__("Error")); ?>',
                    text: response.message || '<?php echo e(__("Something went wrong")); ?>'
                });
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || '<?php echo e(__("Error occurred while restoring criteria")); ?>';
            Swal.fire({
                icon: 'error',
                title: '<?php echo e(__("Error")); ?>',
                text: message
            });
        }
    });
}

function forceDeleteCriteria(criteriaIds) {
    if (criteriaIds.length === 0) return;

    Swal.fire({
        title: '<?php echo e(__("Permanently Delete Criteria")); ?>',
        text: `<?php echo e(__("This action cannot be undone! Are you sure you want to permanently delete")); ?> ${criteriaIds.length} <?php echo e(__("criteria")); ?>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<?php echo e(__("Yes, delete permanently!")); ?>',
        cancelButtonText: '<?php echo e(__("Cancel")); ?>',
        showDenyButton: true,
        denyButtonText: '<?php echo e(__("No, keep them")); ?>',
        denyButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?php echo e(route('criterias.force-delete')); ?>",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    criteria_ids: criteriaIds
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '<?php echo e(__("Permanently Deleted!")); ?>',
                            text: response.message || `<?php echo e(__("Successfully permanently deleted")); ?> ${criteriaIds.length} <?php echo e(__("criteria")); ?>`,
                            timer: 3000,
                            showConfirmButton: false
                        });

                        // Refresh data
                        loadDeletedCriteria();
                        criteriasTable.ajax.reload();

                        // Close modal if no more deleted criteria
                        if (response.deleted_criteria && response.deleted_criteria.length === 0) {
                            $('#restoreModal').modal('hide');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '<?php echo e(__("Error")); ?>',
                            text: response.message || '<?php echo e(__("Something went wrong")); ?>'
                        });
                    }
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || '<?php echo e(__("Error occurred while permanently deleting criteria")); ?>';
                    Swal.fire({
                        icon: 'error',
                        title: '<?php echo e(__("Error")); ?>',
                        text: message
                    });
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
    Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        icon: 'success',
        title: message
    });
}

function showError(message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        icon: 'error',
        title: message
    });
}

function showLoading() {
    Swal.fire({
        title: '<?php echo e(__("Loading...")); ?>',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

function checkWeightStatus() {
    $.get("<?php echo e(route('criterias.total-weight')); ?>", function(response) {
        const totalWeight = response.total_weight;
        const remaining = response.remaining_weight;
        const isComplete = response.is_complete;

        let alertClass = isComplete ? 'success' : (totalWeight < 100 ? 'warning' : 'danger');
        let message = `Total weight: ${totalWeight}% `;

        if (isComplete) {
            message += '- Perfect! Ready for SAW calculation.';
        } else if (totalWeight < 100) {
            message += `- ${remaining}% more needed.`;
        } else {
            message += `- Exceeds ${totalWeight - 100}%! Need to reduce.`;
        }

        Swal.fire({
            icon: isComplete ? 'success' : 'warning',
            title: 'Criteria Weight Status',
            html: `
                <div class="text-start">
                    <p><strong>Total Weight:</strong> ${totalWeight}%</p>
                    <p><strong>Remaining Weight:</strong> ${remaining}%</p>
                    <p><strong>Status:</strong> ${message}</p>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-${alertClass}" style="width: ${Math.min(totalWeight, 100)}%">
                            ${totalWeight}%
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: 'OK'
        });
    });
}

function deleteCriteria(id) {
    Swal.fire({
        title: '<?php echo e(__("Are you sure?")); ?>',
        text: '<?php echo e(__("This action cannot be undone")); ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<?php echo e(__("Yes, delete it!")); ?>',
        cancelButtonText: '<?php echo e(__("Cancel")); ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/criterias/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
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
                    let message = xhr.responseJSON?.message || '<?php echo e(__("Error occurred while deleting criteria")); ?>';
                    showError(message);
                }
            });
        }
    });
}

function viewCriteria(id) {
    showLoading();

    $.get(`/criterias/${id}`, function(data) {
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
                <h6 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>How to Reduce Total Weight:</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-edit text-warning me-2"></i>Edit criteria with high weight to reduce it</li>
                    <li class="mb-2"><i class="fas fa-trash text-danger me-2"></i>Delete less important criteria</li>
                    <li class="mb-2"><i class="fas fa-balance-scale text-info me-2"></i>Redistribute weight evenly</li>
                </ul>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <small><strong>Important:</strong> Total weight must be exactly 100% to run SAW calculation.</small>
                </div>
            </div>
        `,
        confirmButtonText: 'Understand',
        confirmButtonColor: '#0d6efd',
        width: '500px'
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
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
    animation: dropdownFadeIn 0.2s ease-out;
    transform-origin: top right;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8fafc;
    color: #1e293b;
    transform: translateX(2px);
}

.dropdown-item.text-danger:hover {
    background-color: #fef2f2;
    color: #dc2626;
    transform: translateX(2px);
}

.dropdown-divider {
    margin: 0.5rem 0;
    border-color: #e5e7eb;
}

/* Action button styling */
.btn-outline-secondary {
    border-color: #d1d5db;
    color: #6b7280;
    transition: all 0.2s ease;
}

.btn-outline-secondary:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #374151;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-outline-secondary:focus {
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
}

/* Criteria Info Chart Styling */
.criteria-stats .stat-item {
    padding: 15px;
    border-radius: 8px;
    background: #f8fafc;
    border-left: 4px solid #e5e7eb;
    transition: all 0.3s ease;
}

.criteria-stats .stat-item:hover {
    background: #f1f5f9;
    border-left-color: #3b82f6;
    transform: translateX(5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(59, 130, 246, 0.1);
}

/* Usage Guide Styling */
.usage-guide .guide-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.step-number {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    flex-shrink: 0;
}

.step-content h6 {
    color: #1f2937;
    margin-bottom: 5px;
}

        .step-content p {
            color: #6b7280;
            line-height: 1.4;
        }

        /* Deleted Criteria Cards Styling */
        .deleted-criteria-card {
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .deleted-criteria-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            border-color: #d1d5db;
        }

        .deleted-criteria-card .card-header {
            background-color: #fef2f2;
            border-bottom: 1px solid #fecaca;
        }

        .deleted-criteria-card .criteria-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .deleted-criteria-card .criteria-info .row {
            margin: 0;
        }

        .deleted-criteria-card .criteria-info .col-6 {
            padding: 0.25rem;
        }

        .deleted-criteria-card .deleted-info {
            padding: 0.5rem;
            background-color: #fef2f2;
            border-radius: 0.375rem;
            border-left: 3px solid #dc2626;
        }

        .deleted-criteria-card .card-actions {
            margin-top: auto;
        }

        .deleted-criteria-card .form-check-input:checked {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }

        .deleted-criteria-card .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        }

/* Circular Progress - No Animation */
.circular-progress circle:nth-child(2) {
    /* No transitions or animations */
}

/* Card Styling - No Effects */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Weight Status Card Enhancements */
.card-body .row.align-items-center {
    min-height: 120px;
}

.bg-opacity-20 {
    background-color: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(10px);
}

/* Button Enhancements */
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
    border-radius: 0.5rem;
    font-weight: 600;
}

/* Progress Bar Styling - No Effects */
.progress {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    overflow: hidden;
}

.progress-bar {
    border-radius: 0.5rem;
}

/* Table Enhancements */
.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* Badge Styling */
.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 600;
}

/* Button Group - Removed as we now use dropdown */

/* Chart Container */
#weightChart {
    max-height: 300px;
    transition: opacity 0.3s ease;
    position: relative;
    z-index: 1;
}

/* Chart loading animation */
.chart-loading {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}



/* Mobile Responsive */
@media (max-width: 768px) {
    .circular-progress {
        width: 60px !important;
        height: 60px !important;
    }

    .circular-progress circle {
        r: 25 !important;
        cx: 30 !important;
        cy: 30 !important;
    }

    .fs-5 {
        font-size: 0.9rem !important;
    }

    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

/* Alert Enhancements */
.alert {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Icon Styling - No Effects */
.fas {
    /* No transitions or effects */
}

/* Status Icons */
.fa-check-circle {
    color: rgba(255, 255, 255, 0.9);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.fa-exclamation-triangle {
    color: rgba(255, 255, 255, 0.9);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.fa-times-circle {
    color: rgba(255, 255, 255, 0.9);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}
</style>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/criterias/index.blade.php ENDPATH**/ ?>