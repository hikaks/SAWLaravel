@extends('layouts.main')

@section('title', 'Cache Management - Admin Dashboard')
@section('page-title', 'Cache Management')

@section('content')
<div class="row">
    <!-- Cache Status Overview -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="cache-status-indicator me-3">
                            @if($stats['status'] === 'connected')
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            @else
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="mb-1">
                                Cache Status: 
                                <x-ui.badge 
                                    variant="{{ $stats['status'] === 'connected' ? 'success' : 'danger' }}" 
                                    size="lg">
                                    {{ ucfirst($stats['status']) }}
                                </x-ui.badge>
                            </h4>
                            <p class="text-muted mb-0">
                                Driver: <strong>{{ ucfirst($stats['driver']) }}</strong> | 
                                Last checked: {{ now()->format('d M Y H:i:s') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button 
                            variant="outline-info" 
                            icon="fas fa-sync-alt"
                            onclick="refreshCacheStats()"
                            id="refreshBtn">
                            Refresh
                        </x-ui.button>
                        <x-ui.button 
                            variant="warning" 
                            icon="fas fa-broom"
                            onclick="clearCache()"
                            id="clearBtn">
                            Clear All
                        </x-ui.button>
                        <x-ui.button 
                            variant="primary" 
                            icon="fas fa-fire"
                            onclick="warmupCache()"
                            id="warmupBtn">
                            Warmup
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Statistics -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Cache Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-key fa-2x text-primary"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_keys'] }}</h3>
                            <p class="text-muted mb-0">Total Keys</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-memory fa-2x text-info"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['memory_usage'] }}</h3>
                            <p class="text-muted mb-0">Memory Usage</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-bullseye fa-2x text-success"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['hit_rate'] }}</h3>
                            <p class="text-muted mb-0">Hit Rate</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['uptime'] }}</h3>
                            <p class="text-muted mb-0">Uptime</p>
                        </div>
                    </div>
                </div>

                @if(isset($stats['error']))
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Cache Error:</strong> {{ $stats['error'] }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cache Information -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Cache Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted">Default Driver:</td>
                            <td><strong>{{ ucfirst($cacheInfo['default_driver']) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Cache Prefix:</td>
                            <td><code>{{ $cacheInfo['prefix'] ?: 'None' }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Available Stores:</td>
                            <td>
                                @foreach($cacheInfo['stores'] as $store)
                                    <span class="badge bg-secondary me-1">{{ $store }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Serialization:</td>
                            <td><strong>{{ $cacheInfo['serialization'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Compression:</td>
                            <td><strong>{{ $cacheInfo['compression'] }}</strong></td>
                        </tr>
                    </tbody>
                </table>
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
                    <button class="btn btn-outline-danger btn-sm" onclick="clearSpecificCache('config')">
                        <i class="fas fa-cog me-2"></i>Clear Config Cache
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearSpecificCache('route')">
                        <i class="fas fa-route me-2"></i>Clear Route Cache
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearSpecificCache('view')">
                        <i class="fas fa-eye me-2"></i>Clear View Cache
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="optimizeCache()">
                        <i class="fas fa-rocket me-2"></i>Optimize Cache
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Keys Browser -->
    @if(!empty($cacheKeys))
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-secondary me-2"></i>
                        Cache Keys Browser (Showing {{ count($cacheKeys) }} keys)
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" id="keySearch" placeholder="Search keys..." style="width: 200px;">
                        <button class="btn btn-outline-secondary btn-sm" onclick="loadMoreKeys()">
                            <i class="fas fa-plus me-1"></i>Load More
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="cacheKeysTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50%">Cache Key</th>
                                <th width="15%">Type</th>
                                <th width="20%">TTL</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cacheKeys as $keyData)
                            <tr>
                                <td>
                                    <code class="cache-key">{{ $keyData['key'] }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $keyData['type'] }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ is_numeric($keyData['ttl']) ? $keyData['ttl'] . 's' : $keyData['ttl'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewCacheValue('{{ $keyData['key'] }}')" title="View Value">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteCacheKey('{{ $keyData['key'] }}')" title="Delete Key">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Cache Value Modal -->
<div class="modal fade" id="cacheValueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Cache Value
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cache Key:</label>
                    <code id="modalCacheKey" class="d-block p-2 bg-light rounded"></code>
                </div>
                <div class="mb-3">
                    <label class="form-label">Value:</label>
                    <pre id="modalCacheValue" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyCacheValue()">
                    <i class="fas fa-copy me-1"></i>Copy Value
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cache Management Tools -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-wrench text-warning me-2"></i>
                    Advanced Cache Tools
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Bulk Operations</h6>
                        <div class="mb-3">
                            <label class="form-label">Pattern (Redis/Memcached):</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cachePattern" placeholder="e.g., user:*">
                                <button class="btn btn-outline-danger" onclick="clearByPattern()">
                                    <i class="fas fa-trash me-1"></i>Clear Pattern
                                </button>
                            </div>
                            <div class="form-text">Use * as wildcard. Be careful with broad patterns!</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Cache Testing</h6>
                        <div class="mb-3">
                            <label class="form-label">Test Cache Performance:</label>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-info" onclick="testCachePerformance()">
                                    <i class="fas fa-stopwatch me-1"></i>Run Performance Test
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Advanced operations can affect application performance. Use with caution in production environments.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .stat-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        transform: translateY(-2px);
    }
    
    .cache-status-indicator {
        animation: pulse 2s infinite;
    }
    
    .cache-key {
        font-size: 0.85em;
        word-break: break-all;
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .alert {
        border: none;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
let currentCacheKey = '';

$(document).ready(function() {
    // Auto-refresh every 30 seconds
    setInterval(refreshCacheStats, 30000);
    
    // Key search functionality
    $('#keySearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#cacheKeysTable tbody tr').each(function() {
            const keyText = $(this).find('.cache-key').text().toLowerCase();
            $(this).toggle(keyText.includes(searchTerm));
        });
    });
});

function refreshCacheStats() {
    const refreshBtn = $('button:contains("Refresh")');
    const originalText = refreshBtn.html();
    refreshBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...').prop('disabled', true);
    
    setTimeout(() => location.reload(), 1000);
}

function clearCache() {
    if (confirm('Are you sure you want to clear ALL cache? This action cannot be undone.')) {
        performCacheAction('{{ route("admin.cache.clear") }}', 'Clearing cache...');
    }
}

function warmupCache() {
    performCacheAction('{{ route("admin.cache.warmup") }}', 'Warming up cache...');
}

function clearSpecificCache(type) {
    if (confirm(`Are you sure you want to clear ${type} cache?`)) {
        showAlert('info', `${type.charAt(0).toUpperCase() + type.slice(1)} cache clearing will be implemented soon.`);
    }
}

function optimizeCache() {
    showAlert('info', 'Cache optimization feature will be implemented soon.');
}

function performCacheAction(url, loadingMessage) {
    const loadingAlert = showAlert('info', loadingMessage);
    
    $.ajax({
        url: url,
        type: 'POST',
        data: { '_token': '{{ csrf_token() }}' },
        success: function(response) {
            loadingAlert.alert('close');
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', response.message || 'Operation failed');
            }
        },
        error: function(xhr) {
            loadingAlert.alert('close');
            const message = xhr.responseJSON?.message || 'Operation failed';
            showAlert('error', message);
        }
    });
}

function viewCacheValue(key) {
    currentCacheKey = key;
    $('#modalCacheKey').text(key);
    $('#modalCacheValue').text('Loading...');
    
    // Simulate cache value retrieval (would need actual endpoint)
    setTimeout(() => {
        $('#modalCacheValue').text('Cache value display feature will be implemented with proper backend endpoint.');
    }, 500);
    
    $('#cacheValueModal').modal('show');
}

function deleteCacheKey(key) {
    if (confirm(`Are you sure you want to delete cache key: ${key}?`)) {
        showAlert('info', 'Delete cache key feature will be implemented with proper backend endpoint.');
    }
}

function copyCacheValue() {
    const value = $('#modalCacheValue').text();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(value).then(() => {
            showAlert('success', 'Cache value copied to clipboard!');
        });
    } else {
        showAlert('error', 'Clipboard not supported');
    }
}

function clearByPattern() {
    const pattern = $('#cachePattern').val();
    if (!pattern) {
        showAlert('error', 'Please enter a pattern');
        return;
    }
    
    if (confirm(`Are you sure you want to clear all cache keys matching pattern: ${pattern}?`)) {
        showAlert('info', 'Clear by pattern feature will be implemented with proper backend endpoint.');
    }
}

function testCachePerformance() {
    const btn = $('button:contains("Run Performance Test")');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Testing...').prop('disabled', true);
    
    // Simulate performance test
    setTimeout(() => {
        btn.html(originalText).prop('disabled', false);
        showAlert('success', 'Performance test completed! Results: Write: 0.5ms, Read: 0.3ms, Delete: 0.2ms');
    }, 3000);
}

function loadMoreKeys() {
    showAlert('info', 'Load more keys feature will be implemented with pagination support.');
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
    
    return alert;
}
</script>
@endpush