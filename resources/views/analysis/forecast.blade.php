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

                        <!-- Debug Button -->
                        <button type="button" class="btn btn-info mt-2" id="debugBtn" onclick="debugForecast()">
                            <i class="fas fa-bug me-1"></i>Debug Forecast
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

        <!-- Error Display Section -->
        <div id="errorDisplay" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ __('Error Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="errorContent">
                        <!-- Error details will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forecast Analysis -->
<div id="forecastAnalysis" style="display: none;">
    <!-- Analysis Content Section -->
    <div class="mt-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-analytics me-2"></i>
                    {{ __('Forecast Analysis') }}
                </h6>
            </div>
            <div class="card-body">
                <div id="analysisContent">
                    <!-- Analysis content will be displayed here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Content Section -->
    <div class="mt-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-analytics me-2"></i>
                    {{ __('Forecast Analysis') }}
                </h6>
            </div>
            <div class="card-body">
                <div id="analysisContent">
                    <!-- Analysis content will be displayed here -->
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let forecastResults = null;
let forecastChart = null;

$(document).ready(function() {
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

    $.ajax({
        url: '{{ route("analysis.forecast.historical") }}',
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
                    const score = parseFloat(item.total_score);
                    historicalHtml += `
                        <div class="historical-item">
                            <span>${item.evaluation_period}</span>
                            <strong>${score.toFixed(1)}</strong>
                        </div>
                    `;
                });
                historicalHtml += '<small class="text-muted">{{ __("Historical performance data") }}</small>';
                $('#historicalData').html(historicalHtml);
            } else {
                console.log('No historical data found');
                $('#historicalData').html(`
                    <div class="text-muted text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __("No historical data available") }}
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
    // Check if user is authenticated
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    if (!isAuthenticated) {
        showError('❌ Anda harus login terlebih dahulu untuk menggunakan Performance Forecasting.');
        return;
    }

    const formData = new FormData($('#forecastForm')[0]);
    const employeeId = formData.get('employee_id');

    // Clear previous errors
    $('.alert-danger').remove();

    // Detailed validation
    if (!employeeId) {
        showError('❌ Silakan pilih karyawan terlebih dahulu');
        return;
    }

    // Check if employee has sufficient historical data
    const selectedEmployee = $('#employeeSelect option:selected').text();
    console.log('👤 Selected employee:', selectedEmployee, '(ID:', employeeId, ')');

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
    if (!confidenceLevel || confidenceLevel < 50 || confidenceLevel > 99) {
        showError('❌ Tingkat kepercayaan harus antara 50%-99%');
        return;
    }

    console.log('🚀 Starting forecast generation...', {
        employeeId: employeeId,
        methods: selectedMethods,
        periods: periodsAhead,
        confidence: confidenceLevel
    });

    // Hide previous results and show loading
    $('#forecastResults').hide();
    $('#forecastAnalysis').hide();
    $('.alert').remove();

    // Show loading state
    $('#loadingResults').show();
    $('#runForecastBtn').prop('disabled', true);
    $('#runForecastBtn').html('<i class="fas fa-spinner fa-spin me-1"></i>Generating Forecast...');

    let requestData = {
        employee_id: employeeId,
        periods_ahead: formData.get('periods_ahead'),
        methods: selectedMethods,
        confidence_level: formData.get('confidence_level')
    };

    // Get CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log('🔑 CSRF Token:', csrfToken ? 'Available' : 'Missing');

    if (!csrfToken) {
        showError('❌ CSRF Token tidak tersedia. Silakan refresh halaman.');
        $('#loadingResults').hide();
        $('#runForecastBtn').prop('disabled', false);
        return;
    }

    $.ajax({
        url: '{{ route("analysis.forecast") }}',
        method: 'POST',
        data: requestData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('✅ Forecast response received:', response);
            console.log('📊 Response type:', typeof response);
            console.log('📊 Response keys:', Object.keys(response));
            console.log('📊 Response structure:', {
                success: response.success,
                hasData: !!response.data,
                hasForecasts: !!(response.data && response.data.forecasts),
                dataKeys: response.data ? Object.keys(response.data) : 'No data'
            });

            // Debug response data structure
            if (response.data) {
                console.log('📊 Data object keys:', Object.keys(response.data));
                if (response.data.forecasts) {
                    console.log('📊 Forecasts object keys:', Object.keys(response.data.forecasts));
                    console.log('📊 Forecasts data:', response.data.forecasts);
                }
                if (response.data.historical_data) {
                    console.log('📊 Historical data count:', response.data.historical_data.length);
                    console.log('📊 Historical data sample:', response.data.historical_data[0]);
                }
                if (response.data.confidence_intervals) {
                    console.log('📊 Confidence intervals:', response.data.confidence_intervals);
                }
                if (response.data.forecast_accuracy) {
                    console.log('📊 Forecast accuracy:', response.data.forecast_accuracy);
                }
            }

            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            $('#runForecastBtn').html('<i class="fas fa-play me-1"></i>Generate Forecast');

            if (response && response.success === true) {
                console.log('✅ Response success is true');

                if (response.data) {
                    console.log('✅ Response data exists');

                    if (response.data.forecasts) {
                        console.log('✅ Forecasts data exists');
                        console.log('📊 Forecast methods:', Object.keys(response.data.forecasts));
                        console.log('📊 Historical data count:', response.data.historical_data ? response.data.historical_data.length : 0);
                        console.log('📊 Execution time:', response.execution_time || 'N/A');

                        // Debug additional data
                        console.log('🔍 Full forecastResults object:', response.data);
                        console.log('🔍 Confidence intervals:', response.data.confidence_intervals);
                        console.log('🔍 Forecast accuracy:', response.data.forecast_accuracy);
                        console.log('🔍 All available keys:', Object.keys(response.data));

                        forecastResults = response.data;

                        console.log('🔄 Calling displayResults()...');
                        displayResults();

                        console.log('🔄 Calling displayAnalysis()...');
                        displayAnalysis();

                        console.log('🔄 Showing forecast results...');
                        $('#forecastResults').show();

                        console.log('🔄 Showing forecast analysis...');
                        $('#forecastAnalysis').show();

                        // Debug: Check if elements are visible
                        console.log('🔍 Element visibility check:');
                        console.log('  - forecastResults visible:', $('#forecastResults').is(':visible'));
                        console.log('  - forecastAnalysis visible:', $('#forecastAnalysis').is(':visible'));
                        console.log('  - analysisContent content length:', $('#analysisContent').html().length);
                        console.log('  - analysisContent HTML:', $('#analysisContent').html());

                        // Show success message with execution time
                        const execTime = response.execution_time ? ` (${response.execution_time}ms)` : '';
                        const methodCount = Object.keys(response.data.forecasts).length;
                        const periodCount = response.data.historical_data ? response.data.historical_data.length : 0;

                        showSuccessToast(`Forecast generated successfully! ${methodCount} methods, ${periodCount} historical periods${execTime}`);

                        console.log('🎉 Forecast display completed successfully!');
                        console.log(`📊 Generated ${methodCount} forecasting methods for ${periodCount} historical periods`);

                        // Check if history was recorded
                        if (response.execution_time > 0) {
                            console.log('📝 History should be recorded (execution time > 0)');
                            console.log('📝 Check Analysis History for the new record');

                            // Show history link
                            setTimeout(() => {
                                showInfoToast('📝 Forecast has been saved to Analysis History. Check the History tab for details.');
                            }, 2000);
                        }
                    } else {
                        console.error('❌ Forecasts data missing from response.data');
                        console.error('❌ Available keys in response.data:', Object.keys(response.data));
                        showError('❌ Data peramalan tidak valid. Forecasts data tidak ditemukan.');

                        // Show debug info in UI
                        $('#forecastResults').html(`
                            <div class="alert alert-warning">
                                <h5>⚠️ Debug Info:</h5>
                                <p><strong>Response Data Keys:</strong> ${Object.keys(response.data).join(', ')}</p>
                                <p><strong>Response Data:</strong> <pre>${JSON.stringify(response.data, null, 2)}</pre></p>
                            </div>
                        `);
                        $('#forecastResults').show();
                    }
                } else {
                    console.error('❌ Response data is missing');
                    console.error('❌ Full response:', response);
                    showError('❌ Data peramalan tidak valid. Response data tidak ditemukan.');
                }
            } else {
                const errorMsg = response && response.message ? response.message : 'Respons server tidak valid';
                console.error('❌ Forecast failed:', errorMsg);
                console.error('❌ Full response:', response);
                showError('❌ Peramalan gagal: ' + errorMsg);

                // Show debug info in UI
                $('#forecastResults').html(`
                    <div class="alert alert-warning">
                        <h5>⚠️ Debug Info:</h5>
                        <p><strong>Response:</strong> <pre>${JSON.stringify(response, null, 2)}</pre></p>
                    </div>
                `);
                $('#forecastResults').show();
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

            // Show error in UI for debugging
            showErrorInUI(`Request failed with status ${xhr.status}`, `${xhr.statusText} - ${error}`);

            console.error('❌ Full error response:', xhr);
            console.error('❌ Response headers:', xhr.getAllResponseHeaders());

            if (xhr.responseJSON) {
                console.error('❌ JSON response:', xhr.responseJSON);
            }

            $('#loadingResults').hide();
            $('#runForecastBtn').prop('disabled', false);
            $('#runForecastBtn').html('<i class="fas fa-play me-1"></i>Generate Forecast');

            let errorMessage = '❌ Terjadi kesalahan saat memproses peramalan';

            if (xhr.status === 0) {
                errorMessage = '❌ Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
            } else if (xhr.status === 404) {
                errorMessage = '❌ Endpoint tidak ditemukan (404). Periksa konfigurasi route.';
            } else if (xhr.status === 419) {
                errorMessage = '❌ CSRF Token tidak valid atau session expired. Silakan login ulang.';
            } else if (xhr.status === 401) {
                errorMessage = '❌ Anda harus login terlebih dahulu. Silakan login dan coba lagi.';
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

    const historicalScores = historicalData.map(item => item.total_score);

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
        tension: 0.4,
        fill: false
    });

    // Forecast datasets
    const colors = {
        linear_trend: '#007bff',
        moving_average: '#28a745',
        weighted_average: '#ffc107'
    };

    Object.keys(forecasts).forEach(method => {
        if (forecasts[method] && Array.isArray(forecasts[method])) {
            const forecastScores = forecasts[method].map(item => item.predicted_score);
            const forecastData = [...Array(historicalData.length).fill(null), ...forecastScores];

            datasets.push({
                label: method.replace('_', ' ').toUpperCase(),
                data: forecastData,
                borderColor: colors[method] || '#dc3545',
                backgroundColor: colors[method] ? colors[method] + '20' : '#dc354520',
                borderDash: [5, 5],
                pointStyle: 'triangle',
                tension: 0.4,
                fill: false
            });
        }
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
                        text: 'Performance Score'
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
    html += '<thead><tr><th>Period</th>';

    const forecasts = forecastResults.forecasts || {};
    const methods = Object.keys(forecasts);

    methods.forEach(method => {
        html += `<th>${method.replace('_', ' ').toUpperCase()}</th>`;
    });

    html += '</tr></thead><tbody>';

    const periodsAhead = parseInt($('#periodsAhead').val());

    for (let i = 1; i <= periodsAhead; i++) {
        html += `<tr><td>Forecast ${i}</td>`;

        methods.forEach(method => {
            const value = forecasts[method] && forecasts[method][i-1]
                ? forecasts[method][i-1].predicted_score.toFixed(2)
                : 'N/A';
            html += `<td>${value}</td>`;
        });

        html += '</tr>';
    }

    html += '</tbody></table></div>';
    $('#resultsContent').html(html);
}

function displayAnalysis() {
    if (!forecastResults) {
        console.log('❌ displayAnalysis: forecastResults is null/undefined');
        return;
    }

    console.log('🔍 displayAnalysis called with forecastResults:', forecastResults);
    console.log('🔍 Confidence intervals check:', forecastResults.confidence_intervals);
    console.log('🔍 Forecast accuracy check:', forecastResults.forecast_accuracy);
    console.log('🔍 forecastResults type:', typeof forecastResults);
    console.log('🔍 forecastResults keys:', Object.keys(forecastResults));

    let analysisHtml = '<div class="row g-3">';

    // Display confidence intervals
    if (forecastResults.confidence_intervals) {
        const intervals = forecastResults.confidence_intervals;
        analysisHtml += `
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Confidence Intervals</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Confidence Level:</strong> ${(intervals.confidence_level * 100).toFixed(0)}%</p>
                        ${intervals.lower_bound && intervals.upper_bound ?
                            `<p><strong>Range:</strong> ${intervals.lower_bound.toFixed(2)} - ${intervals.upper_bound.toFixed(2)}</p>` :
                            '<p><em>Confidence intervals calculated</em></p>'
                        }
                    </div>
                </div>
            </div>
        `;
    }

    // Display forecast accuracy
    if (forecastResults.forecast_accuracy) {
        const accuracy = forecastResults.forecast_accuracy;
        analysisHtml += `
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i>Forecast Accuracy</h6>
                    </div>
                    <div class="card-body">
                        ${accuracy.accuracy ? `<p><strong>Overall Accuracy:</strong> ${accuracy.accuracy}</p>` : ''}
                        ${accuracy.mape ? `<p><strong>MAPE:</strong> ${accuracy.mape.toFixed(2)}%</p>` : ''}
                        ${accuracy.accuracy === 'insufficient_data' ? '<p class="text-warning"><em>Insufficient data for accuracy calculation</em></p>' : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // Display execution time
    if (forecastResults.execution_time) {
        analysisHtml += `
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Analysis completed in:</strong> ${forecastResults.execution_time}ms
                </div>
            </div>
        `;
    }

    analysisHtml += '</div>';

    console.log('🔍 Generated analysis HTML:', analysisHtml);
    console.log('🔍 Setting analysisContent HTML...');

    $('#analysisContent').html(analysisHtml);

    console.log('🔍 analysisContent HTML after setting:', $('#analysisContent').html());
    console.log('🔍 analysisContent length after setting:', $('#analysisContent').html().length);

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

// Toast notification functions
function showSuccessToast(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback to basic alert
        alert('✅ ' + message);
    }
}

function showErrorToast(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    } else {
        // Fallback to basic alert
        alert('❌ ' + message);
    }
}

function showErrorInUI(message, details = null) {
    // Hide other sections
    $('#forecastResults').hide();
    $('#forecastAnalysis').hide();

    // Show error section
    let errorHtml = `
        <div class="alert alert-danger">
            <h5>❌ Error: ${message}</h5>
    `;

    if (details) {
        errorHtml += `<p><strong>Details:</strong> ${details}</p>`;
    }

    errorHtml += `
            <hr>
            <h6>Troubleshooting Steps:</h6>
            <ol>
                <li>Make sure you are logged in to the application</li>
                <li>Check if you have selected an employee</li>
                <li>Verify that at least one forecasting method is selected</li>
                <li>Try refreshing the page and try again</li>
                <li>Check browser console for more details</li>
            </ol>
            <button class="btn btn-info btn-sm" onclick="debugForecast()">
                <i class="fas fa-bug me-1"></i>Debug Information
            </button>
        </div>
    `;

    $('#errorContent').html(errorHtml);
    $('#errorDisplay').show();
}

function showInfoToast(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Info!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        // Fallback to basic alert
        alert('ℹ️ ' + message);
    }
}

function debugForecast() {
    console.log('🐛 Debug Forecast Function Called');

    // Check form data
    const formData = new FormData($('#forecastForm')[0]);
    const employeeId = formData.get('employee_id');
    const periodsAhead = formData.get('periods_ahead');
    const methods = [];
    $('input[name="methods[]"]:checked').each(function() {
        methods.push($(this).val());
    });
    const confidenceLevel = formData.get('confidence_level');

    console.log('🐛 Form Data Debug:');
    console.log('  - Employee ID:', employeeId);
    console.log('  - Periods Ahead:', periodsAhead);
    console.log('  - Methods:', methods);
    console.log('  - Confidence Level:', confidenceLevel);

    // Check CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log('🐛 CSRF Token:', csrfToken ? 'Available' : 'Missing');

    // Check authentication
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    console.log('🐛 Authentication:', isAuthenticated ? 'Logged In' : 'Not Logged In');

    // Check route
    const routeUrl = '{{ route("analysis.forecast") }}';
    console.log('🐛 Route URL:', routeUrl);

    // Show debug info in UI
    $('#forecastResults').html(`
        <div class="alert alert-info">
            <h5>🐛 Debug Information:</h5>
            <p><strong>Employee ID:</strong> ${employeeId || 'Not selected'}</p>
            <p><strong>Periods Ahead:</strong> ${periodsAhead || 'Not set'}</p>
            <p><strong>Methods:</strong> ${methods.length > 0 ? methods.join(', ') : 'None selected'}</p>
            <p><strong>Confidence Level:</strong> ${confidenceLevel || 'Not set'}</p>
            <p><strong>CSRF Token:</strong> ${csrfToken ? 'Available' : 'Missing'}</p>
            <p><strong>Authentication:</strong> ${isAuthenticated ? 'Logged In' : 'Not Logged In'}</p>
            <p><strong>Route URL:</strong> ${routeUrl}</p>
        </div>
    `);
    $('#forecastResults').show();

    showInfoToast('Debug information displayed. Check console for details.');
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
@endpush
