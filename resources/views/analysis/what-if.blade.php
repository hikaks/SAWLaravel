@extends('layouts.main')

@section('title', __('What-If Analysis') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('What-If Analysis'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">{{ __('Analyze different scenarios and their impact on employee rankings') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="refreshAnalysis()" data-bs-toggle="tooltip" title="{{ __('Refresh Analysis') }}">
            <i class="fas fa-sync-alt me-1"></i>
            {{ __('Refresh') }}
        </button>
        <button class="btn btn-success btn-sm" onclick="exportAnalysis()" data-bs-toggle="tooltip" title="{{ __('Export Analysis') }}">
            <i class="fas fa-download me-1"></i>
            {{ __('Export') }}
        </button>
    </div>
</div>

<!-- Analysis Configuration Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-cogs text-primary me-2"></i>
            {{ __('Analysis Configuration') }}
        </h6>
    </div>
    <div class="card-body">
        <form id="whatIfForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="periodSelect" class="form-label">{{ __('Evaluation Period') }}</label>
                    <select class="form-select" id="periodSelect" name="period" required>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period }}" {{ $period == $selectedPeriod ? 'selected' : '' }}>
                                {{ $period }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="employeeSelect" class="form-label">{{ __('Employee') }}</label>
                    <select class="form-select" id="employeeSelect" name="employee_id">
                        <option value="">{{ __('All Employees') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="scenarioType" class="form-label">{{ __('Scenario Type') }}</label>
                    <select class="form-select" id="scenarioType" name="scenario_type" required>
                        <option value="criteria_weight">{{ __('Criteria Weight Change') }}</option>
                        <option value="score_adjustment">{{ __('Score Adjustment') }}</option>
                        <option value="new_criteria">{{ __('New Criteria Addition') }}</option>
                        <option value="employee_removal">{{ __('Employee Removal') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="changeAmount" class="form-label">{{ __('Change Amount (%)') }}</label>
                    <input type="number" class="form-control" id="changeAmount" name="change_amount"
                           min="-50" max="50" step="5" value="10" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play me-2"></i>
                        {{ __('Run Analysis') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetForm()">
                        <i class="fas fa-undo me-2"></i>
                        {{ __('Reset') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Section -->
<div class="card" id="resultsCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-line text-success me-2"></i>
            {{ __('Analysis Results') }}
        </h6>
    </div>
    <div class="card-body">
        <div id="loadingResults" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2 text-muted">{{ __('Running what-if analysis...') }}</p>
        </div>

        <div id="resultsContent">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<!-- Comparison Table -->
<div class="card mt-4" id="comparisonCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-table text-info me-2"></i>
            {{ __('Ranking Comparison') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="comparisonTable">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Rank') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Original Score') }}</th>
                        <th>{{ __('New Score') }}</th>
                        <th>{{ __('Score Change') }}</th>
                        <th>{{ __('Rank Change') }}</th>
                        <th>{{ __('Impact') }}</th>
                    </tr>
                </thead>
                <tbody id="comparisonTableBody">
                    <!-- Comparison data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Impact Analysis Chart -->
<div class="card mt-4" id="chartCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-bar text-warning me-2"></i>
            {{ __('Impact Analysis Chart') }}
        </h6>
    </div>
    <div class="card-body">
        <canvas id="impactChart" width="400" height="200"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
let impactChart = null;

$(document).ready(function() {
    initializeForm();
    initializeTooltips();
});

function initializeForm() {
    $('#whatIfForm').on('submit', function(e) {
        e.preventDefault();
        runWhatIfAnalysis();
    });

    // Auto-run analysis when period changes
    $('#periodSelect').change(function() {
        if ($('#resultsCard').is(':visible')) {
            runWhatIfAnalysis();
        }
    });
}

function runWhatIfAnalysis() {
    const formData = new FormData($('#whatIfForm')[0]);

    // Show loading state
    $('#loadingResults').show();
    $('#resultsContent').hide();
    $('#resultsCard').show();

    $.ajax({
        url: '{{ route("analysis.what-if") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                displayResults(response.data);
                displayComparison(response.comparison);
                displayChart(response.chartData);
            } else {
                showError('Analysis failed: ' + response.message);
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || '{{ __("Error occurred while running analysis") }}';
            showError(message);
        },
        complete: function() {
            $('#loadingResults').hide();
        }
    });
}

function displayResults(data) {
    let html = `
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>{{ __('Analysis Summary') }}</h6>
            <p class="mb-1"><strong>{{ __('Scenario:') }}</strong> ${data.scenario_description}</p>
            <p class="mb-1"><strong>{{ __('Period:') }}</strong> ${data.period}</p>
            <p class="mb-1"><strong>{{ __('Change Applied:') }}</strong> ${data.change_applied}</p>
            <p class="mb-0"><strong>{{ __('Employees Affected:') }}</strong> ${data.employees_affected}</p>
        </div>
    `;

    $('#resultsContent').html(html).show();
}

function displayComparison(comparison) {
    if (!comparison || comparison.length === 0) {
        $('#comparisonCard').hide();
        return;
    }

    let tbody = '';
    comparison.forEach((item, index) => {
        const rankChange = item.original_rank - item.new_rank;
        const rankChangeText = rankChange > 0 ? `+${rankChange}` : rankChange < 0 ? rankChange : '0';
        const impactClass = rankChange > 0 ? 'text-success' : rankChange < 0 ? 'text-danger' : 'text-muted';
        const impactIcon = rankChange > 0 ? 'fa-arrow-up' : rankChange < 0 ? 'fa-arrow-down' : 'fa-minus';

        tbody += `
            <tr>
                <td><span class="badge bg-primary">${item.new_rank}</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="employee-avatar bg-primary me-2">${item.employee_name.substring(0, 2).toUpperCase()}</div>
                        <div>
                            <div class="fw-semibold">${item.employee_name}</div>
                            <small class="text-muted">${item.employee_code}</small>
                        </div>
                    </div>
                </td>
                <td>${item.original_score.toFixed(2)}</td>
                <td>${item.new_score.toFixed(2)}</td>
                <td class="${item.score_change >= 0 ? 'text-success' : 'text-danger'}">
                    ${item.score_change >= 0 ? '+' : ''}${item.score_change.toFixed(2)}
                </td>
                <td class="${impactClass}">
                    <i class="fas ${impactIcon} me-1"></i>${rankChangeText}
                </td>
                <td>
                    <span class="badge ${rankChange > 0 ? 'bg-success' : rankChange < 0 ? 'bg-danger' : 'bg-secondary'}">
                        ${rankChange > 0 ? '{{ __("Improved") }}' : rankChange < 0 ? '{{ __("Declined") }}' : '{{ __("No Change") }}'}
                    </span>
                </td>
            </tr>
        `;
    });

    $('#comparisonTableBody').html(tbody);
    $('#comparisonCard').show();
}

function displayChart(chartData) {
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        $('#chartCard').hide();
        return;
    }

    const ctx = document.getElementById('impactChart').getContext('2d');

    // Destroy existing chart if it exists
    if (impactChart) {
        impactChart.destroy();
    }

    impactChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: '{{ __("Original Score") }}',
                data: chartData.original_scores,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: '{{ __("New Score") }}',
                data: chartData.new_scores,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: '{{ __("Score Comparison") }}'
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });

    $('#chartCard').show();
}

function resetForm() {
    $('#whatIfForm')[0].reset();
    $('#periodSelect').val('{{ $selectedPeriod }}');
    $('#changeAmount').val(10);
    $('#resultsCard, #comparisonCard, #chartCard').hide();
}

function refreshAnalysis() {
    if ($('#resultsCard').is(':visible')) {
        runWhatIfAnalysis();
    }
}

function exportAnalysis() {
    if (!$('#resultsCard').is(':visible')) {
        showWarning('{{ __("Please run analysis first before exporting") }}');
        return;
    }

    // Implementation for export functionality
    showInfo('{{ __("Export functionality will be implemented") }}');
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: '{{ __("Error") }}',
        text: message
    });
}

function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: '{{ __("Warning") }}',
        text: message
    });
}

function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: '{{ __("Information") }}',
        text: message
    });
}
</script>
@endpush
