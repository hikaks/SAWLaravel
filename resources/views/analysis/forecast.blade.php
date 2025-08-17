@extends('layouts.main')

@section('title', __('Performance Forecasting') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Performance Forecasting'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">{{ __('Predict future employee performance based on historical data trends') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="refreshForecast()" data-bs-toggle="tooltip" title="{{ __('Refresh Forecast') }}">
            <i class="fas fa-sync-alt me-1"></i>
            {{ __('Refresh') }}
        </button>
        <button class="btn btn-success btn-sm" onclick="exportForecast()" data-bs-toggle="tooltip" title="{{ __('Export Forecast') }}">
            <i class="fas fa-download me-1"></i>
            {{ __('Export') }}
        </button>
    </div>
</div>

<!-- Forecast Configuration Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-cogs text-primary me-2"></i>
            {{ __('Forecast Configuration') }}
        </h6>
    </div>
    <div class="card-body">
        <form id="forecastForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="forecastPeriods" class="form-label">{{ __('Forecast Periods') }}</label>
                    <select class="form-select" id="forecastPeriods" name="forecast_periods" required>
                        <option value="1">1 {{ __('Month') }}</option>
                        <option value="3" selected>3 {{ __('Months') }}</option>
                        <option value="6">6 {{ __('Months') }}</option>
                        <option value="12">1 {{ __('Year') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="forecastMethod" class="form-label">{{ __('Forecast Method') }}</label>
                    <select class="form-select" id="forecastMethod" name="forecast_method" required>
                        <option value="linear" selected>{{ __('Linear Regression') }}</option>
                        <option value="exponential">{{ __('Exponential Smoothing') }}</option>
                        <option value="moving_average">{{ __('Moving Average') }}</option>
                        <option value="trend_analysis">{{ __('Trend Analysis') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="confidenceLevel" class="form-label">{{ __('Confidence Level') }}</label>
                    <select class="form-select" id="confidenceLevel" name="confidence_level" required>
                        <option value="0.80">80%</option>
                        <option value="0.85">85%</option>
                        <option value="0.90" selected>90%</option>
                        <option value="0.95">95%</option>
                    </select>
                </div>
                <div class="col-md-3">
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
                        {{ __('Generate Forecast') }}
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

<!-- Forecast Results -->
<div class="card" id="resultsCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-line text-success me-2"></i>
            {{ __('Forecast Results') }}
        </h6>
    </div>
    <div class="card-body">
        <div id="loadingResults" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2 text-muted">{{ __('Generating performance forecast...') }}</p>
        </div>

        <div id="resultsContent">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<!-- Forecast Chart -->
<div class="card mt-4" id="chartCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-chart-area text-warning me-2"></i>
            {{ __('Performance Forecast Chart') }}
        </h6>
    </div>
    <div class="card-body">
        <canvas id="forecastChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Forecast Table -->
<div class="card mt-4" id="forecastTableCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-table text-info me-2"></i>
            {{ __('Forecast Data Table') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="forecastTable">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Current Score') }}</th>
                        <th>{{ __('Forecast Score') }}</th>
                        <th>{{ __('Change') }}</th>
                        <th>{{ __('Trend') }}</th>
                        <th>{{ __('Confidence') }}</th>
                        <th>{{ __('Risk Level') }}</th>
                    </tr>
                </thead>
                <tbody id="forecastTableBody">
                    <!-- Forecast data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Risk Analysis -->
<div class="card mt-4" id="riskCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
            {{ __('Risk Analysis') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row" id="riskStats">
            <!-- Risk statistics will be populated here -->
        </div>
    </div>
</div>

<!-- Recommendations -->
<div class="card mt-4" id="recommendationsCard" style="display: none;">
    <div class="card-header">
        <h6 class="mb-0 fw-semibold d-flex align-items-center">
            <i class="fas fa-lightbulb text-warning me-2"></i>
            {{ __('Strategic Recommendations') }}
        </h6>
    </div>
    <div class="card-body">
        <div id="recommendationsContent">
            <!-- Recommendations will be populated here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let forecastChart = null;

$(document).ready(function() {
    initializeForm();
    initializeTooltips();
});

function initializeForm() {
    $('#forecastForm').on('submit', function(e) {
        e.preventDefault();
        generateForecast();
    });
}

function generateForecast() {
    const formData = new FormData($('#forecastForm')[0]);

    // Show loading state
    $('#loadingResults').show();
    $('#resultsContent').hide();
    $('#resultsCard').show();

    $.ajax({
        url: '{{ route("analysis.forecast") }}',
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
                displayForecastChart(response.chartData);
                displayForecastTable(response.forecastData);
                displayRiskAnalysis(response.riskAnalysis);
                displayRecommendations(response.recommendations);
            } else {
                showError('Forecast failed: ' + response.message);
            }
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.message || '{{ __("Error occurred while generating forecast") }}';
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
            <h6><i class="fas fa-info-circle me-2"></i>{{ __('Forecast Summary') }}</h6>
            <p class="mb-1"><strong>{{ __('Forecast Periods:') }}</strong> ${data.forecast_periods} {{ __('months') }}</p>
            <p class="mb-1"><strong>{{ __('Method Used:') }}</strong> ${data.forecast_method}</p>
            <p class="mb-1"><strong>{{ __('Confidence Level:') }}</strong> ${(data.confidence_level * 100).toFixed(0)}%</p>
            <p class="mb-1"><strong>{{ __('Employees Analyzed:') }}</strong> ${data.employees_analyzed}</p>
            <p class="mb-0"><strong>{{ __('Generated Date:') }}</strong> ${data.generated_date}</p>
        </div>
    `;

    $('#resultsContent').html(html).show();
}

function displayForecastChart(chartData) {
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        $('#chartCard').hide();
        return;
    }

    const ctx = document.getElementById('forecastChart').getContext('2d');

    // Destroy existing chart if it exists
    if (forecastChart) {
        forecastChart.destroy();
    }

    forecastChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: '{{ __("Historical Data") }}',
                data: chartData.historical_scores,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: '{{ __("Forecast") }}',
                data: chartData.forecast_scores,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false
            }, {
                label: '{{ __("Confidence Interval") }}',
                data: chartData.confidence_upper,
                borderColor: 'rgba(255, 99, 132, 0.3)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 1,
                fill: '+1',
                pointRadius: 0
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
                    text: '{{ __("Performance Forecast") }}'
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });

    $('#chartCard').show();
}

function displayForecastTable(forecastData) {
    if (!forecastData || forecastData.length === 0) {
        $('#forecastTableCard').hide();
        return;
    }

    let tbody = '';
    forecastData.forEach((item, index) => {
        const change = item.forecast_score - item.current_score;
        const changeClass = change >= 0 ? 'text-success' : 'text-danger';
        const trendClass = change >= 0 ? 'bg-success' : 'bg-danger';
        const trendText = change >= 0 ? '{{ __("Improving") }}' : '{{ __("Declining") }}';
        const riskClass = item.risk_level === 'high' ? 'bg-danger' : item.risk_level === 'medium' ? 'bg-warning' : 'bg-success';

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
                <td>${item.current_score.toFixed(2)}</td>
                <td>${item.forecast_score.toFixed(2)}</td>
                <td class="${changeClass}">
                    ${change >= 0 ? '+' : ''}${change.toFixed(2)}
                </td>
                <td>
                    <span class="badge ${trendClass}">${trendText}</span>
                </td>
                <td>${(item.confidence * 100).toFixed(1)}%</td>
                <td>
                    <span class="badge ${riskClass}">${item.risk_level.toUpperCase()}</span>
                </td>
            </tr>
        `;
    });

    $('#forecastTableBody').html(tbody);
    $('#forecastTableCard').show();
}

function displayRiskAnalysis(riskAnalysis) {
    if (!riskAnalysis) {
        $('#riskCard').hide();
        return;
    }

    let html = `
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-success mb-1">${riskAnalysis.low_risk_count}</div>
                <div class="text-muted">{{ __('Low Risk') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-warning mb-1">${riskAnalysis.medium_risk_count}</div>
                <div class="text-muted">{{ __('Medium Risk') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-danger mb-1">${riskAnalysis.high_risk_count}</div>
                <div class="text-muted">{{ __('High Risk') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="h4 text-info mb-1">${riskAnalysis.average_confidence.toFixed(1)}%</div>
                <div class="text-muted">{{ __('Avg. Confidence') }}</div>
            </div>
        </div>
    `;

    $('#riskStats').html(html);
    $('#riskCard').show();
}

function displayRecommendations(recommendations) {
    if (!recommendations || recommendations.length === 0) {
        $('#recommendationsCard').hide();
        return;
    }

    let html = '<div class="row">';
    recommendations.forEach((rec, index) => {
        const priorityClass = rec.priority === 'high' ? 'border-danger' : rec.priority === 'medium' ? 'border-warning' : 'border-success';
        const priorityText = rec.priority === 'high' ? '{{ __("High Priority") }}' : rec.priority === 'medium' ? '{{ __("Medium Priority") }}' : '{{ __("Low Priority") }}';

        html += `
            <div class="col-md-6 mb-3">
                <div class="card border ${priorityClass} h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            ${rec.title}
                            <span class="badge ${rec.priority === 'high' ? 'bg-danger' : rec.priority === 'medium' ? 'bg-warning' : 'bg-success'} float-end">
                                ${priorityText}
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text">${rec.description}</p>
                        <div class="mt-2">
                            <strong>{{ __('Impact:') }}</strong> ${rec.impact}
                        </div>
                        <div class="mt-1">
                            <strong>{{ __('Timeline:') }}</strong> ${rec.timeline}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';

    $('#recommendationsContent').html(html);
    $('#recommendationsCard').show();
}

function resetForm() {
    $('#forecastForm')[0].reset();
    $('#forecastPeriods').val('3');
    $('#forecastMethod').val('linear');
    $('#confidenceLevel').val('0.90');
    $('#resultsCard, #chartCard, #forecastTableCard, #riskCard, #recommendationsCard').hide();
}

function refreshForecast() {
    if ($('#resultsCard').is(':visible')) {
        generateForecast();
    }
}

function exportForecast() {
    if (!$('#resultsCard').is(':visible')) {
        showWarning('{{ __("Please generate forecast first before exporting") }}');
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

