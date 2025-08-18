@extends('layouts.main')

@section('title', __('Advanced Analysis') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Advanced Analysis Dashboard'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Advanced Analytics') }}</h1>
        <p class="text-muted mb-0">{{ __('Comprehensive analysis tools for decision support system') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            variant="outline-info" 
            icon="fas fa-history"
            onclick="showAnalysisHistory()"
            id="historyBtn">
            {{ __('Analysis History') }}
        </x-ui.button>
        <x-ui.button 
            variant="outline-primary" 
            icon="fas fa-download"
            onclick="exportDashboard()"
            id="exportDashboardBtn">
            {{ __('Export Dashboard') }}
        </x-ui.button>
        <x-ui.button 
            href="{{ route('analysis.debug') }}" 
            variant="outline-danger" 
            icon="fas fa-bug">
            {{ __('Debug') }}
        </x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $availablePeriods->count() }}</div>
                <div class="stats-label">{{ __('Available Periods') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #e83e8c 0%, #c13584 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $criterias->count() }}</div>
                <div class="stats-label">{{ __('Analysis Criteria') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-sliders-h"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $employees->count() }}</div>
                <div class="stats-label">{{ __('Total Employees') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);">
            <div class="stats-content">
                <div class="stats-number">5</div>
                <div class="stats-label">{{ __('Analysis Tools') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Tools Grid -->
<div class="row g-4">
    <!-- Sensitivity Analysis Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card" data-tool="sensitivity">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="analysis-icon bg-primary">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="card-title mb-0">{{ __('Sensitivity Analysis') }}</h5>
                        <small class="text-muted">{{ __('Analyze weight impact') }}</small>
                    </div>
                </div>
                <p class="card-text">{{ __('Evaluate how changes in criteria weights affect employee rankings and identify the most sensitive criteria.') }}</p>
                <div class="mt-auto">
                    <a href="{{ route('analysis.sensitivity.view') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-play me-1"></i>
                        {{ __('Start Analysis') }}
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewSensitivity()">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('Preview') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- What-if Scenarios Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card" data-tool="what-if">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="analysis-icon bg-warning">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="card-title mb-0">{{ __('What-if Scenarios') }}</h5>
                        <small class="text-muted">{{ __('Scenario planning') }}</small>
                    </div>
                </div>
                <p class="card-text">{{ __('Create and compare different scenarios by modifying criteria weights, employee scores, or evaluation criteria.') }}</p>
                <div class="mt-auto">
                    <a href="{{ route('analysis.what-if.view') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-play me-1"></i>
                        {{ __('Start Analysis') }}
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewWhatIf()">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('Preview') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-period Comparison Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card" data-tool="comparison">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="analysis-icon bg-info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="card-title mb-0">{{ __('Multi-period Comparison') }}</h5>
                        <small class="text-muted">{{ __('Historical comparison') }}</small>
                    </div>
                </div>
                <p class="card-text">{{ __('Compare performance across multiple evaluation periods with detailed statistics and trend analysis.') }}</p>
                <div class="mt-auto">
                    <a href="{{ route('analysis.comparison.view') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-play me-1"></i>
                        {{ __('Start Analysis') }}
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewComparison()">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('Preview') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Forecasting Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card" data-tool="forecast">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="analysis-icon bg-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="card-title mb-0">{{ __('Performance Forecasting') }}</h5>
                        <small class="text-muted">{{ __('Predictive analysis') }}</small>
                    </div>
                </div>
                <p class="card-text">{{ __('Predict future performance trends using historical data with multiple forecasting methods.') }}</p>
                <div class="mt-auto">
                    <a href="{{ route('analysis.forecast.view') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-play me-1"></i>
                        {{ __('Start Analysis') }}
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewForecast()">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('Preview') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Statistics Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card" data-tool="statistics">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="analysis-icon bg-secondary">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="card-title mb-0">{{ __('Advanced Statistics') }}</h5>
                        <small class="text-muted">{{ __('Statistical analysis') }}</small>
                    </div>
                </div>
                <p class="card-text">{{ __('Comprehensive statistical analysis including correlation, variance, and distribution analysis.') }}</p>
                <div class="mt-auto">
                    <button class="btn btn-secondary btn-sm" onclick="showAdvancedStats()">
                        <i class="fas fa-play me-1"></i>
                        {{ __('Start Analysis') }}
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewStatistics()">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('Preview') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Analysis Card -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 analysis-card border-dashed" data-tool="quick">
            <div class="card-body text-center">
                <div class="analysis-icon bg-light text-muted mx-auto mb-3">
                    <i class="fas fa-lightning-bolt"></i>
                </div>
                <h5 class="card-title">{{ __('Quick Analysis') }}</h5>
                <p class="card-text text-muted">{{ __('Run a quick analysis with default settings for immediate insights.') }}</p>
                <button class="btn btn-outline-primary" onclick="runQuickAnalysis()">
                    <i class="fas fa-bolt me-1"></i>
                    {{ __('Quick Start') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Analysis Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('Recent Analysis') }}
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewAllHistory()">
                        {{ __('View All') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="recentAnalysisTable">
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">{{ __('No recent analysis found. Start your first analysis above.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analysis History Modal -->
<div class="modal fade" id="analysisHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Analysis History') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analysisHistoryContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Analysis Modal -->
<div class="modal fade" id="quickAnalysisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Quick Analysis') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickAnalysisForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Evaluation Period') }}</label>
                        <select class="form-select" name="period" required>
                            @foreach($availablePeriods as $period)
                                <option value="{{ $period }}">{{ $period }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Analysis Type') }}</label>
                        <select class="form-select" name="type" required>
                            <option value="sensitivity">{{ __('Sensitivity Analysis') }}</option>
                            <option value="comparison">{{ __('Multi-period Comparison') }}</option>
                            <option value="statistics">{{ __('Advanced Statistics') }}</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="executeQuickAnalysis()">
                    <i class="fas fa-play me-1"></i>
                    {{ __('Run Analysis') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.analysis-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.analysis-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.analysis-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.border-dashed {
    border: 2px dashed #dee2e6 !important;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 1.5rem;
    color: white;
    position: relative;
    overflow: hidden;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-content {
    position: relative;
    z-index: 2;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
}

.stats-icon {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 3rem;
    opacity: 0.2;
}

.card-body {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.card-body .mt-auto {
    margin-top: auto !important;
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadRecentAnalysis();
});

function loadRecentAnalysis() {
    $.ajax({
        url: '{{ route("analysis.history") }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>{{ __("Type") }}</th><th>{{ __("Period") }}</th><th>{{ __("Date") }}</th><th>{{ __("Actions") }}</th></tr></thead><tbody>';
                
                response.data.slice(0, 5).forEach(function(analysis) {
                    html += `<tr>
                        <td><span class="badge bg-primary">${analysis.type}</span></td>
                        <td>${analysis.period}</td>
                        <td>${analysis.created_at}</td>
                        <td><button class="btn btn-sm btn-outline-primary" onclick="viewAnalysis(${analysis.id})">{{ __("View") }}</button></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                $('#recentAnalysisTable').html(html);
            }
        }
    });
}

function showAnalysisHistory() {
    $('#analysisHistoryModal').modal('show');
    
    $.ajax({
        url: '{{ route("analysis.history") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '<div class="table-responsive"><table class="table table-hover">';
                html += '<thead><tr><th>{{ __("Type") }}</th><th>{{ __("Period") }}</th><th>{{ __("Date") }}</th><th>{{ __("Actions") }}</th></tr></thead><tbody>';
                
                if (response.data.length > 0) {
                    response.data.forEach(function(analysis) {
                        html += `<tr>
                            <td><span class="badge bg-primary">${analysis.type}</span></td>
                            <td>${analysis.period}</td>
                            <td>${analysis.created_at}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAnalysis(${analysis.id})">{{ __("View") }}</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAnalysis(${analysis.id})">{{ __("Delete") }}</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted">{{ __("No analysis history found") }}</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#analysisHistoryContent').html(html);
            }
        }
    });
}

function runQuickAnalysis() {
    $('#quickAnalysisModal').modal('show');
}

function executeQuickAnalysis() {
    const formData = new FormData($('#quickAnalysisForm')[0]);
    const type = formData.get('type');
    const period = formData.get('period');
    
    $('#quickAnalysisModal').modal('hide');
    
    // Show loading
    showLoadingToast('{{ __("Running analysis...") }}');
    
    let url = '';
    let data = { evaluation_period: period };
    
    switch(type) {
        case 'sensitivity':
            url = '{{ route("analysis.sensitivity") }}';
            break;
        case 'comparison':
            url = '{{ route("analysis.comparison") }}';
            data = { periods: [period] };
            break;
        case 'statistics':
            url = '{{ route("analysis.statistics") }}';
            data = { periods: [period] };
            break;
    }
    
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoadingToast();
            if (response.success) {
                showSuccessToast('{{ __("Analysis completed successfully") }}');
                // Show results in modal or redirect
                showAnalysisResults(type, response.data);
            } else {
                showErrorToast('{{ __("Analysis failed") }}');
            }
        },
        error: function(xhr) {
            hideLoadingToast();
            showErrorToast('{{ __("Analysis failed") }}');
        }
    });
}

function showAnalysisResults(type, data) {
    // Implementation depends on the specific result display requirements
    console.log('Analysis results:', type, data);
}

function previewSensitivity() {
    showInfoToast('{{ __("Sensitivity analysis preview - evaluates how criteria weight changes affect rankings") }}');
}

function previewWhatIf() {
    showInfoToast('{{ __("What-if scenarios preview - compare different evaluation scenarios") }}');
}

function previewComparison() {
    showInfoToast('{{ __("Multi-period comparison preview - analyze trends across time periods") }}');
}

function previewForecast() {
    showInfoToast('{{ __("Performance forecasting preview - predict future performance trends") }}');
}

function previewStatistics() {
    showInfoToast('{{ __("Advanced statistics preview - comprehensive statistical analysis") }}');
}

function showAdvancedStats() {
    // Show modal for advanced statistics configuration
    showInfoToast('{{ __("Advanced statistics feature - coming soon") }}');
}

function exportDashboard() {
    showInfoToast('{{ __("Dashboard export feature - coming soon") }}');
}

function viewAllHistory() {
    showAnalysisHistory();
}

function viewAnalysis(id) {
    showInfoToast('{{ __("View analysis feature - coming soon") }}');
}

function deleteAnalysis(id) {
    if (confirm('{{ __("Are you sure you want to delete this analysis?") }}')) {
        showInfoToast('{{ __("Delete analysis feature - coming soon") }}');
    }
}

// Toast notification functions
function showLoadingToast(message) {
    // Implementation depends on your toast library
    console.log('Loading:', message);
}

function hideLoadingToast() {
    console.log('Hide loading');
}

function showSuccessToast(message) {
    console.log('Success:', message);
}

function showErrorToast(message) {
    console.log('Error:', message);
}

function showInfoToast(message) {
    console.log('Info:', message);
}
</script>
@endpush