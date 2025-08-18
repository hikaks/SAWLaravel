@extends('layouts.main')

@section('title', __('Sensitivity Analysis') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Sensitivity Analysis'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Sensitivity Analysis') }}</h1>
        <p class="text-muted mb-0">{{ __('Analyze how changes in criteria weights affect employee rankings') }}</p>
    </div>
    <div class="flex gap-2">
        <x-ui.button 
            variant="outline-secondary" 
            icon="fas fa-undo"
            onclick="resetAnalysis()"
            id="resetBtn">
            {{ __('Reset') }}
        </x-ui.button>
        <x-ui.button 
            variant="outline-info" 
            icon="fas fa-download"
            onclick="exportResults()"
            id="exportBtn">
            {{ __('Export Results') }}
        </x-ui.button>
        <x-ui.button 
            href="{{ route('analysis.index') }}" 
            variant="outline-primary" 
            icon="fas fa-arrow-left">
            {{ __('Back to Analysis') }}
        </x-ui.button>
    </div>
</div>

<!-- Analysis Configuration -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    {{ __('Analysis Configuration') }}
                </h6>
            </div>
            <div class="card-body">
                <form id="sensitivityForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Evaluation Period') }}</label>
                        <select class="form-select" name="evaluation_period" id="evaluationPeriod" required>
                            @foreach($availablePeriods as $period)
                                <option value="{{ $period }}" {{ $period == $selectedPeriod ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Analysis Type') }}</label>
                        <select class="form-select" name="analysis_type" id="analysisType">
                            <option value="standard">{{ __('Standard Scenarios') }}</option>
                            <option value="custom">{{ __('Custom Weights') }}</option>
                        </select>
                    </div>

                    <div id="customWeightsSection" style="display: none;">
                        <label class="form-label">{{ __('Modify Criteria Weights') }}</label>
                        <div id="criteriaWeights">
                            @foreach($criterias as $criteria)
                                <div class="mb-3">
                                    <label class="form-label small">
                                        {{ $criteria->name }}
                                        <span class="text-muted">({{ $criteria->type }})</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="range" class="form-range me-2" 
                                               min="1" max="50" 
                                               value="{{ $criteria->weight }}" 
                                               id="weight_{{ $criteria->id }}"
                                               oninput="updateWeightValue({{ $criteria->id }})">
                                        <input type="number" class="form-control" 
                                               style="width: 80px;"
                                               min="1" max="100" 
                                               value="{{ $criteria->weight }}" 
                                               id="weightValue_{{ $criteria->id }}"
                                               onchange="updateWeightSlider({{ $criteria->id }})">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('Total weight will be normalized to 100%') }}
                        </div>
                    </div>

                    <div class="grid">
                        <x-ui.button 
                            variant="primary" 
                            type="submit" 
                            icon="fas fa-play" 
                            id="runAnalysisBtn" 
                            class="w-full">
                            {{ __('Run Analysis') }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Criteria Weights -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-weight me-2"></i>
                    {{ __('Current Weights') }}
                </h6>
            </div>
            <div class="card-body">
                @foreach($criterias as $criteria)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">{{ $criteria->name }}</small>
                        <span class="badge bg-{{ $criteria->type == 'benefit' ? 'success' : 'warning' }}">
                            {{ $criteria->weight }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('Analysis Results') }}
                    </h6>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="showResultsTable()" id="tableViewBtn">
                            <i class="fas fa-table me-1"></i>
                            {{ __('Table View') }}
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="showResultsChart()" id="chartViewBtn">
                            <i class="fas fa-chart-bar me-1"></i>
                            {{ __('Chart View') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="analysisResults">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No Analysis Results') }}</h5>
                        <p class="text-muted">{{ __('Configure your analysis settings and click "Run Analysis" to see results.') }}</p>
                    </div>
                </div>

                <div id="loadingResults" style="display: none;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <h5 class="text-muted">{{ __('Running Analysis...') }}</h5>
                        <p class="text-muted">{{ __('Please wait while we analyze the sensitivity of your criteria.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sensitivity Summary -->
<div class="row g-4" id="sensitivitySummary" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-analytics me-2"></i>
                    {{ __('Sensitivity Summary') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4" id="summaryContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Scenario Results -->
<div class="row g-4" id="scenarioResults" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Scenario Comparison') }}
                    </h6>
                    <select class="form-select w-auto" id="scenarioSelector" onchange="showScenarioDetails()">
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="scenarioDetails">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.form-range {
    flex: 1;
}

.sensitivity-metric {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    border: 1px solid #dee2e6;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #495057;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.ranking-change {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.ranking-change.improved {
    background-color: #d1e7dd;
    color: #0f5132;
}

.ranking-change.declined {
    background-color: #f8d7da;
    color: #842029;
}

.ranking-change.unchanged {
    background-color: #e2e3e5;
    color: #41464b;
}

.scenario-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.scenario-card:hover {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.scenario-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.weight-bar {
    height: 8px;
    border-radius: 4px;
    background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
    position: relative;
    overflow: hidden;
}

.weight-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(255,255,255,0.3);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { width: 0; }
    50% { width: 100%; }
    100% { width: 0; }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let analysisResults = null;
let currentView = 'table';

$(document).ready(function() {
    // Initialize form handlers
    $('#analysisType').change(function() {
        toggleCustomWeights();
    });

    $('#sensitivityForm').submit(function(e) {
        e.preventDefault();
        runSensitivityAnalysis();
    });

    // Load initial data if period is selected
    if ($('#evaluationPeriod').val()) {
        // Optional: Run default analysis
    }
});

function toggleCustomWeights() {
    const analysisType = $('#analysisType').val();
    if (analysisType === 'custom') {
        $('#customWeightsSection').show();
    } else {
        $('#customWeightsSection').hide();
    }
}

function updateWeightValue(criteriaId) {
    const sliderValue = $('#weight_' + criteriaId).val();
    $('#weightValue_' + criteriaId).val(sliderValue);
}

function updateWeightSlider(criteriaId) {
    const inputValue = $('#weightValue_' + criteriaId).val();
    $('#weight_' + criteriaId).val(inputValue);
}

function runSensitivityAnalysis() {
    const formData = new FormData($('#sensitivityForm')[0]);
    const analysisType = formData.get('analysis_type');
    
    // Show loading
    $('#analysisResults').hide();
    $('#loadingResults').show();
    $('#runAnalysisBtn').prop('disabled', true);
    
    let requestData = {
        evaluation_period: formData.get('evaluation_period')
    };
    
    // Add custom weights if selected
    if (analysisType === 'custom') {
        requestData.weight_changes = [];
        @foreach($criterias as $criteria)
            requestData.weight_changes.push({
                criteria_id: {{ $criteria->id }},
                weight: parseInt($('#weightValue_{{ $criteria->id }}').val())
            });
        @endforeach
    }
    
    $.ajax({
        url: '{{ route("analysis.sensitivity") }}',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#loadingResults').hide();
            $('#runAnalysisBtn').prop('disabled', false);
            
            if (response.success) {
                analysisResults = response.data;
                displayAnalysisResults();
                showSensitivitySummary();
                showScenarioResults();
            } else {
                showError('Analysis failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            $('#loadingResults').hide();
            $('#runAnalysisBtn').prop('disabled', false);
            
            let errorMessage = 'Analysis failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showError(errorMessage);
        }
    });
}

function displayAnalysisResults() {
    if (!analysisResults) return;
    
    if (currentView === 'table') {
        showResultsTable();
    } else {
        showResultsChart();
    }
    
    $('#analysisResults').show();
}

function showResultsTable() {
    currentView = 'table';
    $('#tableViewBtn').addClass('active');
    $('#chartViewBtn').removeClass('active');
    
    if (!analysisResults || !analysisResults.original_results) {
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr>';
    html += '<th>{{ __("Employee") }}</th>';
    html += '<th>{{ __("Original Rank") }}</th>';
    html += '<th>{{ __("Original Score") }}</th>';
    html += '<th>{{ __("Actions") }}</th>';
    html += '</tr></thead><tbody>';
    
    analysisResults.original_results.forEach(function(result) {
        html += `<tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar me-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                            ${result.employee.name.substring(0, 2).toUpperCase()}
                        </div>
                    </div>
                    <div>
                        <div class="fw-semibold">${result.employee.name}</div>
                        <small class="text-muted">${result.employee.department || ''}</small>
                    </div>
                </div>
            </td>
            <td><span class="badge bg-primary">#${result.ranking}</span></td>
            <td>${(result.total_score * 100).toFixed(2)}%</td>
            <td>
                <button class="btn btn-sm btn-outline-info" onclick="showEmployeeDetails(${result.employee_id})">
                    <i class="fas fa-eye me-1"></i>{{ __("Details") }}
                </button>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    $('#analysisResults').html(html);
}

function showResultsChart() {
    currentView = 'chart';
    $('#chartViewBtn').addClass('active');
    $('#tableViewBtn').removeClass('active');
    
    if (!analysisResults || !analysisResults.original_results) {
        return;
    }
    
    const html = '<canvas id="sensitivityChart" width="400" height="200"></canvas>';
    $('#analysisResults').html(html);
    
    // Create chart
    const ctx = document.getElementById('sensitivityChart').getContext('2d');
    const chartData = {
        labels: analysisResults.original_results.map(r => r.employee.name),
        datasets: [{
            label: '{{ __("Original Score") }}',
            data: analysisResults.original_results.map(r => (r.total_score * 100).toFixed(2)),
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
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
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}

function showSensitivitySummary() {
    if (!analysisResults || !analysisResults.summary) {
        return;
    }
    
    const summary = analysisResults.summary;
    let html = '';
    
    // Stability metrics
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${(summary.avg_stability_index * 100).toFixed(1)}%</div>`;
    html += '<div class="metric-label">{{ __("Average Stability") }}</div>';
    html += '</div></div>';
    
    // Most sensitive scenario
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${summary.most_sensitive_scenario || 'N/A'}</div>`;
    html += '<div class="metric-label">{{ __("Most Sensitive") }}</div>';
    html += '</div></div>';
    
    // Least sensitive scenario
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${summary.least_sensitive_scenario || 'N/A'}</div>`;
    html += '<div class="metric-label">{{ __("Most Stable") }}</div>';
    html += '</div></div>';
    
    // Total scenarios
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${Object.keys(analysisResults.sensitivity_scenarios).length}</div>`;
    html += '<div class="metric-label">{{ __("Scenarios Analyzed") }}</div>';
    html += '</div></div>';
    
    $('#summaryContent').html(html);
    $('#sensitivitySummary').show();
}

function showScenarioResults() {
    if (!analysisResults || !analysisResults.sensitivity_scenarios) {
        return;
    }
    
    // Populate scenario selector
    let selectorHtml = '<option value="">{{ __("Select Scenario") }}</option>';
    Object.keys(analysisResults.sensitivity_scenarios).forEach(function(scenarioName) {
        selectorHtml += `<option value="${scenarioName}">${scenarioName.replace(/_/g, ' ').toUpperCase()}</option>`;
    });
    $('#scenarioSelector').html(selectorHtml);
    
    $('#scenarioResults').show();
}

function showScenarioDetails() {
    const selectedScenario = $('#scenarioSelector').val();
    if (!selectedScenario || !analysisResults.sensitivity_scenarios[selectedScenario]) {
        $('#scenarioDetails').html('<p class="text-muted">{{ __("Select a scenario to view details") }}</p>');
        return;
    }
    
    const scenario = analysisResults.sensitivity_scenarios[selectedScenario];
    let html = '';
    
    // Scenario metrics
    html += '<div class="row mb-4">';
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${scenario.metrics.avg_ranking_change.toFixed(1)}</div>`;
    html += '<div class="metric-label">{{ __("Avg Rank Change") }}</div>';
    html += '</div></div>';
    
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${scenario.metrics.max_ranking_change}</div>`;
    html += '<div class="metric-label">{{ __("Max Rank Change") }}</div>';
    html += '</div></div>';
    
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${(scenario.metrics.stability_index * 100).toFixed(1)}%</div>`;
    html += '<div class="metric-label">{{ __("Stability Index") }}</div>';
    html += '</div></div>';
    
    html += '<div class="col-md-3">';
    html += '<div class="sensitivity-metric">';
    html += `<div class="metric-value">${scenario.ranking_changes.length}</div>`;
    html += '<div class="metric-label">{{ __("Employees Affected") }}</div>';
    html += '</div></div>';
    html += '</div>';
    
    // Ranking changes table
    html += '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr>';
    html += '<th>{{ __("Employee") }}</th>';
    html += '<th>{{ __("Original") }}</th>';
    html += '<th>{{ __("New Rank") }}</th>';
    html += '<th>{{ __("Change") }}</th>';
    html += '<th>{{ __("Score Change") }}</th>';
    html += '</tr></thead><tbody>';
    
    scenario.ranking_changes.forEach(function(change) {
        let changeClass = 'unchanged';
        let changeIcon = 'fas fa-minus';
        let changeText = 'No Change';
        
        if (change.ranking_change > 0) {
            changeClass = 'improved';
            changeIcon = 'fas fa-arrow-up';
            changeText = `+${change.ranking_change}`;
        } else if (change.ranking_change < 0) {
            changeClass = 'declined';
            changeIcon = 'fas fa-arrow-down';
            changeText = `${change.ranking_change}`;
        }
        
        html += `<tr>
            <td>${change.employee_name}</td>
            <td><span class="badge bg-secondary">#${change.original_ranking}</span></td>
            <td><span class="badge bg-primary">#${change.new_ranking}</span></td>
            <td>
                <span class="ranking-change ${changeClass}">
                    <i class="${changeIcon} me-1"></i>${changeText}
                </span>
            </td>
            <td>${(change.score_change * 100).toFixed(2)}%</td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    
    $('#scenarioDetails').html(html);
}

function resetAnalysis() {
    analysisResults = null;
    $('#analysisResults').html(`
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">{{ __('No Analysis Results') }}</h5>
            <p class="text-muted">{{ __('Configure your analysis settings and click "Run Analysis" to see results.') }}</p>
        </div>
    `);
    $('#sensitivitySummary').hide();
    $('#scenarioResults').hide();
    
    // Reset form
    $('#sensitivityForm')[0].reset();
    $('#analysisType').change();
}

function exportResults() {
    if (!analysisResults) {
        showError('{{ __("No results to export. Please run an analysis first.") }}');
        return;
    }
    
    // Implementation for export functionality
    showInfo('{{ __("Export functionality will be implemented soon.") }}');
}

function showEmployeeDetails(employeeId) {
    // Implementation for showing employee details
    showInfo('{{ __("Employee details functionality will be implemented soon.") }}');
}

function showError(message) {
    // Implementation depends on your notification system
    console.error(message);
    alert('Error: ' + message);
}

function showInfo(message) {
    // Implementation depends on your notification system
    console.info(message);
    alert('Info: ' + message);
}
</script>
@endpush