@extends('layouts.main')

@section('title', __('Multi-Period Comparison') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Multi-Period Comparison'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">{{ __('Compare employee performance across different evaluation periods') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="refreshComparison()" data-bs-toggle="tooltip" title="{{ __('Refresh Comparison') }}">
            <i class="fas fa-sync-alt me-1"></i>
            {{ __('Refresh') }}
        </button>
        <button class="btn btn-success btn-sm" onclick="exportComparison()" data-bs-toggle="tooltip" title="{{ __('Export Comparison') }}">
            <i class="fas fa-download me-1"></i>
            {{ __('Export') }}
        </button>
    </div>
</div>

<!-- Comparison Configuration Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-cogs text-primary me-2"></i>
            {{ __('Comparison Configuration') }}
        </h6>
    </div>
    <div class="card-body">
        <form id="comparisonForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="period1Select" class="form-label">{{ __('Period 1') }}</label>
                    <select class="form-select" id="period1Select" name="period1" required>
                        <option value="">{{ __('Select Period') }}</option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period }}">{{ $period }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="period2Select" class="form-label">{{ __('Period 2') }}</label>
                    <select class="form-select" id="period2Select" name="period2" required>
                        <option value="">{{ __('Select Period') }}</option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period }}">{{ $period }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="employeeFilter" class="form-label">{{ __('Employee Filter') }}</label>
                    <select class="form-select" id="employeeFilter" name="employee_id">
                        <option value="">{{ __('All Employees') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('Run Comparison') }}
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

<!-- Comparison Results -->
<div class="card" id="resultsCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-line text-success me-2"></i>
            {{ __('Comparison Results') }}
        </h6>
    </div>
    <div class="card-body">
        <div id="loadingResults" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2 text-muted">{{ __('Running comparison analysis...') }}</p>
        </div>

        <div id="resultsContent">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<!-- Comparison Table -->
<div class="card mt-4" id="comparisonTableCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-table text-info me-2"></i>
            {{ __('Performance Comparison Table') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="comparisonTable">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Period 1') }}</th>
                        <th>{{ __('Period 2') }}</th>
                        <th>{{ __('Score Change') }}</th>
                        <th>{{ __('Rank Change') }}</th>
                        <th>{{ __('Performance Trend') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="comparisonTableBody">
                    <!-- Comparison data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Trend Analysis Chart -->
<div class="card mt-4" id="chartCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-bar text-warning me-2"></i>
            {{ __('Performance Trend Chart') }}
        </h6>
    </div>
    <div class="card-body">
        <canvas id="trendChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Summary Statistics -->
<div class="card mt-4" id="summaryCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-pie text-success me-2"></i>
            {{ __('Summary Statistics') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row" id="summaryStats">
            <!-- Summary statistics will be populated here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let trendChart = null;

$(document).ready(function() {
    initializeForm();
    initializeTooltips();
});

function initializeForm() {
    $('#comparisonForm').on('submit', function(e) {
        e.preventDefault();
        runComparison();
    });
}

function runComparison() {
    const formData = new FormData($('#comparisonForm')[0]);

    // Validate periods
    const period1 = $('#period1Select').val();
    const period2 = $('#period2Select').val();

    if (period1 === period2) {
        showError('{{ __("Please select different periods for comparison") }}');
        return;
    }

    // Show loading state
    $('#loadingResults').show();
    $('#resultsContent').hide();
    $('#resultsCard').show();

    $.ajax({
        url: '{{ route("analysis.comparison") }}',
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
                displayComparisonTable(response.comparison);
                displayTrendChart(response.chartData);
                displaySummaryStats(response.summary);
            } else {
                showError('Comparison failed: ' + response.message);
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || '{{ __("Error occurred while running comparison") }}';
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
            <h6><i class="fas fa-info-circle me-2"></i>{{ __('Comparison Summary') }}</h6>
            <p class="mb-1"><strong>{{ __('Period 1:') }}</strong> ${data.period1}</p>
            <p class="mb-1"><strong>{{ __('Period 2:') }}</strong> ${data.period2}</p>
            <p class="mb-1"><strong>{{ __('Employees Compared:') }}</strong> ${data.employees_count}</p>
            <p class="mb-0"><strong>{{ __('Analysis Date:') }}</strong> ${data.analysis_date}</p>
        </div>
    `;

    $('#resultsContent').html(html).show();
}

function displayComparisonTable(comparison) {
    if (!comparison || comparison.length === 0) {
        $('#comparisonTableCard').hide();
        return;
    }

    let tbody = '';
    comparison.forEach((item, index) => {
        const scoreChange = item.score_change;
        const rankChange = item.rank_change;
        const scoreChangeClass = scoreChange >= 0 ? 'text-success' : 'text-danger';
        const rankChangeClass = rankChange > 0 ? 'text-success' : rankChange < 0 ? 'text-danger' : 'text-muted';
        const rankChangeIcon = rankChange > 0 ? 'fa-arrow-up' : rankChange < 0 ? 'fa-arrow-down' : 'fa-minus';
        const trendClass = scoreChange >= 0 ? 'bg-success' : 'bg-danger';
        const trendText = scoreChange >= 0 ? '{{ __("Improving") }}' : '{{ __("Declining") }}';

        tbody += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="employee-avatar bg-primary me-2">${item.employee_name.substring(0, 2).toUpperCase()}</div>
                        <div>
                            <div class="fw-semibold">${item.employee_name}</div>
                            <small class="text-muted">${item.employee_code}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">${item.period1_rank}</span>
                        <span>${item.period1_score.toFixed(2)}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">${item.period2_rank}</span>
                        <span>${item.period2_score.toFixed(2)}</span>
                    </div>
                </td>
                <td class="${scoreChangeClass}">
                    ${scoreChange >= 0 ? '+' : ''}${scoreChange.toFixed(2)}
                </td>
                <td class="${rankChangeClass}">
                    <i class="fas ${rankChangeIcon} me-1"></i>${rankChange > 0 ? '+' : ''}${rankChange}
                </td>
                <td>
                    <span class="badge ${trendClass}">${trendText}</span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewEmployeeDetails(${item.employee_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#comparisonTableBody').html(tbody);
    $('#comparisonTableCard').show();
}

function displayTrendChart(chartData) {
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        $('#chartCard').hide();
        return;
    }

    const ctx = document.getElementById('trendChart').getContext('2d');

    // Destroy existing chart if it exists
    if (trendChart) {
        trendChart.destroy();
    }

    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: '{{ __("Period 1 Scores") }}',
                data: chartData.period1_scores,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: '{{ __("Period 2 Scores") }}',
                data: chartData.period2_scores,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                borderWidth: 2,
                fill: false
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
                    text: '{{ __("Performance Trend Comparison") }}'
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });

    $('#chartCard').show();
}

function displaySummaryStats(summary) {
    if (!summary) {
        $('#summaryCard').hide();
        return;
    }

    let html = `
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-primary mb-1">${summary.improved_count}</div>
                <div class="text-muted">{{ __('Improved') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-danger mb-1">${summary.declined_count}</div>
                <div class="text-muted">{{ __('Declined') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-secondary mb-1">${summary.stable_count}</div>
                <div class="text-muted">{{ __('Stable') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-info mb-1">${summary.average_change.toFixed(2)}%</div>
                <div class="text-muted">{{ __('Avg. Change') }}</div>
            </div>
        </div>
    `;

    $('#summaryStats').html(html);
    $('#summaryCard').show();
}

function resetForm() {
    $('#comparisonForm')[0].reset();
    $('#resultsCard, #comparisonTableCard, #chartCard, #summaryCard').hide();
}

function refreshComparison() {
    if ($('#resultsCard').is(':visible')) {
        runComparison();
    }
}

function exportComparison() {
    if (!$('#resultsCard').is(':visible')) {
        showWarning('{{ __("Please run comparison first before exporting") }}');
        return;
    }

    // Implementation for export functionality
    showInfo('{{ __("Export functionality will be implemented") }}');
}

function viewEmployeeDetails(employeeId) {
    // Navigate to employee details page
    window.location.href = `/employees/${employeeId}`;
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

