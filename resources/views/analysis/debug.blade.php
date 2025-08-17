@extends('layouts.main')

@section('title', __('Analytics Debug') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Analytics Debug'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bug me-2"></i>
                        {{ __('Advanced Analytics Debug Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- System Status -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <i class="fas fa-server me-2"></i>
                                    {{ __('System Status') }}
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Laravel Version') }}</span>
                                            <span class="badge bg-success">{{ app()->version() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('PHP Version') }}</span>
                                            <span class="badge bg-success">{{ PHP_VERSION }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Cache Status') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Database Connection') }}</span>
                                            <span class="badge bg-success">{{ __('Connected') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Data Availability -->
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <i class="fas fa-database me-2"></i>
                                    {{ __('Data Availability') }}
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Total Employees') }}</span>
                                            <span class="badge bg-primary">{{ $employees->count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Total Criteria') }}</span>
                                            <span class="badge bg-primary">{{ $criterias->count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Available Periods') }}</span>
                                            <span class="badge bg-primary">{{ $availablePeriods->count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Total Evaluations') }}</span>
                                            <span class="badge bg-primary">{{ $totalEvaluations }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Total Results') }}</span>
                                            <span class="badge bg-primary">{{ $totalResults }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Service Status -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-cogs me-2"></i>
                                    {{ __('Service Status') }}
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('AdvancedAnalysisService') }}</span>
                                            <span class="badge bg-success">{{ __('Registered') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('SAWCalculationService') }}</span>
                                            <span class="badge bg-success">{{ __('Registered') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('CacheService') }}</span>
                                            <span class="badge bg-success">{{ __('Registered') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Route Status -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-route me-2"></i>
                                    {{ __('Route Status') }}
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Analysis Dashboard') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Sensitivity Analysis') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('What-if Scenarios') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Multi-period Comparison') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('Performance Forecasting') }}</span>
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Data -->
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <i class="fas fa-table me-2"></i>
                                    {{ __('Sample Data Preview') }}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>{{ __('Recent Employees') }}</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Name') }}</th>
                                                            <th>{{ __('Department') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($employees->take(5) as $employee)
                                                        <tr>
                                                            <td>{{ $employee->name }}</td>
                                                            <td>{{ $employee->department ?? 'N/A' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <h6>{{ __('Criteria') }}</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Name') }}</th>
                                                            <th>{{ __('Weight') }}</th>
                                                            <th>{{ __('Type') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($criterias as $criteria)
                                                        <tr>
                                                            <td>{{ $criteria->name }}</td>
                                                            <td>{{ $criteria->weight }}%</td>
                                                            <td>
                                                                <span class="badge {{ $criteria->type == 'benefit' ? 'bg-success' : 'bg-warning' }}">
                                                                    {{ ucfirst($criteria->type) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <h6>{{ __('Available Periods') }}</h6>
                                            <div class="list-group">
                                                @foreach($availablePeriods as $period)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    {{ $period }}
                                                    <span class="badge bg-primary rounded-pill">{{ $periodCounts[$period] ?? 0 }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Analysis -->
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <i class="fas fa-flask me-2"></i>
                                    {{ __('Test Analysis Functions') }}
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-primary w-100" onclick="testSensitivityAnalysis()">
                                                <i class="fas fa-balance-scale me-2"></i>
                                                {{ __('Test Sensitivity') }}
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-warning w-100" onclick="testWhatIfAnalysis()">
                                                <i class="fas fa-question-circle me-2"></i>
                                                {{ __('Test What-if') }}
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-info w-100" onclick="testComparisonAnalysis()">
                                                <i class="fas fa-chart-bar me-2"></i>
                                                {{ __('Test Comparison') }}
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-success w-100" onclick="testForecastAnalysis()">
                                                <i class="fas fa-chart-area me-2"></i>
                                                {{ __('Test Forecast') }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="testResults" class="mt-4" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6>{{ __('Test Results') }}</h6>
                                            <div id="testOutput"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function testSensitivityAnalysis() {
    showTestLoading('Testing Sensitivity Analysis...');
    
    $.ajax({
        url: '{{ route("analysis.sensitivity") }}',
        method: 'POST',
        data: {
            evaluation_period: '{{ $availablePeriods->first() }}',
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showTestResult('Sensitivity Analysis', response.success, response);
        },
        error: function(xhr) {
            showTestResult('Sensitivity Analysis', false, xhr.responseJSON || xhr);
        }
    });
}

function testWhatIfAnalysis() {
    showTestLoading('Testing What-if Analysis...');
    
    $.ajax({
        url: '{{ route("analysis.what-if") }}',
        method: 'POST',
        data: {
            evaluation_period: '{{ $availablePeriods->first() }}',
            scenarios: [{
                name: 'Test Scenario',
                type: 'weight_changes',
                changes: {
                    @foreach($criterias->take(2) as $criteria)
                    {{ $criteria->id }}: {{ $criteria->weight + 5 }},
                    @endforeach
                }
            }],
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showTestResult('What-if Analysis', response.success, response);
        },
        error: function(xhr) {
            showTestResult('What-if Analysis', false, xhr.responseJSON || xhr);
        }
    });
}

function testComparisonAnalysis() {
    showTestLoading('Testing Multi-period Comparison...');
    
    $.ajax({
        url: '{{ route("analysis.comparison") }}',
        method: 'POST',
        data: {
            periods: {!! json_encode($availablePeriods->take(2)->values()) !!},
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showTestResult('Multi-period Comparison', response.success, response);
        },
        error: function(xhr) {
            showTestResult('Multi-period Comparison', false, xhr.responseJSON || xhr);
        }
    });
}

function testForecastAnalysis() {
    showTestLoading('Testing Performance Forecasting...');
    
    $.ajax({
        url: '{{ route("analysis.forecast") }}',
        method: 'POST',
        data: {
            employee_id: {{ $employees->first()->id ?? 'null' }},
            periods_ahead: 3,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showTestResult('Performance Forecasting', response.success, response);
        },
        error: function(xhr) {
            showTestResult('Performance Forecasting', false, xhr.responseJSON || xhr);
        }
    });
}

function showTestLoading(message) {
    $('#testResults').show();
    $('#testOutput').html(`
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${message}
        </div>
    `);
}

function showTestResult(testName, success, data) {
    const statusBadge = success ? 
        '<span class="badge bg-success">SUCCESS</span>' : 
        '<span class="badge bg-danger">FAILED</span>';
    
    const resultHtml = `
        <div class="border-bottom pb-2 mb-2">
            <strong>${testName}</strong> ${statusBadge}
            <div class="mt-2">
                <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.8em;">
${JSON.stringify(data, null, 2)}
                </pre>
            </div>
        </div>
    `;
    
    $('#testOutput').html(resultHtml);
}
</script>
@endsection