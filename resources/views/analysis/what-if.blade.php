@extends('layouts.main')

@section('title', __('What-if Scenarios') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('What-if Scenarios'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('What-if Scenarios') }}</h1>
        <p class="text-muted mb-0">{{ __('Create and compare different evaluation scenarios') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="resetScenarios()">
            <i class="fas fa-undo me-1"></i>
            {{ __('Reset') }}
        </button>
        <button class="btn btn-outline-info" onclick="exportResults()">
            <i class="fas fa-download me-1"></i>
            {{ __('Export Results') }}
        </button>
        <a href="{{ route('analysis.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>
            {{ __('Back to Analysis') }}
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
                    {{ __('Scenario Configuration') }}
                </h6>
            </div>
            <div class="card-body">
                <form id="scenarioForm">
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
                        <label class="form-label">{{ __('Scenario Type') }}</label>
                        <select class="form-select" name="scenario_type" id="scenarioType">
                            <option value="weight_changes">{{ __('Weight Changes') }}</option>
                            <option value="score_changes">{{ __('Score Changes') }}</option>
                            <option value="criteria_changes">{{ __('Criteria Changes') }}</option>
                        </select>
                    </div>

                    <div id="scenarioConfiguration">
                        <!-- Dynamic scenario configuration will be loaded here -->
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning" id="runScenarioBtn">
                            <i class="fas fa-play me-1"></i>
                            {{ __('Run Scenario') }}
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
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <h5>{{ __('Running What-if Analysis...') }}</h5>
                    <p class="text-muted">{{ __('Please wait while we process your scenarios') }}</p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="scenarioResults" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Scenario Results') }}</h6>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewType" id="tableView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="tableView">
                            <i class="fas fa-table me-1"></i>{{ __('Table') }}
                        </label>

                        <input type="radio" class="btn-check" name="viewType" id="chartView" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="chartView">
                            <i class="fas fa-chart-bar me-1"></i>{{ __('Chart') }}
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
                {{ __('Scenario Comparison') }}
            </h6>
        </div>
        <div class="card-body">
            <div id="comparisonContent">
                <!-- Comparison results will be displayed here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
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
@endsection

@section('scripts')
<script>
let scenarioResults = null;
let scenarios = [];

$(document).ready(function() {
    loadScenarioConfiguration();
    
    $('#scenarioType').change(function() {
        loadScenarioConfiguration();
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
                    <label class="form-label">{{ __('Modify Criteria Weights') }}</label>
                    @foreach($criterias as $criteria)
                    <div class="mb-2">
                        <label class="form-label small">{{ $criteria->name }}</label>
                        <div class="input-group">
                            <input type="range" class="form-range me-2" 
                                   min="1" max="50" 
                                   value="{{ $criteria->weight }}" 
                                   id="weight_{{ $criteria->id }}">
                            <input type="number" class="form-control" 
                                   style="width: 80px;"
                                   min="1" max="100" 
                                   value="{{ $criteria->weight }}" 
                                   id="weightValue_{{ $criteria->id }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            `;
            break;
            
        case 'score_changes':
            html = `
                <div class="mb-3">
                    <label class="form-label">{{ __('Employee') }}</label>
                    <select class="form-select" id="targetEmployee">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Score Modification') }}</label>
                    <input type="number" class="form-control" id="scoreChange" 
                           placeholder="Enter percentage change (e.g., +10 or -5)" min="-50" max="50">
                </div>
            `;
            break;
            
        case 'criteria_changes':
            html = `
                <div class="mb-3">
                    <label class="form-label">{{ __('Action') }}</label>
                    <select class="form-select" id="criteriaAction">
                        <option value="remove">{{ __('Remove Criteria') }}</option>
                        <option value="add">{{ __('Add New Criteria') }}</option>
                    </select>
                </div>
                <div id="criteriaOptions">
                    <!-- Dynamic options based on action -->
                </div>
            `;
            break;
    }
    
    $('#scenarioConfiguration').html(html);
}

function runWhatIfAnalysis() {
    const formData = new FormData($('#scenarioForm')[0]);
    const scenarioType = $('#scenarioType').val();
    
    $('#scenarioResults').hide();
    $('#loadingResults').show();
    $('#runScenarioBtn').prop('disabled', true);
    
    let requestData = {
        evaluation_period: formData.get('evaluation_period'),
        scenarios: [{
            name: 'Scenario 1',
            type: scenarioType,
            changes: getScenarioChanges(scenarioType)
        }]
    };
    
    $.ajax({
        url: '{{ route("analysis.what-if") }}',
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
                displayResults();
                $('#scenarioResults').show();
                $('#scenarioComparison').show();
            } else {
                showError('Analysis failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            $('#loadingResults').hide();
            $('#runScenarioBtn').prop('disabled', false);
            
            let errorMessage = 'Analysis failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showError(errorMessage);
        }
    });
}

function getScenarioChanges(scenarioType) {
    switch(scenarioType) {
        case 'weight_changes':
            let changes = {};
            @foreach($criterias as $criteria)
                changes[{{ $criteria->id }}] = parseInt($('#weightValue_{{ $criteria->id }}').val());
            @endforeach
            return changes;
            
        case 'score_changes':
            return {
                employee_id: $('#targetEmployee').val(),
                score_change: parseFloat($('#scoreChange').val())
            };
            
        case 'criteria_changes':
            return {
                action: $('#criteriaAction').val()
            };
            
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
    // Implementation for table view
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr><th>Employee</th><th>Original Rank</th><th>New Rank</th><th>Change</th></tr></thead>';
    html += '<tbody>';
    // Add table rows here based on scenarioResults
    html += '</tbody></table></div>';
    
    $('#resultsContent').html(html);
}

function showResultsChart() {
    // Implementation for chart view
    $('#resultsContent').html('<canvas id="scenarioChart"></canvas>');
    // Add Chart.js implementation here
}

function showError(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
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

function exportResults() {
    // Implementation for export functionality
    console.log('Export results');
}
</script>
@endsection