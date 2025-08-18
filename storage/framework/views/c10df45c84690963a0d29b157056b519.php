<?php $__env->startSection('title', __('Performance Forecasting') . ' - ' . __('SAW Employee Evaluation')); ?>
<?php $__env->startSection('page-title', __('Performance Forecasting')); ?>

<?php $__env->startSection('content'); ?>
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?php echo e(__('Performance Forecasting')); ?></h1>
        <p class="text-muted mb-0"><?php echo e(__('Predict future performance using historical data')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="resetForecast()">
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

<!-- Forecast Configuration -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    <?php echo e(__('Forecast Configuration')); ?>

                </h6>
            </div>
            <div class="card-body">
                <form id="forecastForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Select Employee')); ?></label>
                        <select class="form-select" name="employee_id" id="employeeSelect" required>
                            <option value=""><?php echo e(__('Choose Employee...')); ?></option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted"><?php echo e(__('Only employees with 3+ historical periods are shown')); ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Forecast Periods')); ?></label>
                        <select class="form-select" name="periods_ahead" id="periodsAhead">
                            <option value="1"><?php echo e(__('1 Period Ahead')); ?></option>
                            <option value="2"><?php echo e(__('2 Periods Ahead')); ?></option>
                            <option value="3" selected><?php echo e(__('3 Periods Ahead')); ?></option>
                            <option value="4"><?php echo e(__('4 Periods Ahead')); ?></option>
                            <option value="6"><?php echo e(__('6 Periods Ahead')); ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Forecasting Methods')); ?></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="linearTrend" name="methods[]" value="linear_trend" checked>
                            <label class="form-check-label" for="linearTrend">
                                <?php echo e(__('Linear Trend')); ?>

                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="movingAverage" name="methods[]" value="moving_average" checked>
                            <label class="form-check-label" for="movingAverage">
                                <?php echo e(__('Moving Average')); ?>

                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="weightedAverage" name="methods[]" value="weighted_average" checked>
                            <label class="form-check-label" for="weightedAverage">
                                <?php echo e(__('Weighted Average')); ?>

                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Confidence Level')); ?></label>
                        <select class="form-select" name="confidence_level" id="confidenceLevel">
                            <option value="90"><?php echo e(__('90% Confidence')); ?></option>
                            <option value="95" selected><?php echo e(__('95% Confidence')); ?></option>
                            <option value="99"><?php echo e(__('99% Confidence')); ?></option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" id="runForecastBtn">
                            <i class="fas fa-play me-1"></i>
                            <?php echo e(__('Generate Forecast')); ?>

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
                    <?php echo e(__('Historical Data')); ?>

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
                        <span class="visually-hidden"><?php echo e(__('Loading...')); ?></span>
                    </div>
                    <h5><?php echo e(__('Generating Performance Forecast...')); ?></h5>
                    <p class="text-muted"><?php echo e(__('Please wait while we analyze historical data')); ?></p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="forecastResults" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e(__('Forecast Results')); ?></h6>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewType" id="chartView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="chartView">
                            <i class="fas fa-chart-line me-1"></i><?php echo e(__('Chart')); ?>

                        </label>

                        <input type="radio" class="btn-check" name="viewType" id="tableView" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="tableView">
                            <i class="fas fa-table me-1"></i><?php echo e(__('Table')); ?>

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
                        <?php echo e(__('Forecast Accuracy')); ?>

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
                        <?php echo e(__('Confidence Intervals')); ?>

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
                        <?php echo e(__('Recommendations')); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let forecastResults = null;
let forecastChart = null;

$(document).ready(function() {
    // Show debug info on page load
    console.log('🚀 Forecast page loaded');
    showDebugInfo();
    
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
    
    // Add debug button (hidden by default, can be shown via console)
    if (!$('#debugBtn').length) {
        const debugBtn = $(`
            <button type="button" id="debugBtn" class="btn btn-sm btn-outline-secondary" 
                    style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none;"
                    onclick="showDebugInfo()">
                <i class="fas fa-bug me-1"></i> Debug
            </button>
        `);
        $('body').append(debugBtn);
    }
    
    // Global error handler for unhandled JavaScript errors
    window.addEventListener('error', function(e) {
        console.error('❌ JavaScript Error:', {
            message: e.message,
            filename: e.filename,
            lineno: e.lineno,
            colno: e.colno,
            error: e.error
        });
    });
});

function loadHistoricalData(employeeId) {
    $('#historicalPreview').show();
    $('#historicalData').html(`
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <small class="text-muted d-block mt-2">Loading historical data...</small>
        </div>
    `);
    
    // Make AJAX call to get actual historical data
    $.ajax({
        url: '<?php echo e(route("analysis.forecast.historical")); ?>',
        method: 'GET',
        data: {
            employee_id: employeeId
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Historical data response:', response);
            if (response.success && response.data && response.data.length > 0) {
                let historicalHtml = '';
                response.data.forEach(function(item) {
                    const score = parseFloat(item.total_score) * 100; // Convert to percentage
                    historicalHtml += `
                        <div class="historical-item">
                            <span>${item.evaluation_period}</span>
                            <strong>${score.toFixed(1)}%</strong>
                        </div>
                    `;
                });
                historicalHtml += '<small class="text-muted"><?php echo e(__("Historical performance data")); ?></small>';
                $('#historicalData').html(historicalHtml);
            } else {
                console.log('No historical data found');
                $('#historicalData').html(`
                    <div class="text-muted text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(__("No historical data available")); ?>

                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Historical data error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            let errorMessage = 'Gagal memuat data historis';
            
            if (xhr.status === 0) {
                errorMessage = 'Tidak dapat terhubung ke server';
            } else if (xhr.status === 404) {
                errorMessage = 'Endpoint data historis tidak ditemukan';
            } else if (xhr.status === 419) {
                errorMessage = 'CSRF Token tidak valid';
            } else if (xhr.status === 500) {
                errorMessage = 'Kesalahan server saat memuat data historis';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            $('#historicalData').html(`
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>❌ ${errorMessage}</strong><br>
                    <small class="text-muted mt-1 d-block">Silakan refresh halaman atau pilih karyawan lain</small>
                </div>
            `);
        }
    });
}

function generateForecast() {
    const formData = new FormData($('#forecastForm')[0]);
    const employeeId = formData.get('employee_id');
    
    // Clear previous errors
    $('.alert-danger').remove();
    
    // Detailed validation
    if (!employeeId) {
        showError('❌ Silakan pilih karyawan terlebih dahulu');
        return;
    }
    
    const selectedMethods = [];
    $('input[name="methods[]"]:checked').each(function() {
        selectedMethods.push($(this).val());
    });
    
    if (selectedMethods.length === 0) {
        showError('❌ Silakan pilih minimal satu metode peramalan');
        return;
    }
    
    const periodsAhead = parseInt(formData.get('periods_ahead'));
    if (!periodsAhead || periodsAhead < 1 || periodsAhead > 12) {
        showError('❌ Periode peramalan harus antara 1-12 bulan');
        return;
    }
    
    const confidenceLevel = parseFloat(formData.get('confidence_level'));
    if (!confidenceLevel || confidenceLevel < 0.5 || confidenceLevel > 0.99) {
        showError('❌ Tingkat kepercayaan harus antara 50%-99%');
        return;
    }
    
    console.log('🚀 Starting forecast generation...', {
        employeeId: employeeId,
        methods: selectedMethods,
        periods: periodsAhead,
        confidence: confidenceLevel
    });
    
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
        url: '<?php echo e(route("analysis.forecast")); ?>',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('✅ Forecast response received:', response);
            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            
            if (response && response.success === true) {
                if (response.data && response.data.forecasts) {
                    console.log('✅ Forecast data valid, displaying results...');
                    forecastResults = response.data;
                    displayResults();
                    displayAnalysis();
                    $('#forecastResults').show();
                    $('#forecastAnalysis').show();
                } else {
                    console.error('❌ Invalid forecast data structure:', response.data);
                    showError('❌ Data peramalan tidak valid. Struktur data tidak sesuai.');
                }
            } else {
                const errorMsg = response && response.message ? response.message : 'Respons server tidak valid';
                console.error('❌ Forecast failed:', errorMsg);
                showError('❌ Peramalan gagal: ' + errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error Details:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error,
                ajaxStatus: status
            });
            
            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            
            let errorMessage = '❌ Terjadi kesalahan saat memproses peramalan';
            
            if (xhr.status === 0) {
                errorMessage = '❌ Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
            } else if (xhr.status === 404) {
                errorMessage = '❌ Endpoint tidak ditemukan (404). Periksa konfigurasi route.';
            } else if (xhr.status === 419) {
                errorMessage = '❌ CSRF Token tidak valid. Silakan refresh halaman.';
            } else if (xhr.status === 422) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = '❌ Validasi gagal: ' + errors.join(', ');
                } else {
                    errorMessage = '❌ Data input tidak valid.';
                }
            } else if (xhr.status === 500) {
                errorMessage = '❌ Kesalahan server internal. Silakan coba lagi atau hubungi administrator.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = '❌ ' + xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = '❌ ' + response.message;
                    }
                } catch (e) {
                    errorMessage = '❌ Respons server tidak valid: ' + xhr.statusText;
                }
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
        const forecastScores = forecasts[method].map(item => item.predicted_score * 100);
        const forecastData = [...Array(historicalData.length).fill(null), ...forecastScores];
        
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
                ? (forecasts[method][i-1].predicted_score * 100).toFixed(2) + '%' 
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
    // Remove any existing alerts
    $('.alert').remove();
    
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 mt-1" style="font-size: 1.2em;"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-2">Peramalan Gagal</h6>
                    <p class="mb-2">${message}</p>
                    <hr class="my-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Jika masalah berlanjut, silakan refresh halaman atau hubungi administrator.
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Show error in multiple places for better visibility
    $('#forecastResults').html(alertHtml).show();
    
    // Also show a toast notification
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message.replace(/❌\s*/, '')}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Create toast container if it doesn't exist
        if (!$('#toast-container').length) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        const $toast = $(toastHtml);
        $('#toast-container').append($toast);
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        // Remove toast element after it's hidden
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Scroll to error message
    $('html, body').animate({
        scrollTop: $('#forecastResults').offset().top - 100
    }, 500);
}

function resetForecast() {
    $('#forecastForm')[0].reset();
    $('#forecastResults').hide();
    $('#forecastAnalysis').hide();
    $('#historicalPreview').hide();
    $('.alert').remove();
    
    if (forecastChart) {
        forecastChart.destroy();
        forecastChart = null;
    }
    
    console.log('🔄 Forecast form reset');
}

// Add debug info function
function showDebugInfo() {
    const debugInfo = {
        'CSRF Token': $('meta[name="csrf-token"]').attr('content') ? '✅ Available' : '❌ Missing',
        'jQuery': typeof $ !== 'undefined' ? '✅ Loaded' : '❌ Not loaded',
        'Bootstrap': typeof bootstrap !== 'undefined' ? '✅ Loaded' : '❌ Not loaded',
        'Current URL': window.location.href,
        'User Agent': navigator.userAgent,
        'Timestamp': new Date().toISOString()
    };
    
    console.group('🔍 Debug Information');
    Object.entries(debugInfo).forEach(([key, value]) => {
        console.log(`${key}: ${value}`);
    });
    console.groupEnd();
    
    return debugInfo;
}

function exportResults() {
    // Implementation for export functionality
    console.log('Export forecast results');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/analysis/forecast.blade.php ENDPATH**/ ?>