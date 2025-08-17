@extends('layouts.main')

@section('title', 'System Health Check - Admin Dashboard')
@section('page-title', 'System Health Check')

@section('content')
<div class="row">
    <!-- Overall Status Card -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator status-{{ $health['overall'] }} me-3">
                            @if($health['overall'] === 'healthy')
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            @elseif($health['overall'] === 'warning')
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            @else
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="mb-1">
                                System Status: 
                                <span class="badge bg-{{ $health['overall'] === 'healthy' ? 'success' : ($health['overall'] === 'warning' ? 'warning' : 'danger') }} fs-6">
                                    {{ ucfirst($health['overall']) }}
                                </span>
                            </h4>
                            <p class="text-muted mb-0">
                                Last checked: {{ now()->format('d M Y H:i:s') }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="refreshHealthCheck()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Components -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat text-danger me-2"></i>
                    System Components Health
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Database Health -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-database fa-lg me-3 text-primary"></i>
                                    <div>
                                        <h6 class="mb-0">Database</h6>
                                        <small class="text-muted">MySQL Connection</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($health['database'] === 'healthy')
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <div class="small text-success">Connected</div>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        <div class="small text-danger">Error</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cache Health -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-memory fa-lg me-3 text-info"></i>
                                    <div>
                                        <h6 class="mb-0">Cache System</h6>
                                        <small class="text-muted">Redis/File Cache</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($health['cache'] === 'healthy')
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <div class="small text-success">Working</div>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        <div class="small text-danger">Error</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Health -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hdd fa-lg me-3 text-warning"></i>
                                    <div>
                                        <h6 class="mb-0">File Storage</h6>
                                        <small class="text-muted">Disk Read/Write</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($health['storage'] === 'healthy')
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <div class="small text-success">Accessible</div>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        <div class="small text-danger">Error</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Queue Health -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-tasks fa-lg me-3 text-secondary"></i>
                                    <div>
                                        <h6 class="mb-0">Queue System</h6>
                                        <small class="text-muted">Background Jobs</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($health['queue'] === 'healthy')
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <div class="small text-success">Running</div>
                                    @elseif($health['queue'] === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                                        <div class="small text-warning">Issues</div>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        <div class="small text-danger">Error</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disk Space -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-server fa-lg me-3 text-dark"></i>
                                    <div>
                                        <h6 class="mb-0">Disk Space</h6>
                                        <small class="text-muted">Available Storage</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($health['disk_space'] === 'healthy')
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <div class="small text-success">Sufficient</div>
                                    @elseif($health['disk_space'] === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                                        <div class="small text-warning">Low Space</div>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                        <div class="small text-danger">Critical</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="col-md-6">
                        <div class="health-item p-3 border rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-microchip fa-lg me-3 text-success"></i>
                                    <div>
                                        <h6 class="mb-0">Memory Usage</h6>
                                        <small class="text-muted">Current: {{ $health['memory_usage']['current'] }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <i class="fas fa-info-circle text-info fa-lg"></i>
                                    <div class="small text-info">{{ $health['memory_usage']['peak'] }} Peak</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="system-info">
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">PHP Version:</span>
                            <strong>{{ $systemInfo['php_version'] }}</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Laravel Version:</span>
                            <strong>{{ $systemInfo['laravel_version'] }}</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Database:</span>
                            <strong>{{ Str::limit($systemInfo['database_version'], 20) }}</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Environment:</span>
                            <span class="badge bg-{{ $systemInfo['environment'] === 'production' ? 'danger' : 'warning' }}">
                                {{ ucfirst($systemInfo['environment']) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Debug Mode:</span>
                            <span class="badge bg-{{ $systemInfo['debug_mode'] ? 'warning' : 'success' }}">
                                {{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Memory Limit:</span>
                            <strong>{{ $systemInfo['memory_limit'] }}</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Max Execution:</span>
                            <strong>{{ $systemInfo['max_execution_time'] }}s</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Upload Limit:</span>
                            <strong>{{ $systemInfo['upload_max_filesize'] }}</strong>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Timezone:</span>
                            <strong>{{ $systemInfo['timezone'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools text-primary me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>Clear Cache
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="warmupCache()">
                        <i class="fas fa-fire me-2"></i>Warmup Cache
                    </button>
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-tasks me-2"></i>View Jobs
                    </a>
                    <button class="btn btn-outline-warning btn-sm" onclick="downloadLogs()">
                        <i class="fas fa-download me-2"></i>Download Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Health Check History -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    Health Check History
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Health check history will be implemented in future updates. This will show system health trends over time.
                </div>
                
                <!-- Placeholder for health history chart -->
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chart-area fa-3x mb-3"></i>
                    <p>Health monitoring chart will appear here</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .health-item {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .health-item:hover {
        border-color: #007bff !important;
        box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    }
    
    .status-indicator {
        animation: pulse 2s infinite;
    }
    
    .status-healthy .status-indicator {
        animation: none;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .info-item {
        border-bottom: 1px solid #f8f9fa;
        padding-bottom: 0.5rem;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0 !important;
    }
    
    .system-info {
        font-size: 0.9rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setInterval(function() {
        refreshHealthCheck();
    }, 30000);
});

function refreshHealthCheck() {
    // Show loading indicator
    const refreshBtn = $('button:contains("Refresh")');
    const originalText = refreshBtn.html();
    refreshBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...').prop('disabled', true);
    
    // Reload the page to get fresh data
    setTimeout(function() {
        location.reload();
    }, 1000);
}

function clearCache() {
    if (confirm('Are you sure you want to clear the application cache?')) {
        $.ajax({
            url: '{{ route("admin.cache.clear") }}',
            type: 'POST',
            data: {
                '_token': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Cache cleared successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', 'Failed to clear cache: ' + response.message);
                }
            },
            error: function() {
                showAlert('error', 'Failed to clear cache. Please try again.');
            }
        });
    }
}

function warmupCache() {
    const btn = $('button:contains("Warmup Cache")');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Warming up...').prop('disabled', true);
    
    $.ajax({
        url: '{{ route("admin.cache.warmup") }}',
        type: 'POST',
        data: {
            '_token': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Cache warmed up successfully!');
            } else {
                showAlert('error', 'Failed to warmup cache: ' + response.message);
            }
        },
        error: function() {
            showAlert('error', 'Failed to warmup cache. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

function downloadLogs() {
    showAlert('info', 'Log download feature will be implemented soon.');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
    const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        alert.alert('close');
    }, 5000);
}
</script>
@endpush