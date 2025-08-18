@extends('layouts.main')

@section('title', __('Multi-period Comparison') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Multi-period Comparison'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Multi-period Comparison') }}</h1>
        <p class="text-muted mb-0">{{ __('Compare performance across multiple evaluation periods') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="resetComparison()">
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

<!-- Comparison Configuration -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    {{ __('Comparison Configuration') }}
                </h6>
            </div>
            <div class="card-body">
                <form id="comparisonForm">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Periods to Compare') }}</label>
                        <div class="period-selection">
                            @foreach($availablePeriods as $period)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           value="{{ $period }}" id="period_{{ $loop->index }}"
                                           name="periods[]">
                                    <label class="form-check-label" for="period_{{ $loop->index }}">
                                        {{ $period }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">{{ __('Select 2-6 periods for comparison') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Comparison Type') }}</label>
                        <select class="form-select" name="comparison_type" id="comparisonType">
                            <option value="all">{{ __('All Employees') }}</option>
                            <option value="specific">{{ __('Specific Employee') }}</option>
                            <option value="department">{{ __('By Department') }}</option>
                        </select>
                    </div>

                    <div id="specificOptions" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Employee') }}</label>
                            <select class="form-select" name="employee_id" id="employeeSelect">
                                <option value="">{{ __('Choose Employee...') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="departmentOptions" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Department (Optional)') }}</label>
                            <select class="form-select" name="department_id" id="departmentSelect">
                                <option value="">{{ __('All Departments') }}</option>
                            </select>
                            <small class="text-muted">{{ __('Leave empty to compare all departments') }}</small>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Department comparison will show performance statistics grouped by department across selected periods.') }}
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-info" id="runComparisonBtn">
                            <i class="fas fa-play me-1"></i>
                            {{ __('Run Comparison') }}
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
                    <div class="spinner-border text-info mb-3" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <h5>{{ __('Running Multi-period Comparison...') }}</h5>
                    <p class="text-muted">{{ __('Please wait while we analyze the data') }}</p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="comparisonResults" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Comparison Results') }}</h6>
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

<!-- Statistics Summary -->
<div id="statisticsSummary" style="display: none;">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('Period Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="periodStats">
                        <!-- Period statistics will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trending-up me-2"></i>
                        {{ __('Trend Analysis') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="trendAnalysis">
                        <!-- Trend analysis will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.period-selection {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
}

.comparison-chart {
    position: relative;
    height: 400px;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.trend-up {
    color: #28a745;
}

.trend-down {
    color: #dc3545;
}

.trend-stable {
    color: #6c757d;
}

.period-card {
    border-left: 4px solid #007bff;
    margin-bottom: 1rem;
}

.period-card.best-period {
    border-left-color: #28a745;
}

.period-card.worst-period {
    border-left-color: #dc3545;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let comparisonResults = null;
let comparisonChart = null;

$(document).ready(function() {
    // Load departments on page load
    loadDepartments();
    
    $('#comparisonType').change(function() {
        const type = $(this).val();
        
        // Hide all options first
        $('#specificOptions').hide();
        $('#departmentOptions').hide();
        
        // Show relevant options based on type
        if (type === 'specific') {
            $('#specificOptions').show();
        } else if (type === 'department') {
            $('#departmentOptions').show();
        }
    });
    
    $('#comparisonForm').submit(function(e) {
        e.preventDefault();
        runComparison();
    });
    
    $('input[name="viewType"]').change(function() {
        displayResults();
    });
});

function runComparison() {
    const formData = new FormData($('#comparisonForm')[0]);
    const selectedPeriods = [];
    
    $('input[name="periods[]"]:checked').each(function() {
        selectedPeriods.push($(this).val());
    });
    
    if (selectedPeriods.length < 2) {
        showError('Please select at least 2 periods for comparison');
        return;
    }
    
    if (selectedPeriods.length > 6) {
        showError('Please select maximum 6 periods for comparison');
        return;
    }
    
    $('#comparisonResults').hide();
    $('#statisticsSummary').hide();
    $('#loadingResults').show();
    $('#runComparisonBtn').prop('disabled', true);
    
    // Prepare request data
    const requestData = {
        periods: selectedPeriods,
        comparison_type: $('#comparisonType').val() || 'all'
    };
    
    // Add employee_id if specific employee is selected
    const employeeId = $('#employeeSelect').val();
    if (employeeId) {
        requestData.employee_id = employeeId;
    }
    
    // Add department_id if department comparison is selected
    const comparisonType = $('#comparisonType').val();
    if (comparisonType === 'department') {
        const departmentId = $('#departmentSelect').val();
        if (departmentId) {
            requestData.department_id = departmentId;
        }
    }
    
    $.ajax({
        url: '{{ route("analysis.comparison") }}',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#loadingResults').hide();
            $('#runComparisonBtn').prop('disabled', false);
            
            if (response.success) {
                comparisonResults = response.data;
                displayResults();
                displayStatistics();
                $('#comparisonResults').show();
                $('#statisticsSummary').show();
            } else {
                showError('Comparison failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            $('#loadingResults').hide();
            $('#runComparisonBtn').prop('disabled', false);
            
            let errorMessage = 'Comparison failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showError(errorMessage);
        }
    });
}

function displayResults() {
    if (!comparisonResults) return;
    
    const viewType = $('input[name="viewType"]:checked').attr('id');
    const comparisonType = $('#comparisonType').val();
    
    if (viewType === 'chartView') {
        if (comparisonType === 'department') {
            showDepartmentChart();
        } else {
            showResultsChart();
        }
    } else {
        if (comparisonType === 'department') {
            showDepartmentTable();
        } else {
            showResultsTable();
        }
    }
}

function showResultsChart() {
    const ctx = document.createElement('canvas');
    ctx.id = 'comparisonChart';
    
    $('#resultsContent').html('').append(ctx);
    
    // Destroy existing chart if it exists
    if (comparisonChart) {
        comparisonChart.destroy();
    }
    
    // Prepare chart data
    const periods = Object.keys(comparisonResults).filter(key => key !== 'period_changes');
    const datasets = [];
    
    // Sample data preparation - adjust based on actual data structure
    if (comparisonResults[periods[0]] && comparisonResults[periods[0]].results) {
        const employees = comparisonResults[periods[0]].results;
        
        employees.forEach((employee, index) => {
            const data = periods.map(period => {
                const periodResult = comparisonResults[period].results.find(r => r.employee_id === employee.employee_id);
                return periodResult ? periodResult.total_score * 100 : 0;
            });
            
            datasets.push({
                label: employee.employee.name,
                data: data,
                borderColor: `hsl(${index * 360 / employees.length}, 70%, 50%)`,
                backgroundColor: `hsla(${index * 360 / employees.length}, 70%, 50%, 0.1)`,
                tension: 0.4
            });
        });
    }
    
    comparisonChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periods,
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
                        text: 'Evaluation Period'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Multi-period Performance Comparison'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function showDepartmentChart() {
    const ctx = document.createElement('canvas');
    ctx.id = 'comparisonChart';
    
    $('#resultsContent').html('').append(ctx);
    
    // Destroy existing chart if it exists
    if (comparisonChart) {
        comparisonChart.destroy();
    }
    
    // Prepare chart data for department comparison
    const periods = Object.keys(comparisonResults).filter(key => key !== 'department_changes');
    const datasets = [];
    
    // Get all departments across all periods
    const allDepartments = new Set();
    periods.forEach(period => {
        if (comparisonResults[period] && comparisonResults[period].departments) {
            Object.keys(comparisonResults[period].departments).forEach(dept => {
                allDepartments.add(dept);
            });
        }
    });
    
    // Create dataset for each department
    Array.from(allDepartments).forEach((department, index) => {
        const data = periods.map(period => {
            const deptData = comparisonResults[period]?.departments?.[department];
            return deptData ? deptData.statistics.avg_score * 100 : 0;
        });
        
        datasets.push({
            label: department,
            data: data,
            borderColor: `hsl(${index * 360 / allDepartments.size}, 70%, 50%)`,
            backgroundColor: `hsla(${index * 360 / allDepartments.size}, 70%, 50%, 0.1)`,
            tension: 0.4
        });
    });
    
    comparisonChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periods,
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
                        text: 'Average Performance Score (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Evaluation Period'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Department Performance Comparison'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function showDepartmentTable() {
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr><th>Department</th>';
    
    const periods = Object.keys(comparisonResults).filter(key => key !== 'department_changes');
    periods.forEach(period => {
        html += `<th>${period}</th>`;
    });
    html += '<th>Trend</th></tr></thead><tbody>';
    
    // Get all departments
    const allDepartments = new Set();
    periods.forEach(period => {
        if (comparisonResults[period] && comparisonResults[period].departments) {
            Object.keys(comparisonResults[period].departments).forEach(dept => {
                allDepartments.add(dept);
            });
        }
    });
    
    // Add table rows for each department
    Array.from(allDepartments).forEach(department => {
        html += `<tr><td><strong>${department}</strong></td>`;
        
        periods.forEach(period => {
            const deptData = comparisonResults[period]?.departments?.[department];
            if (deptData) {
                const avgScore = (deptData.statistics.avg_score * 100).toFixed(2);
                const employeeCount = deptData.statistics.total_employees;
                html += `<td>${avgScore}% <small class="text-muted">(${employeeCount} emp)</small></td>`;
            } else {
                html += '<td>N/A</td>';
            }
        });
        
        // Calculate trend
        let trendIcon = 'fas fa-minus';
        let trendClass = 'trend-stable';
        let trendText = 'Stable';
        
        if (comparisonResults.department_changes && comparisonResults.department_changes[department]) {
            const changes = Object.values(comparisonResults.department_changes[department]);
            if (changes.length > 0) {
                const lastChange = changes[changes.length - 1];
                if (lastChange.trend === 'improving') {
                    trendIcon = 'fas fa-arrow-up';
                    trendClass = 'trend-up';
                    trendText = 'Improving';
                } else if (lastChange.trend === 'declining') {
                    trendIcon = 'fas fa-arrow-down';
                    trendClass = 'trend-down';
                    trendText = 'Declining';
                }
            }
        }
        
        html += `<td><span class="trend-indicator ${trendClass}"><i class="${trendIcon}"></i> ${trendText}</span></td>`;
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    $('#resultsContent').html(html);
}

function showResultsTable() {
    let html = '<div class="table-responsive">';
    html += '<table class="table table-hover">';
    html += '<thead><tr><th>Employee</th>';
    
    const periods = Object.keys(comparisonResults).filter(key => key !== 'period_changes');
    periods.forEach(period => {
        html += `<th>${period}</th>`;
    });
    html += '<th>Trend</th></tr></thead><tbody>';
    
    // Add table rows based on comparisonResults
    if (comparisonResults[periods[0]] && comparisonResults[periods[0]].results) {
        const employees = comparisonResults[periods[0]].results;
        
        employees.forEach(employee => {
            html += `<tr><td>${employee.employee.name}</td>`;
            
            periods.forEach(period => {
                const periodResult = comparisonResults[period].results.find(r => r.employee_id === employee.employee_id);
                const score = periodResult ? (periodResult.total_score * 100).toFixed(2) : 'N/A';
                html += `<td>${score}%</td>`;
            });
            
            html += '<td><span class="trend-indicator trend-stable"><i class="fas fa-minus"></i> Stable</span></td>';
            html += '</tr>';
        });
    }
    
    html += '</tbody></table></div>';
    $('#resultsContent').html(html);
}

function displayStatistics() {
    if (!comparisonResults) return;
    
    const periods = Object.keys(comparisonResults).filter(key => key !== 'period_changes');
    let statsHtml = '';
    
    periods.forEach(period => {
        const stats = comparisonResults[period].statistics;
        if (stats) {
            statsHtml += `
                <div class="period-card">
                    <h6>${period}</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted">Avg Score:</small><br>
                            <strong>${(stats.avg_score * 100).toFixed(2)}%</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Employees:</small><br>
                            <strong>${stats.total_employees}</strong>
                        </div>
                    </div>
                </div>
            `;
        }
    });
    
    $('#periodStats').html(statsHtml);
    
    // Simple trend analysis
    const trendHtml = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Trend analysis shows performance patterns across ${periods.length} periods.
        </div>
    `;
    $('#trendAnalysis').html(trendHtml);
}

function showError(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#comparisonResults').html(alertHtml).show();
}

function resetComparison() {
    $('#comparisonForm')[0].reset();
    $('#comparisonResults').hide();
    $('#statisticsSummary').hide();
    $('#specificOptions').hide();
    $('#departmentOptions').hide();
    comparisonResults = null;
    
    if (comparisonChart) {
        comparisonChart.destroy();
        comparisonChart = null;
    }
}

function loadDepartments() {
    $.get('{{ route("employees.index") }}', {get_departments: true})
        .done(function(response) {
            let departmentSelect = $('#departmentSelect');
            departmentSelect.empty().append('<option value="">{{ __("All Departments") }}</option>');
            
            if (response.departments) {
                response.departments.forEach(function(dept) {
                    departmentSelect.append(`<option value="${dept}">${dept}</option>`);
                });
            }
        })
        .fail(function() {
            console.log('Could not load departments');
        });
}

function exportResults() {
    // Implementation for export functionality
    console.log('Export comparison results');
}
</script>
@endpush