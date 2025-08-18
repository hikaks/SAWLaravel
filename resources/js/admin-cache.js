/**
 * Admin Cache Management JavaScript
 */

function clearCache() {
    Swal.fire({
        title: 'Clear System Cache?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will clear all application caches:</p>
                <ul class="text-muted">
                    <li>Application cache</li>
                    <li>Configuration cache</li>
                    <li>Route cache</li>
                    <li>View cache</li>
                </ul>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This may temporarily slow down the system until caches are rebuilt.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-broom me-1"></i>Clear Cache',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const clearUrl = window.appRoutes?.admin?.cache?.clear || '/admin/cache/clear';
            
            $.ajax({
                url: clearUrl,
                type: 'POST',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Clearing Cache...',
                        html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cache Cleared!',
                            html: `
                                <div class="text-start">
                                    <p class="mb-3">${response.message}</p>
                                    <div class="row g-2">
                                        ${Object.entries(response.cleared || {}).map(([key, value]) => 
                                            `<div class="col-6">
                                                <small class="text-muted">${key.replace('_', ' ')}:</small>
                                                <span class="badge bg-${value ? 'success' : 'danger'} ms-1">
                                                    ${value ? 'Cleared' : 'Failed'}
                                                </span>
                                            </div>`
                                        ).join('')}
                                    </div>
                                </div>
                            `,
                            timer: 5000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Failed to clear cache';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        }
    });
}

function warmupCache() {
    Swal.fire({
        title: 'Warmup System Cache?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will pre-populate system caches:</p>
                <ul class="text-muted">
                    <li>Configuration cache</li>
                    <li>Route cache</li>
                </ul>
                <div class="alert alert-success">
                    <i class="fas fa-fire me-2"></i>
                    This will improve system performance for all users.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-fire me-1"></i>Warmup Cache',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const warmupUrl = window.appRoutes?.admin?.cache?.warmup || '/admin/cache/warmup';
            
            $.ajax({
                url: warmupUrl,
                type: 'POST',
                data: {
                    _token: window.getCsrfToken ? window.getCsrfToken() : $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Warming Up Cache...',
                        html: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cache Warmed Up!',
                            html: `
                                <div class="text-start">
                                    <p class="mb-3">${response.message}</p>
                                    <div class="row g-2">
                                        ${Object.entries(response.warmed || {}).map(([key, value]) => 
                                            `<div class="col-6">
                                                <small class="text-muted">${key.replace('_', ' ')}:</small>
                                                <span class="badge bg-${value ? 'success' : 'danger'} ms-1">
                                                    ${value ? 'Warmed' : 'Failed'}
                                                </span>
                                            </div>`
                                        ).join('')}
                                    </div>
                                </div>
                            `,
                            timer: 5000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    if (window.utils?.handleAjaxError) {
                        window.utils.handleAjaxError(xhr);
                    } else {
                        let message = xhr.responseJSON?.message || 'Failed to warmup cache';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        }
    });
}

// System management functions
function optimizeDatabase() {
    Swal.fire({
        icon: 'info',
        title: 'Database Optimization',
        text: 'Database optimization tools will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function generateBackup() {
    Swal.fire({
        icon: 'info',
        title: 'Backup Generation',
        text: 'Automated backup generation will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function viewSystemLogs() {
    Swal.fire({
        icon: 'info',
        title: 'System Logs',
        text: 'System log viewer will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function refreshSystemStats() {
    // Reload the page to get fresh stats
    window.location.reload();
}