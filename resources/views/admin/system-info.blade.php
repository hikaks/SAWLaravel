@extends('layouts.main')

@section('title', 'System Information - Admin Dashboard')
@section('page-title', 'System Information')

@section('content')
<div class="row">
    <!-- Basic System Info -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server text-primary me-2"></i>
                    Basic System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-card p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">PHP Version</h6>
                                    <p class="mb-0 text-muted">{{ $systemInfo['php_version'] }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ version_compare($systemInfo['php_version'], '8.0', '>=') ? 'success' : 'warning' }}">
                                        {{ version_compare($systemInfo['php_version'], '8.0', '>=') ? 'Modern' : 'Legacy' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-card p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Laravel Version</h6>
                                    <p class="mb-0 text-muted">{{ $systemInfo['laravel_version'] }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success">Framework</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-card p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Database Version</h6>
                                    <p class="mb-0 text-muted">{{ $systemInfo['database_version'] }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info">{{ ucfirst($extendedInfo['database_info']['connection']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-card p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Environment</h6>
                                    <p class="mb-0 text-muted">{{ ucfirst($systemInfo['environment']) }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $systemInfo['environment'] === 'production' ? 'danger' : 'warning' }}">
                                        {{ $systemInfo['debug_mode'] ? 'Debug ON' : 'Debug OFF' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Information -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-globe text-info me-2"></i>
                    Server Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Server Software:</td>
                            <td><strong>{{ $systemInfo['server_software'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Server Name:</td>
                            <td><strong>{{ $extendedInfo['server_info']['server_name'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Server Port:</td>
                            <td><strong>{{ $extendedInfo['server_info']['server_port'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">HTTPS:</td>
                            <td>
                                <span class="badge bg-{{ $extendedInfo['server_info']['https'] ? 'success' : 'warning' }}">
                                    {{ $extendedInfo['server_info']['https'] ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Document Root:</td>
                            <td><code class="small">{{ $extendedInfo['server_info']['document_root'] }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Timezone:</td>
                            <td><strong>{{ $systemInfo['timezone'] }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PHP Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fab fa-php text-purple me-2"></i>
                    PHP Configuration
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Memory Limit:</td>
                            <td><strong>{{ $systemInfo['memory_limit'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Max Execution Time:</td>
                            <td><strong>{{ $systemInfo['max_execution_time'] }}s</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Upload Max Size:</td>
                            <td><strong>{{ $systemInfo['upload_max_filesize'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Post Max Size:</td>
                            <td><strong>{{ $extendedInfo['php_ini']['post_max_size'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Max Input Vars:</td>
                            <td><strong>{{ $extendedInfo['php_ini']['max_input_vars'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Default Timezone:</td>
                            <td><strong>{{ $extendedInfo['php_ini']['default_timezone'] ?: 'Not Set' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Display Errors:</td>
                            <td>
                                <span class="badge bg-{{ $extendedInfo['php_ini']['display_errors'] ? 'warning' : 'success' }}">
                                    {{ $extendedInfo['php_ini']['display_errors'] ? 'ON' : 'OFF' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Laravel Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs text-danger me-2"></i>
                    Laravel Configuration
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">App Name:</td>
                            <td><strong>{{ $extendedInfo['laravel_config']['app_name'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">App URL:</td>
                            <td><a href="{{ $extendedInfo['laravel_config']['app_url'] }}" target="_blank">{{ $extendedInfo['laravel_config']['app_url'] }}</a></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Cache Driver:</td>
                            <td><strong>{{ ucfirst($extendedInfo['laravel_config']['cache_driver']) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Session Driver:</td>
                            <td><strong>{{ ucfirst($extendedInfo['laravel_config']['session_driver']) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Queue Connection:</td>
                            <td><strong>{{ ucfirst($extendedInfo['laravel_config']['queue_connection']) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Mail Driver:</td>
                            <td><strong>{{ ucfirst($extendedInfo['laravel_config']['mail_driver']) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Database Information -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-database text-success me-2"></i>
                    Database Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Connection:</td>
                            <td><strong>{{ ucfirst($extendedInfo['database_info']['connection']) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Host:</td>
                            <td><strong>{{ $extendedInfo['database_info']['host'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Port:</td>
                            <td><strong>{{ $extendedInfo['database_info']['port'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Database:</td>
                            <td><strong>{{ $extendedInfo['database_info']['database'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Version:</td>
                            <td><strong>{{ $systemInfo['database_version'] }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Disk Usage -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-hdd text-warning me-2"></i>
                    Disk Usage
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Disk Usage</span>
                        <span><strong>{{ $extendedInfo['disk_usage']['usage_percentage'] }}%</strong></span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-{{ $extendedInfo['disk_usage']['usage_percentage'] > 80 ? 'danger' : ($extendedInfo['disk_usage']['usage_percentage'] > 60 ? 'warning' : 'success') }}" 
                             role="progressbar" 
                             style="width: {{ $extendedInfo['disk_usage']['usage_percentage'] }}%">
                            {{ $extendedInfo['disk_usage']['usage_percentage'] }}%
                        </div>
                    </div>
                </div>
                
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Total Space:</td>
                            <td><strong>{{ $extendedInfo['disk_usage']['total'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Used Space:</td>
                            <td><strong>{{ $extendedInfo['disk_usage']['used'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Free Space:</td>
                            <td><strong>{{ $extendedInfo['disk_usage']['free'] }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-puzzle-piece text-secondary me-2"></i>
                        PHP Extensions ({{ count($extendedInfo['php_extensions']) }})
                    </h5>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#phpExtensions">
                        <i class="fas fa-eye me-1"></i>Show/Hide
                    </button>
                </div>
            </div>
            <div class="card-body collapse" id="phpExtensions">
                <div class="row">
                    @foreach($extendedInfo['php_extensions'] as $extension)
                        <div class="col-md-3 col-sm-4 col-6 mb-2">
                            <span class="badge bg-light text-dark border">{{ $extension }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export System Info -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-download text-primary me-2"></i>
                    Export System Information
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Export system information for troubleshooting or documentation purposes.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" onclick="exportSystemInfo('json')">
                        <i class="fas fa-file-code me-1"></i>Export as JSON
                    </button>
                    <button class="btn btn-success" onclick="exportSystemInfo('txt')">
                        <i class="fas fa-file-alt me-1"></i>Export as Text
                    </button>
                    <button class="btn btn-info" onclick="copySystemInfo()">
                        <i class="fas fa-copy me-1"></i>Copy to Clipboard
                    </button>
                    <button class="btn btn-warning" onclick="emailSystemInfo()">
                        <i class="fas fa-envelope me-1"></i>Email Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .info-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    }
    
    .table td {
        border-top: 1px solid #f8f9fa;
        padding: 0.5rem;
    }
    
    .table tr:first-child td {
        border-top: none;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
        font-weight: bold;
        font-size: 0.8rem;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 0.85em;
    }
</style>
@endpush

@push('scripts')
<script>
function exportSystemInfo(format) {
    const data = {
        basic: @json($systemInfo),
        extended: @json($extendedInfo),
        timestamp: new Date().toISOString(),
        exported_by: '{{ auth()->user()->name ?? "System" }}'
    };
    
    if (format === 'json') {
        downloadFile(JSON.stringify(data, null, 2), 'system-info.json', 'application/json');
    } else if (format === 'txt') {
        let textContent = 'SYSTEM INFORMATION REPORT\n';
        textContent += '='.repeat(50) + '\n\n';
        textContent += `Generated: ${new Date().toLocaleString()}\n`;
        textContent += `Exported by: ${data.exported_by}\n\n`;
        
        // Basic Info
        textContent += 'BASIC INFORMATION:\n';
        textContent += '-'.repeat(20) + '\n';
        Object.entries(data.basic).forEach(([key, value]) => {
            textContent += `${key.replace('_', ' ').toUpperCase()}: ${value}\n`;
        });
        
        textContent += '\nSERVER INFORMATION:\n';
        textContent += '-'.repeat(20) + '\n';
        Object.entries(data.extended.server_info).forEach(([key, value]) => {
            textContent += `${key.replace('_', ' ').toUpperCase()}: ${value}\n`;
        });
        
        textContent += '\nLARAVEL CONFIGURATION:\n';
        textContent += '-'.repeat(25) + '\n';
        Object.entries(data.extended.laravel_config).forEach(([key, value]) => {
            textContent += `${key.replace('_', ' ').toUpperCase()}: ${value}\n`;
        });
        
        textContent += '\nDATABASE INFORMATION:\n';
        textContent += '-'.repeat(25) + '\n';
        Object.entries(data.extended.database_info).forEach(([key, value]) => {
            textContent += `${key.replace('_', ' ').toUpperCase()}: ${value}\n`;
        });
        
        textContent += '\nDISK USAGE:\n';
        textContent += '-'.repeat(15) + '\n';
        Object.entries(data.extended.disk_usage).forEach(([key, value]) => {
            textContent += `${key.replace('_', ' ').toUpperCase()}: ${value}\n`;
        });
        
        downloadFile(textContent, 'system-info.txt', 'text/plain');
    }
}

function downloadFile(content, filename, contentType) {
    const blob = new Blob([content], { type: contentType });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    showAlert('success', `System information exported as ${filename}`);
}

function copySystemInfo() {
    const data = {
        basic: @json($systemInfo),
        extended: @json($extendedInfo),
        timestamp: new Date().toISOString()
    };
    
    const textContent = JSON.stringify(data, null, 2);
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(textContent).then(() => {
            showAlert('success', 'System information copied to clipboard!');
        }).catch(() => {
            fallbackCopyToClipboard(textContent);
        });
    } else {
        fallbackCopyToClipboard(textContent);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showAlert('success', 'System information copied to clipboard!');
    } catch (err) {
        showAlert('error', 'Failed to copy to clipboard');
    }
    
    document.body.removeChild(textArea);
}

function emailSystemInfo() {
    showAlert('info', 'Email system info feature will be implemented soon.');
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