<?php $__env->startSection('title', __('What-if Scenarios') . ' - ' . __('SAW Employee Evaluation')); ?>
<?php $__env->startSection('page-title', __('What-if Scenarios')); ?>

<?php $__env->startSection('content'); ?>
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?php echo e(__('What-if Scenarios')); ?></h1>
        <p class="text-muted mb-0"><?php echo e(__('Create and compare different evaluation scenarios')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="resetScenarios()">
            <i class="fas fa-undo me-1"></i>
            <?php echo e(__('Reset')); ?>

        </button>
        <button class="btn btn-outline-info" onclick="exportResults()">
            <i class="fas fa-download me-1"></i>
            <?php echo e(__('Export Results')); ?>

        </button>
        <a href="<?php echo e(route('analysis.index')); ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>
            <?php echo e(__('Back to Analysis')); ?>

        </a>
    </div>
</div>

<!-- Scenario Configuration -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    <?php echo e(__('Scenario Configuration')); ?>

                </h6>
            </div>
            <div class="card-body">
                <form id="scenarioForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Evaluation Period')); ?></label>
                        <select class="form-select" name="evaluation_period" id="evaluationPeriod" required>
                            <?php $__currentLoopData = $availablePeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($period); ?>" <?php echo e($period == $selectedPeriod ? 'selected' : ''); ?>>
                                    <?php echo e($period); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Scenario Type')); ?></label>
                        <select class="form-select" name="scenario_type" id="scenarioType">
                            <option value="weight_changes"><?php echo e(__('Weight Changes')); ?></option>
                            <option value="score_changes"><?php echo e(__('Score Changes')); ?></option>
                            <option value="criteria_changes"><?php echo e(__('Criteria Changes')); ?></option>
                        </select>
                    </div>

                    <div id="scenarioConfiguration">
                        <!-- Dynamic scenario configuration will be loaded here -->
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning" id="runScenarioBtn">
                            <i class="fas fa-play me-1"></i>
                            <?php echo e(__('Run Scenario')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Loading State -->
        <div id="loadingResults" style="display: none;">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-warning mb-3" role="status">
                        <span class="visually-hidden"><?php echo e(__('Loading...')); ?></span>
                    </div>
                    <h5><?php echo e(__('Running What-if Analysis...')); ?></h5>
                    <p class="text-muted"><?php echo e(__('Please wait while we process your scenarios')); ?></p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="scenarioResults" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e(__('Scenario Results')); ?></h6>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewType" id="tableView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="tableView">
                            <i class="fas fa-table me-1"></i><?php echo e(__('Table')); ?>

                        </label>

                        <input type="radio" class="btn-check" name="viewType" id="chartView" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="chartView">
                            <i class="fas fa-chart-bar me-1"></i><?php echo e(__('Chart')); ?>

                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="resultsContent">
                        <!-- Results will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scenario Comparison -->
<div id="scenarioComparison" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-balance-scale me-2"></i>
                <?php echo e(__('Scenario Comparison')); ?>

            </h6>
        </div>
        <div class="card-body">
            <div id="comparisonContent">
                <!-- Comparison results will be displayed here -->
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
.scenario-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.scenario-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.scenario-card.selected {
    border-color: #ffc107;
    background-color: #fff3cd;
}

.weight-slider {
    margin: 10px 0;
}

.comparison-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.rank-change {
    font-weight: bold;
}

.rank-change.improved {
    color: #28a745;
}

.rank-change.declined {
    color: #dc3545;
}

.rank-change.unchanged {
    color: #6c757d;
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let scenarioResults = null;
let scenarios = [];

$(document).ready(function() {
    loadScenarioConfiguration();
    
    $('#scenarioType').change(function() {
        loadScenarioConfiguration();
    });
    
    // Event listener untuk criteriaAction
    $(document).on('change', '#criteriaAction', function() {
        loadCriteriaOptions();
    });
    
    $('#scenarioForm').submit(function(e) {
        e.preventDefault();
        runWhatIfAnalysis();
    });
    
    $('input[name="viewType"]').change(function() {
        displayResults();
    });
});

function loadScenarioConfiguration() {
    const scenarioType = $('#scenarioType').val();
    let html = '';
    
    switch(scenarioType) {
        case 'weight_changes':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Modify Criteria Weights')); ?></label>
                    <?php $__currentLoopData = $criterias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criteria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-2">
                        <label class="form-label small"><?php echo e($criteria->name); ?></label>
                        <div class="input-group">
                            <input type="range" class="form-range me-2" 
                                   min="1" max="50" 
                                   value="<?php echo e($criteria->weight); ?>" 
                                   id="weight_<?php echo e($criteria->id); ?>"
                                   oninput="syncWeightInputs(<?php echo e($criteria->id); ?>, 'range')">
                            <input type="number" class="form-control" 
                                   style="width: 80px;"
                                   min="1" max="100" 
                                   value="<?php echo e($criteria->weight); ?>" 
                                   id="weightValue_<?php echo e($criteria->id); ?>"
                                   oninput="syncWeightInputs(<?php echo e($criteria->id); ?>, 'number')">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            `;
            break;
            
        case 'score_changes':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Employee')); ?></label>
                    <select class="form-select" id="targetEmployee">
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Score Modification')); ?></label>
                    <input type="number" class="form-control" id="scoreChange" 
                           placeholder="Enter percentage change (e.g., +10 or -5)" min="-50" max="50">
                </div>
            `;
            break;
            
        case 'criteria_changes':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Action')); ?></label>
                    <select class="form-select" id="criteriaAction">
                        <option value="remove"><?php echo e(__('Remove Criteria')); ?></option>
                        <option value="add"><?php echo e(__('Add New Criteria')); ?></option>
                        <option value="modify"><?php echo e(__('Modify Criteria Weight')); ?></option>
                    </select>
                </div>
                <div id="criteriaOptions">
                    <!-- Dynamic options based on action -->
                </div>
            `;
            break;
    }
    
    $('#scenarioConfiguration').html(html);
    
    // Load criteria options if criteria_changes is selected
    if (scenarioType === 'criteria_changes') {
        setTimeout(loadCriteriaOptions, 100);
    }
}

function runWhatIfAnalysis() {
    const formData = new FormData($('#scenarioForm')[0]);
    const scenarioType = $('#scenarioType').val();
    
    $('#scenarioResults').hide();
    $('#loadingResults').show();
    $('#runScenarioBtn').prop('disabled', true);
    
    let requestData;
    try {
        requestData = {
            evaluation_period: formData.get('evaluation_period'),
            scenarios: [{
                name: 'Scenario 1',
                type: scenarioType,
                changes: getScenarioChanges(scenarioType)
            }]
        };
    } catch (error) {
        $('#loadingResults').hide();
        $('#runScenarioBtn').prop('disabled', false);
        showError('Invalid scenario configuration: ' + error.message);
        return;
    }
    
    $.ajax({
        url: '<?php echo e(route("analysis.what-if")); ?>',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#loadingResults').hide();
            $('#runScenarioBtn').prop('disabled', false);
            
            if (response.success) {
                scenarioResults = response.data;
                
                // Check if there are errors in the results
                if (scenarioResults._has_errors) {
                    showError('Analysis completed with some errors', scenarioResults);
                } else {
                    displayResults();
                    displayScenarioComparison();
                    $('#scenarioResults').show();
                    $('#scenarioComparison').show();
                }
            } else {
                showError('Analysis failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            $('#loadingResults').hide();
            $('#runScenarioBtn').prop('disabled', false);
            
            let errorMessage = 'Analysis failed';
            let errorDetails = '';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Handle validation errors
                if (xhr.responseJSON.errors) {
                    errorDetails = '<ul class="mb-0 mt-2">';
                    Object.keys(xhr.responseJSON.errors).forEach(field => {
                        xhr.responseJSON.errors[field].forEach(error => {
                            errorDetails += `<li>${error}</li>`;
                        });
                    });
                    errorDetails += '</ul>';
                }
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            } else if (xhr.status === 404) {
                errorMessage = 'Analysis endpoint not found.';
            } else if (xhr.status === 0) {
                errorMessage = 'Network error. Please check your connection.';
            }
            
            showError(errorMessage + errorDetails);
        }
    });
}

function syncWeightInputs(criteriaId, sourceType) {
    if (sourceType === 'range') {
        const rangeValue = $('#weight_' + criteriaId).val();
        $('#weightValue_' + criteriaId).val(rangeValue);
    } else if (sourceType === 'number') {
        const numberValue = $('#weightValue_' + criteriaId).val();
        // Ensure the range slider stays within its limits
        const clampedValue = Math.max(1, Math.min(50, numberValue));
        $('#weight_' + criteriaId).val(clampedValue);
    }
}

function loadCriteriaOptions() {
    const action = $('#criteriaAction').val();
    let html = '';
    
    switch(action) {
        case 'remove':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Select Criteria to Remove')); ?></label>
                    <select class="form-select" id="criteriaToRemove" required>
                        <option value=""><?php echo e(__('Choose criteria...')); ?></option>
                        <?php $__currentLoopData = $criterias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criteria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($criteria->id); ?>"><?php echo e($criteria->name); ?> (<?php echo e($criteria->weight); ?>%)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            `;
            break;
            
        case 'add':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('New Criteria Name')); ?></label>
                    <input type="text" class="form-control" id="newCriteriaName" 
                           placeholder="<?php echo e(__('Enter criteria name')); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Weight')); ?> (%)</label>
                    <input type="number" class="form-control" id="newCriteriaWeight" 
                           min="1" max="100" value="10" required>
                    <div class="form-text"><?php echo e(__('Note: Total weights will be normalized automatically')); ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Criteria Type')); ?></label>
                    <select class="form-select" id="newCriteriaType" required>
                        <option value="benefit"><?php echo e(__('Benefit (Higher is Better)')); ?></option>
                        <option value="cost"><?php echo e(__('Cost (Lower is Better)')); ?></option>
                    </select>
                </div>
            `;
            break;
            
        case 'modify':
            html = `
                <div class="mb-3">
                    <label class="form-label"><?php echo e(__('Select Criteria to Modify')); ?></label>
                    <select class="form-select" id="criteriaToModify" required>
                        <option value=""><?php echo e(__('Choose criteria...')); ?></option>
                        <?php $__currentLoopData = $criterias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criteria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($criteria->id); ?>" data-current-weight="<?php echo e($criteria->weight); ?>"><?php echo e($criteria->name); ?> (<?php echo e($criteria->weight); ?>%)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3" id="modifyWeightSection" style="display: none;">
                    <label class="form-label"><?php echo e(__('New Weight')); ?> (%)</label>
                    <div class="input-group">
                        <input type="range" class="form-range me-2" 
                               min="1" max="100" 
                               id="modifyWeightRange"
                               oninput="syncModifyWeightInputs('range')">
                        <input type="number" class="form-control" 
                               style="width: 80px;"
                               min="1" max="100" 
                               id="modifyWeightValue"
                               oninput="syncModifyWeightInputs('number')" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text"><?php echo e(__('Current weight: ')); ?><span id="currentWeight"></span>%</div>
                </div>
            `;
            break;
    }
    
    $('#criteriaOptions').html(html);
    
    // Add event listener for modify criteria selection
    if (action === 'modify') {
        $(document).on('change', '#criteriaToModify', function() {
            const selectedOption = $(this).find('option:selected');
            const currentWeight = selectedOption.data('current-weight');
            
            if (currentWeight) {
                $('#currentWeight').text(currentWeight);
                $('#modifyWeightRange').val(currentWeight);
                $('#modifyWeightValue').val(currentWeight);
                $('#modifyWeightSection').show();
            } else {
                $('#modifyWeightSection').hide();
            }
        });
    }
}

function syncModifyWeightInputs(source) {
    if (source === 'range') {
        const value = $('#modifyWeightRange').val();
        $('#modifyWeightValue').val(value);
    } else {
        const value = $('#modifyWeightValue').val();
        $('#modifyWeightRange').val(value);
    }
}

function getScenarioChanges(scenarioType) {
    switch(scenarioType) {
        case 'weight_changes':
            let changes = {};
            let hasValidChanges = false;
            <?php $__currentLoopData = $criterias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criteria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                const weight<?php echo e($criteria->id); ?> = parseInt($('#weightValue_<?php echo e($criteria->id); ?>').val());
                if (!isNaN(weight<?php echo e($criteria->id); ?>) && weight<?php echo e($criteria->id); ?> > 0) {
                    changes[<?php echo e($criteria->id); ?>] = weight<?php echo e($criteria->id); ?>;
                    hasValidChanges = true;
                }
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            if (!hasValidChanges) {
                throw new Error('No valid weight changes specified');
            }
            
            return changes;
            
        case 'score_changes':
            return {
                employee_id: $('#targetEmployee').val(),
                score_change: parseFloat($('#scoreChange').val())
            };
            
        case 'criteria_changes':
            const action = $('#criteriaAction').val();
            let criteriaChanges = { action: action };
            
            switch(action) {
                case 'remove':
                    const criteriaToRemove = $('#criteriaToRemove').val();
                    if (!criteriaToRemove) {
                        throw new Error('Please select a criteria to remove');
                    }
                    criteriaChanges.criteria_id = parseInt(criteriaToRemove);
                     break;
                     
                 case 'add':
                     const newName = $('#newCriteriaName').val().trim();
                     const newWeight = parseInt($('#newCriteriaWeight').val());
                     const newType = $('#newCriteriaType').val();
                     
                     if (!newName) {
                         throw new Error('Please enter criteria name');
                     }
                     if (!newWeight || newWeight < 1 || newWeight > 100) {
                         throw new Error('Please enter valid weight (1-100)');
                     }
                     
                     criteriaChanges.name = newName;
                     criteriaChanges.weight = newWeight;
                     criteriaChanges.type = newType;
                     break;
                     
                 case 'modify':
                     const criteriaToModify = $('#criteriaToModify').val();
                     const newWeightValue = parseInt($('#modifyWeightValue').val());
                     
                     if (!criteriaToModify) {
                         throw new Error('Please select a criteria to modify');
                     }
                     if (!newWeightValue || newWeightValue < 1 || newWeightValue > 100) {
                         throw new Error('Please enter valid weight (1-100)');
                     }
                     
                     criteriaChanges.criteria_id = parseInt(criteriaToModify);
                     criteriaChanges.weight = newWeightValue;
                     break;
             }
             
             return criteriaChanges;
            
        default:
            return {};
    }
}

function displayResults() {
    if (!scenarioResults) return;
    
    const viewType = $('input[name="viewType"]:checked').attr('id');
    
    if (viewType === 'tableView') {
        showResultsTable();
    } else {
        showResultsChart();
    }
}

function showResultsTable() {
    if (!scenarioResults) {
        $('#resultsContent').html('<p class="text-muted">No results to display</p>');
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr><th>Employee</th><th>Original Rank</th><th>New Rank</th><th>Score Change</th><th>Rank Change</th></tr></thead>';
    html += '<tbody>';
    
    // Process scenario results
    Object.keys(scenarioResults).forEach(scenarioName => {
        const scenario = scenarioResults[scenarioName];
        
        // Check for different impact types
        let impactData = null;
        if (scenario.weight_impact) {
            impactData = scenario.weight_impact;
        } else if (scenario.score_impact) {
            impactData = scenario.score_impact;
        } else if (scenario.criteria_impact) {
            impactData = scenario.criteria_impact;
        }
        
        if (impactData && Array.isArray(impactData)) {
            impactData.forEach(employee => {
                const originalRank = employee.original_ranking || 'N/A';
                const newRank = employee.ranking || 'N/A';
                const scoreChange = employee.score_change ? employee.score_change.toFixed(3) : '0.000';
                const rankChange = originalRank !== 'N/A' && newRank !== 'N/A' ? (originalRank - newRank) : 0;
                const rankChangeClass = rankChange > 0 ? 'text-success' : rankChange < 0 ? 'text-danger' : 'text-muted';
                const rankChangeIcon = rankChange > 0 ? 'fa-arrow-up' : rankChange < 0 ? 'fa-arrow-down' : 'fa-minus';
                
                html += `<tr>`;
                html += `<td>${employee.employee ? employee.employee.name : 'Unknown'}</td>`;
                html += `<td>${originalRank}</td>`;
                html += `<td>${newRank}</td>`;
                html += `<td>${scoreChange}</td>`;
                html += `<td class="${rankChangeClass}"><i class="fas ${rankChangeIcon} me-1"></i>${Math.abs(rankChange)}</td>`;
                html += `</tr>`;
            });
        }
    });
    
    html += '</tbody></table></div>';
    $('#resultsContent').html(html);
}

function showResultsChart() {
    if (!scenarioResults) {
        $('#resultsContent').html('<p class="text-muted">No results to display</p>');
        return;
    }
    
    $('#resultsContent').html('<canvas id="scenarioChart" width="400" height="200"></canvas>');
    
    // Prepare chart data
    const employees = [];
    const originalScores = [];
    const newScores = [];
    const scoreChanges = [];
    
    // Process scenario results
    Object.keys(scenarioResults).forEach(scenarioName => {
        const scenario = scenarioResults[scenarioName];
        
        // Check for different impact types
        let impactData = null;
        if (scenario.weight_impact) {
            impactData = scenario.weight_impact;
        } else if (scenario.score_impact) {
            impactData = scenario.score_impact;
        } else if (scenario.criteria_impact) {
            impactData = scenario.criteria_impact;
        }
        
        if (impactData && Array.isArray(impactData)) {
            impactData.forEach(employee => {
                employees.push(employee.employee ? employee.employee.name : 'Unknown');
                originalScores.push(employee.original_score ? (employee.original_score * 100).toFixed(1) : 0);
                newScores.push(employee.total_score ? (employee.total_score * 100).toFixed(1) : 0);
                scoreChanges.push(employee.score_change ? (employee.score_change * 100).toFixed(1) : 0);
            });
        }
    });
    
    // Create chart
    const ctx = document.getElementById('scenarioChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: employees,
            datasets: [{
                label: 'Original Score (%)',
                data: originalScores,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'New Score (%)',
                data: newScores,
                backgroundColor: 'rgba(255, 193, 7, 0.6)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });
}

function showError(message, errors = null) {
    let alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> ${message}
    `;
    
    // Add detailed errors if available
    if (errors && typeof errors === 'object') {
        alertHtml += '<div class="mt-2"><strong>Details:</strong>';
        if (errors._errors) {
            Object.keys(errors._errors).forEach(scenarioName => {
                alertHtml += `<div class="mt-1"><em>Scenario "${scenarioName}":</em>`;
                Object.keys(errors._errors[scenarioName]).forEach(errorType => {
                    alertHtml += `<div class="ms-3">• ${errorType}: ${errors._errors[scenarioName][errorType]}</div>`;
                });
                alertHtml += '</div>';
            });
        }
        alertHtml += '</div>';
    }
    
    alertHtml += `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#scenarioResults').html(alertHtml).show();
}

function resetScenarios() {
    $('#scenarioForm')[0].reset();
    $('#scenarioResults').hide();
    $('#scenarioComparison').hide();
    loadScenarioConfiguration();
}

function displayScenarioComparison() {
    if (!scenarioResults) {
        $('#comparisonContent').html('<p class="text-muted">No comparison data available</p>');
        return;
    }
    
    let html = '<div class="row">';
    
    // Summary Statistics
    html += '<div class="col-md-6">';
    html += '<div class="card border-0 bg-light">';
    html += '<div class="card-body">';
    html += '<h6 class="card-title"><i class="fas fa-chart-line me-2"></i>Impact Summary</h6>';
    
    let totalEmployees = 0;
    let avgScoreChange = 0;
    let rankChanges = { improved: 0, declined: 0, unchanged: 0 };
    
    // Calculate summary statistics
    Object.keys(scenarioResults).forEach(scenarioName => {
        const scenario = scenarioResults[scenarioName];
        let impactData = scenario.weight_impact || scenario.score_impact || scenario.criteria_impact;
        
        if (impactData && Array.isArray(impactData)) {
            totalEmployees = impactData.length;
            let totalScoreChange = 0;
            
            impactData.forEach(employee => {
                const scoreChange = employee.score_change || 0;
                totalScoreChange += scoreChange;
                
                const originalRank = employee.original_ranking;
                const newRank = employee.ranking;
                
                if (originalRank && newRank) {
                    const rankChange = originalRank - newRank;
                    if (rankChange > 0) rankChanges.improved++;
                    else if (rankChange < 0) rankChanges.declined++;
                    else rankChanges.unchanged++;
                }
            });
            
            avgScoreChange = totalEmployees > 0 ? (totalScoreChange / totalEmployees) : 0;
        }
    });
    
    html += `<div class="mb-2"><strong>Total Employees:</strong> ${totalEmployees}</div>`;
    html += `<div class="mb-2"><strong>Avg Score Change:</strong> ${(avgScoreChange * 100).toFixed(2)}%</div>`;
    html += `<div class="mb-2"><strong>Rank Improvements:</strong> <span class="text-success">${rankChanges.improved}</span></div>`;
    html += `<div class="mb-2"><strong>Rank Declines:</strong> <span class="text-danger">${rankChanges.declined}</span></div>`;
    html += `<div class="mb-2"><strong>Unchanged:</strong> <span class="text-muted">${rankChanges.unchanged}</span></div>`;
    
    html += '</div></div></div>';
    
    // Top Performers
    html += '<div class="col-md-6">';
    html += '<div class="card border-0 bg-light">';
    html += '<div class="card-body">';
    html += '<h6 class="card-title"><i class="fas fa-trophy me-2"></i>Top Impact Changes</h6>';
    
    // Find employees with biggest changes
    let allEmployees = [];
    Object.keys(scenarioResults).forEach(scenarioName => {
        const scenario = scenarioResults[scenarioName];
        let impactData = scenario.weight_impact || scenario.score_impact || scenario.criteria_impact;
        
        if (impactData && Array.isArray(impactData)) {
            impactData.forEach(employee => {
                allEmployees.push({
                    name: employee.employee ? employee.employee.name : 'Unknown',
                    scoreChange: employee.score_change || 0,
                    rankChange: (employee.original_ranking && employee.ranking) ? 
                        (employee.original_ranking - employee.ranking) : 0
                });
            });
        }
    });
    
    // Sort by absolute score change
    allEmployees.sort((a, b) => Math.abs(b.scoreChange) - Math.abs(a.scoreChange));
    
    html += '<div class="list-group list-group-flush">';
    allEmployees.slice(0, 5).forEach(employee => {
        const changeClass = employee.scoreChange > 0 ? 'text-success' : employee.scoreChange < 0 ? 'text-danger' : 'text-muted';
        const changeIcon = employee.scoreChange > 0 ? 'fa-arrow-up' : employee.scoreChange < 0 ? 'fa-arrow-down' : 'fa-minus';
        
        html += `<div class="list-group-item border-0 px-0 py-2">`;
        html += `<div class="d-flex justify-content-between align-items-center">`;
        html += `<span class="fw-medium">${employee.name}</span>`;
        html += `<span class="${changeClass}"><i class="fas ${changeIcon} me-1"></i>${(employee.scoreChange * 100).toFixed(2)}%</span>`;
        html += `</div></div>`;
    });
    html += '</div>';
    
    html += '</div></div></div>';
    html += '</div>';
    
    // Detailed Comparison Table
    html += '<div class="mt-4">';
    html += '<h6><i class="fas fa-table me-2"></i>Detailed Comparison</h6>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm table-hover">';
    html += '<thead class="table-light"><tr><th>Employee</th><th>Original Score</th><th>New Score</th><th>Score Change</th><th>Original Rank</th><th>New Rank</th><th>Rank Change</th></tr></thead>';
    html += '<tbody>';
    
    Object.keys(scenarioResults).forEach(scenarioName => {
        const scenario = scenarioResults[scenarioName];
        let impactData = scenario.weight_impact || scenario.score_impact || scenario.criteria_impact;
        
        if (impactData && Array.isArray(impactData)) {
            impactData.forEach(employee => {
                const originalScore = employee.original_score ? (employee.original_score * 100).toFixed(1) + '%' : 'N/A';
                const newScore = employee.total_score ? (employee.total_score * 100).toFixed(1) + '%' : 'N/A';
                const scoreChange = employee.score_change ? (employee.score_change * 100).toFixed(2) + '%' : '0.00%';
                const originalRank = employee.original_ranking || 'N/A';
                const newRank = employee.ranking || 'N/A';
                const rankChange = (originalRank !== 'N/A' && newRank !== 'N/A') ? (originalRank - newRank) : 0;
                
                const scoreChangeClass = employee.score_change > 0 ? 'text-success' : employee.score_change < 0 ? 'text-danger' : 'text-muted';
                const rankChangeClass = rankChange > 0 ? 'text-success' : rankChange < 0 ? 'text-danger' : 'text-muted';
                const rankChangeIcon = rankChange > 0 ? 'fa-arrow-up' : rankChange < 0 ? 'fa-arrow-down' : 'fa-minus';
                
                html += `<tr>`;
                html += `<td class="fw-medium">${employee.employee ? employee.employee.name : 'Unknown'}</td>`;
                html += `<td>${originalScore}</td>`;
                html += `<td>${newScore}</td>`;
                html += `<td class="${scoreChangeClass}">${scoreChange}</td>`;
                html += `<td>${originalRank}</td>`;
                html += `<td>${newRank}</td>`;
                html += `<td class="${rankChangeClass}"><i class="fas ${rankChangeIcon} me-1"></i>${Math.abs(rankChange)}</td>`;
                html += `</tr>`;
            });
        }
    });
    
    html += '</tbody></table></div></div>';
    
    $('#comparisonContent').html(html);
}

function exportResults() {
    // Implementation for export functionality
    console.log('Export results');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/analysis/what-if.blade.php ENDPATH**/ ?>