@extends('layouts.main')

@section('title', __('Performance Forecasting') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Performance Forecasting'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Performance Forecasting') }}</h1>
        <p class="text-muted mb-0">{{ __('Predict future performance using historical data') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="resetForecast()">
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

<!-- Forecast Configuration -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    {{ __('Forecast Configuration') }}
                </h6>
            </div>
            <div class="card-body">
                <form id="forecastForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Employee') }}</label>
                        <select class="form-select" name="employee_id" id="employeeSelect" required>
                            <option value="">{{ __('Choose Employee...') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Only employees with 3+ historical periods are shown') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Forecast Periods') }}</label>
                        <select class="form-select" name="periods_ahead" id="periodsAhead">
                            <option value="1">{{ __('1 Period Ahead') }}</option>
                            <option value="2">{{ __('2 Periods Ahead') }}</option>
                            <option value="3" selected>{{ __('3 Periods Ahead') }}</option>
                            <option value="4">{{ __('4 Periods Ahead') }}</option>
                            <option value="6">{{ __('6 Periods Ahead') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Forecasting Methods') }}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="linearTrend" name="methods[]" value="linear_trend" checked>
                            <label class="form-check-label" for="linearTrend">
                                {{ __('Linear Trend') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="movingAverage" name="methods[]" value="moving_average" checked>
                            <label class="form-check-label" for="movingAverage">
                                {{ __('Moving Average') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="weightedAverage" name="methods[]" value="weighted_average" checked>
                            <label class="form-check-label" for="weightedAverage">
                                {{ __('Weighted Average') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Confidence Level') }}</label>
                        <select class="form-select" name="confidence_level" id="confidenceLevel">
                            <option value="90">{{ __('90% Confidence') }}</option>
                            <option value="95" selected>{{ __('95% Confidence') }}</option>
                            <option value="99">{{ __('99% Confidence') }}</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" id="runForecastBtn">
                            <i class="fas fa-play me-1"></i>
                            {{ __('Generate Forecast') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historical Data Preview -->
        <div class="card mt-4" id="historicalPreview" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    {{ __('Historical Data') }}
                </h6>
            </div>
            <div class="card-body">
                <div id="historicalData">
                    <!-- Historical data will be shown here -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Loading State -->
        <div id="loadingResults" style="display: none;">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <h5>{{ __('Generating Performance Forecast...') }}</h5>
                    <p class="text-muted">{{ __('Please wait while we analyze historical data') }}</p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="forecastResults" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Forecast Results') }}</h6>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewType" id="chartView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="chartView">
                            <i class="fas fa-chart-line me-1"></i>{{ __('Chart') }}
                        </label>

                        <input type="radio" class="btn-check" name="viewType" id="tableView" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="tableView">
                            <i class="fas fa-table me-1"></i>{{ __('Table') }}
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

<!-- Forecast Analysis -->
<div id="forecastAnalysis" style="display: none;">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        {{ __('Forecast Accuracy') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="accuracyMetrics">
                        <!-- Accuracy metrics will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        {{ __('Confidence Intervals') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="confidenceIntervals">
                        <!-- Confidence intervals will be displayed here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        {{ __('Recommendations') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recommendations">
                        <!-- Recommendations will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.forecast-chart {
    position: relative;
    height: 400px;
}

.method-card {
    border-left: 4px solid;
    margin-bottom: 1rem;
}

.method-card.linear-trend {
    border-left-color: #007bff;
}

.method-card.moving-average {
    border-left-color: #28a745;
}

.method-card.weighted-average {
    border-left-color: #ffc107;
}

.accuracy-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.confidence-range {
    background: linear-gradient(90deg, rgba(40,167,69,0.1) 0%, rgba(40,167,69,0.3) 50%, rgba(40,167,69,0.1) 100%);
    border-radius: 0.25rem;
    padding: 0.5rem;
    margin: 0.25rem 0;
}

.historical-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.historical-item:last-child {
    border-bottom: none;
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let forecastResults = null;
let forecastChart = null;

$(document).ready(function() {
    $('#employeeSelect').change(function() {
        const employeeId = $(this).val();
        if (employeeId) {
            loadHistoricalData(employeeId);
        } else {
            $('#historicalPreview').hide();
        }
    });
    
    $('#forecastForm').submit(function(e) {
        e.preventDefault();
        generateForecast();
    });
    
    $('input[name="viewType"]').change(function() {
        displayResults();
    });
});

function loadHistoricalData(employeeId) {
    // This would typically make an AJAX call to get historical data
    $('#historicalPreview').show();
    $('#historicalData').html(`
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <small class="text-muted d-block mt-2">Loading historical data...</small>
        </div>
    `);
    
    // Simulate loading historical data
    setTimeout(() => {
        $('#historicalData').html(`
            <div class="historical-item">
                <span>2024-01</span>
                <strong>85.2%</strong>
            </div>
            <div class="historical-item">
                <span>2024-02</span>
                <strong>87.1%</strong>
            </div>
            <div class="historical-item">
                <span>2024-03</span>
                <strong>89.3%</strong>
            </div>
            <small class="text-muted">{{ __('Historical performance data') }}</small>
        `);
    }, 1000);
}

function generateForecast() {
    const formData = new FormData($('#forecastForm')[0]);
    const employeeId = formData.get('employee_id');
    
    if (!employeeId) {
        showError('Please select an employee');
        return;
    }
    
    const selectedMethods = [];
    $('input[name="methods[]"]:checked').each(function() {
        selectedMethods.push($(this).val());
    });
    
    if (selectedMethods.length === 0) {
        showError('Please select at least one forecasting method');
        return;
    }
    
    $('#forecastResults').hide();
    $('#forecastAnalysis').hide();
    $('#loadingResults').show();
    $('#runForecastBtn').prop('disabled', true);
    
    let requestData = {
        employee_id: employeeId,
        periods_ahead: formData.get('periods_ahead'),
        methods: selectedMethods,
        confidence_level: formData.get('confidence_level')
    };
    
    $.ajax({
        url: '{{ route("analysis.forecast") }}',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            
            if (response.success) {
                forecastResults = response.data;
                displayResults();
                displayAnalysis();
                $('#forecastResults').show();
                $('#forecastAnalysis').show();
            } else {
                showError('Forecast failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            
            let errorMessage = 'Forecast failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showError(errorMessage);
        }
    });
}

function displayResults() {
    if (!forecastResults) return;
    
    const viewType = $('input[name="viewType"]:checked').attr('id');
    
    if (viewType === 'chartView') {
        showResultsChart();
    } else {
        showResultsTable();
    }
}

function showResultsChart() {
    const ctx = document.createElement('canvas');
    ctx.id = 'forecastChart';
    
    $('#resultsContent').html('').append(ctx);
    
    // Destroy existing chart if it exists
    if (forecastChart) {
        forecastChart.destroy();
    }
    
    // Prepare chart data
    const historicalData = forecastResults.historical_data || [];
    const forecasts = forecastResults.forecasts || {};
    
    const labels = [];
    const datasets = [];
    
    // Historical data
    historicalData.forEach(item => {
        labels.push(item.evaluation_period);
    });
    
    const historicalScores = historicalData.map(item => item.total_score * 100);
    
    // Add forecast periods
    const periodsAhead = parseInt($('#periodsAhead').val());
    for (let i = 1; i <= periodsAhead; i++) {
        labels.push(`Forecast ${i}`);
    }
    
    // Historical data dataset
    datasets.push({
        label: 'Historical Performance',
        data: [...historicalScores, ...Array(periodsAhead).fill(null)],
        borderColor: '#6c757d',
        backgroundColor: 'rgba(108, 117, 125, 0.1)',
        pointStyle: 'circle',
        tension: 0.4
    });
    
    // Forecast datasets
    const colors = {
        linear_trend: '#007bff',
        moving_average: '#28a745',
        weighted_average: '#ffc107'
    };
    
    Object.keys(forecasts).forEach(method => {
        const forecastData = [...Array(historicalData.length).fill(null), ...forecasts[method]];
        
        datasets.push({
            label: method.replace('_', ' ').toUpperCase(),
            data: forecastData,
            borderColor: colors[method] || '#dc3545',
            backgroundColor: colors[method] ? colors[method] + '20' : '#dc354520',
            borderDash: [5, 5],
            pointStyle: 'triangle',
            tension: 0.4
        });
    });
    
    forecastChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Performance Score (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Period'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Performance Forecast'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function showResultsTable() {
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr><th>Period</th><th>Linear Trend</th><th>Moving Average</th><th>Weighted Average</th></tr></thead>';
    html += '<tbody>';
    
    const periodsAhead = parseInt($('#periodsAhead').val());
    const forecasts = forecastResults.forecasts || {};
    
    for (let i = 1; i <= periodsAhead; i++) {
        html += `<tr><td>Forecast ${i}</td>`;
        
        ['linear_trend', 'moving_average', 'weighted_average'].forEach(method => {
            const value = forecasts[method] && forecasts[method][i-1] 
                ? forecasts[method][i-1].toFixed(2) + '%' 
                : 'N/A';
            html += `<td>${value}</td>`;
        });
        
        html += '</tr>';
    }
    
    html += '</tbody></table></div>';
    $('#resultsContent').html(html);
}

function displayAnalysis() {
    if (!forecastResults) return;
    
    // Display accuracy metrics
    const accuracy = forecastResults.forecast_accuracy || {};
    let accuracyHtml = '';
    
    Object.keys(accuracy).forEach(method => {
        const value = accuracy[method];
        const badgeClass = value > 0.8 ? 'bg-success' : value > 0.6 ? 'bg-warning' : 'bg-danger';
        
        accuracyHtml += `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>${method.replace('_', ' ').toUpperCase()}</span>
                <span class="badge accuracy-badge ${badgeClass}">${(value * 100).toFixed(1)}%</span>
            </div>
        `;
    });
    
    $('#accuracyMetrics').html(accuracyHtml);
    
    // Display confidence intervals
    const intervals = forecastResults.confidence_intervals || {};
    let intervalsHtml = '';
    
    Object.keys(intervals).forEach(level => {
        const interval = intervals[level];
        intervalsHtml += `
            <div class="confidence-range">
                <strong>${level}% Confidence:</strong><br>
                <small>${interval.lower?.toFixed(2) || 'N/A'}% - ${interval.upper?.toFixed(2) || 'N/A'}%</small>
            </div>
        `;
    });
    
    $('#confidenceIntervals').html(intervalsHtml);
    
    // Display recommendations
    const recommendationsHtml = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Based on historical trends, performance is expected to continue improving.
        </div>
        <ul class="list-unstyled">
            <li><i class="fas fa-check text-success me-2"></i>Linear trend shows consistent growth</li>
            <li><i class="fas fa-check text-success me-2"></i>Moving average indicates stability</li>
            <li><i class="fas fa-exclamation text-warning me-2"></i>Monitor for seasonal variations</li>
        </ul>
    `;
    
    $('#recommendations').html(recommendationsHtml);
}

function showError(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#forecastResults').html(alertHtml).show();
}

function resetForecast() {
    $('#forecastForm')[0].reset();
    $('#forecastResults').hide();
    $('#forecastAnalysis').hide();
    $('#historicalPreview').hide();
    
    if (forecastChart) {
        forecastChart.destroy();
        forecastChart = null;
    }
}

function exportResults() {
    // Implementation for export functionality
    console.log('Export forecast results');
}
</script>
@endsection