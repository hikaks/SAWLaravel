@extends('layouts.main')

@section('title', 'Job Details - Admin Dashboard')
@section('page-title', 'Job Details')

@section('content')
<div class="row">
    <!-- Job Overview -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="job-status-indicator me-3">
                            @if($jobDetails['status'] === 'failed')
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            @elseif($jobDetails['status'] === 'running')
                                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            @else
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="mb-1">
                                Job #{{ $jobDetails['id'] }}
                                <span class="badge bg-{{ $jobDetails['status'] === 'failed' ? 'danger' : ($jobDetails['status'] === 'running' ? 'primary' : 'warning') }} fs-6">
                                    {{ ucfirst($jobDetails['status']) }}
                                </span>
                            </h4>
                            <p class="text-muted mb-0">
                                <strong>{{ $jobDetails['type'] ?? $jobDetails['job_class'] }}</strong> | 
                                Queue: {{ $jobDetails['queue'] }} | 
                                Attempts: {{ $jobDetails['attempts'] }}
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Jobs
                        </a>
                        @if($jobDetails['status'] === 'failed')
                            <button class="btn btn-warning" onclick="retryJob({{ $jobDetails['id'] }})">
                                <i class="fas fa-redo me-1"></i>Retry Job
                            </button>
                        @endif
                        <button class="btn btn-outline-info" onclick="refreshJobDetails()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Information -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Job Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Job ID:</td>
                                    <td><strong>#{{ $jobDetails['id'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Job Class:</td>
                                    <td><code>{{ $jobDetails['job_class'] }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Job Type:</td>
                                    <td><strong>{{ $jobDetails['type'] ?? 'Generic Job' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Queue:</td>
                                    <td><span class="badge bg-info">{{ $jobDetails['queue'] }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status:</td>
                                    <td>
                                        <span class="badge bg-{{ $jobDetails['status'] === 'failed' ? 'danger' : ($jobDetails['status'] === 'running' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($jobDetails['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Attempts:</td>
                                    <td><strong>{{ $jobDetails['attempts'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created At:</td>
                                    <td><strong>{{ $jobDetails['created_at'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Reserved At:</td>
                                    <td><strong>{{ $jobDetails['reserved_at'] ?? 'Not reserved' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Failed At:</td>
                                    <td><strong>{{ $jobDetails['failed_at'] ?? 'Not failed' }}</strong></td>
                                </tr>
                                @if(isset($jobDetails['period']))
                                <tr>
                                    <td class="text-muted">Period:</td>
                                    <td><strong>{{ $jobDetails['period'] }}</strong></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job-Specific Information -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs text-secondary me-2"></i>
                    Specific Details
                </h5>
            </div>
            <div class="card-body">
                @if(isset($jobDetails['type']))
                    @if($jobDetails['type'] === 'SAW Calculation')
                        <div class="mb-3">
                            <h6 class="text-primary">
                                <i class="fas fa-calculator me-1"></i>SAW Calculation Job
                            </h6>
                            <p class="text-muted mb-2">This job calculates Simple Additive Weighting for employee evaluations.</p>
                            @if(isset($jobDetails['period']))
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Evaluation Period:</span>
                                    <strong>{{ $jobDetails['period'] }}</strong>
                                </div>
                            @endif
                        </div>
                    @elseif($jobDetails['type'] === 'Report Generation')
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="fas fa-file-alt me-1"></i>Report Generation Job
                            </h6>
                            <p class="text-muted mb-2">This job generates evaluation reports and exports.</p>
                            @if(isset($jobDetails['report_type']))
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Report Type:</span>
                                    <strong>{{ $jobDetails['report_type'] }}</strong>
                                </div>
                            @endif
                            @if(isset($jobDetails['period']))
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Period:</span>
                                    <strong>{{ $jobDetails['period'] }}</strong>
                                </div>
                            @endif
                        </div>
                    @elseif($jobDetails['type'] === 'Notification')
                        <div class="mb-3">
                            <h6 class="text-info">
                                <i class="fas fa-bell me-1"></i>Notification Job
                            </h6>
                            <p class="text-muted mb-2">This job sends notifications to users.</p>
                            @if(isset($jobDetails['notification_type']))
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Type:</span>
                                    <strong>{{ $jobDetails['notification_type'] }}</strong>
                                </div>
                            @endif
                            @if(isset($jobDetails['recipients_count']))
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Recipients:</span>
                                    <strong>{{ $jobDetails['recipients_count'] }}</strong>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="mb-3">
                        <h6 class="text-secondary">
                            <i class="fas fa-cog me-1"></i>Generic Job
                        </h6>
                        <p class="text-muted">This is a background job with no specific type classification.</p>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="mt-4">
                    <h6>Quick Actions</h6>
                    <div class="d-grid gap-2">
                        @if($jobDetails['status'] === 'failed')
                            <button class="btn btn-warning btn-sm" onclick="retryJob({{ $jobDetails['id'] }})">
                                <i class="fas fa-redo me-1"></i>Retry Job
                            </button>
                        @endif
                        <button class="btn btn-outline-info btn-sm" onclick="viewJobLogs({{ $jobDetails['id'] }})">
                            <i class="fas fa-file-alt me-1"></i>View Logs
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportJobDetails()">
                            <i class="fas fa-download me-1"></i>Export Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Payload -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-code text-warning me-2"></i>
                        Job Payload
                    </h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="copyPayload()">
                        <i class="fas fa-copy me-1"></i>Copy Payload
                    </button>
                </div>
            </div>
            <div class="card-body">
                <pre id="jobPayload" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ json_encode($jobDetails['data'], JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>

    <!-- Related Jobs -->
    @if($relatedJobs->count() > 0)
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-link text-info me-2"></i>
                    Related Jobs
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Job ID</th>
                                <th>Type</th>
                                <th>Queue</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatedJobs as $relatedJob)
                                @php
                                    $relatedPayload = json_decode($relatedJob->payload);
                                    $relatedStatus = isset($relatedJob->failed_at) ? 'failed' : (isset($relatedJob->reserved_at) ? 'running' : 'pending');
                                @endphp
                                <tr>
                                    <td><strong>#{{ $relatedJob->id }}</strong></td>
                                    <td>{{ $relatedPayload->displayName ?? 'Unknown' }}</td>
                                    <td><span class="badge bg-info">{{ $relatedJob->queue ?? 'default' }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $relatedStatus === 'failed' ? 'danger' : ($relatedStatus === 'running' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($relatedStatus) }}
                                        </span>
                                    </td>
                                    <td>{{ $relatedJob->created_at ? date('M d, H:i', $relatedJob->created_at) : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.jobs.show', $relatedJob->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

    <!-- Job Timeline -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history text-success me-2"></i>
                    Job Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Job Created</h6>
                            <p class="timeline-description">Job was queued and waiting for processing</p>
                            <small class="text-muted">{{ $jobDetails['created_at'] }}</small>
                        </div>
                    </div>

                    @if($jobDetails['reserved_at'])
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Job Started</h6>
                            <p class="timeline-description">Job processing began</p>
                            <small class="text-muted">{{ $jobDetails['reserved_at'] }}</small>
                        </div>
                    </div>
                    @endif

                    @if($jobDetails['status'] === 'failed' && $jobDetails['failed_at'])
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Job Failed</h6>
                            <p class="timeline-description">Job encountered an error and failed</p>
                            <small class="text-muted">{{ $jobDetails['failed_at'] }}</small>
                        </div>
                    </div>
                    @elseif($jobDetails['status'] === 'running')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Job Running</h6>
                            <p class="timeline-description">Job is currently being processed</p>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Job Logs Modal -->
<div class="modal fade" id="jobLogsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Job Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="jobLogsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Loading logs...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadLogs()">
                    <i class="fas fa-download me-1"></i>Download Logs
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }

    .timeline:before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 40px;
        width: 2px;
        margin-left: -1.5px;
        content: '';
        background-color: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 50px;
        min-height: 50px;
    }

    .timeline-marker {
        position: absolute;
        top: 0;
        left: 40px;
        width: 15px;
        height: 15px;
        margin-left: -7.5px;
        border: 2px solid #fff;
        border-radius: 50%;
        background-color: #007bff;
    }

    .timeline-content {
        position: relative;
        margin-left: 70px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #007bff;
    }

    .timeline-title {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 16px;
        font-weight: 600;
    }

    .timeline-description {
        margin-bottom: 10px;
        color: #6c757d;
    }

    .job-status-indicator {
        animation: pulse 2s infinite;
    }

    .job-status-indicator .fa-spinner {
        animation: spin 1s linear infinite, pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    pre {
        font-size: 0.85em;
        line-height: 1.4;
    }

    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 10 seconds if job is running
    @if($jobDetails['status'] === 'running')
        setInterval(function() {
            refreshJobDetails();
        }, 10000);
    @endif
});

function refreshJobDetails() {
    const refreshBtn = $('button:contains("Refresh")');
    const originalText = refreshBtn.html();
    refreshBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...').prop('disabled', true);
    
    setTimeout(() => location.reload(), 1000);
}

function retryJob(jobId) {
    if (confirm('Are you sure you want to retry this job?')) {
        const btn = $('button:contains("Retry Job")');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Retrying...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.jobs.retry", ":id") }}'.replace(':id', jobId),
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Job retry initiated successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', response.message || 'Failed to retry job');
                    btn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to retry job';
                showAlert('error', message);
                btn.html(originalText).prop('disabled', false);
            }
        });
    }
}

function viewJobLogs(jobId) {
    $('#jobLogsModal').modal('show');
    
    // Simulate log loading (would need actual endpoint)
    setTimeout(() => {
        $('#jobLogsContent').html(`
            <pre class="bg-dark text-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">
[${new Date().toISOString()}] INFO: Job #${jobId} processing started
[${new Date().toISOString()}] INFO: Initializing job parameters
[${new Date().toISOString()}] INFO: Validating input data
[${new Date().toISOString()}] INFO: Processing job logic
${jobId % 2 === 0 ? '[' + new Date().toISOString() + '] ERROR: Sample error occurred during processing' : '[' + new Date().toISOString() + '] INFO: Job completed successfully'}

Note: This is sample log data. Actual log viewing feature requires backend implementation.
            </pre>
        `);
    }, 1000);
}

function exportJobDetails() {
    const jobData = {
        job_id: {{ $jobDetails['id'] }},
        job_class: '{{ $jobDetails['job_class'] }}',
        status: '{{ $jobDetails['status'] }}',
        queue: '{{ $jobDetails['queue'] }}',
        attempts: {{ $jobDetails['attempts'] }},
        created_at: '{{ $jobDetails['created_at'] }}',
        reserved_at: '{{ $jobDetails['reserved_at'] ?? 'null' }}',
        failed_at: '{{ $jobDetails['failed_at'] ?? 'null' }}',
        payload: {!! json_encode($jobDetails['data']) !!}
    };
    
    const dataStr = JSON.stringify(jobData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `job-${jobData.job_id}-details.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showAlert('success', 'Job details exported successfully!');
}

function copyPayload() {
    const payload = $('#jobPayload').text();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(payload).then(() => {
            showAlert('success', 'Job payload copied to clipboard!');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = payload;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showAlert('success', 'Job payload copied to clipboard!');
        } catch (err) {
            showAlert('error', 'Failed to copy to clipboard');
        }
        document.body.removeChild(textArea);
    }
}

function downloadLogs() {
    showAlert('info', 'Log download feature will be implemented with proper log file access.');
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