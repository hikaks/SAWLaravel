@extends('layouts.main')

@section('title', __('Performance Details') . ' - ' . $employee->name . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Performance Details'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Performance Analysis') }}</h1>
        <p class="text-muted mb-0">{{ __('Comprehensive performance breakdown for') }} {{ $employee->name }} - {{ $period }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info" onclick="exportPerformanceReport()">
            <i class="fas fa-download me-1"></i>
            {{ __('Export Report') }}
        </button>
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-primary">
            <i class="fas fa-user me-1"></i>
            {{ __('Employee Profile') }}
        </a>
        <a href="{{ route('results.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            {{ __('Back to Results') }}
        </a>
    </div>
</div>

<!-- Employee & Result Summary -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
            <div class="card-body text-white">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                             style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 700; color: white;">
                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="fw-bold mb-2">{{ $employee->name }}</h2>
                        <p class="fs-5 mb-3 opacity-75">{{ $employee->position }} • {{ $employee->department }}</p>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="opacity-75">{{ __('Employee Code') }}</small>
                                <div class="fw-semibold">{{ $employee->employee_code }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="opacity-75">{{ __('Evaluation Period') }}</small>
                                <div class="fw-semibold">{{ $period }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="opacity-75">{{ __('Total Criterias') }}</small>
                                <div class="fw-semibold">{{ $criterias->count() }} {{ __('criterias') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="position-relative d-inline-block">
                        <svg width="120" height="120" class="circular-progress">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="10"/>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#0d6efd" stroke-width="10"
                                    stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $result->total_score) }}"
                                    stroke-linecap="round"
                                    transform="rotate(-90 60 60)"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="h2 fw-bold text-primary mb-0">{{ number_format($result->total_score * 100, 1) }}%</div>
                            <small class="text-muted">{{ __('Final Score') }}</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="badge bg-primary fs-5 px-4 py-2">{{ __('Rank') }} #{{ $result->ranking }}</span>
                </div>
                <div class="text-muted">
                    @if($result->ranking <= 3)
                        <i class="fas fa-star text-warning me-1"></i>{{ __('Excellent Performance') }}
                    @elseif($result->ranking <= 10)
                        <i class="fas fa-thumbs-up text-success me-1"></i>{{ __('Good Performance') }}
                    @else
                        <i class="fas fa-chart-line text-info me-1"></i>{{ __('Average Performance') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Criteria Analysis -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 fw-semibold">
            <i class="fas fa-chart-bar me-2 text-primary"></i>
            {{ __('Criteria Performance Analysis') }}
        </h5>
    </div>
    <div class="card-body">
        @if(count($normalizedScores) > 0)
            <div class="row g-3">
                @foreach($normalizedScores as $score)
                <div class="col-lg-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">{{ $score['criteria']->name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-{{ $score['criteria']->type == 'benefit' ? 'success' : 'warning' }}">
                                            {{ ucfirst($score['criteria']->type) }}
                                        </span>
                                        <small class="text-muted">{{ __('Weight') }}: {{ $score['criteria']->weight }}%</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="h5 fw-bold mb-0 {{ $score['raw_score'] >= 80 ? 'text-success' : ($score['raw_score'] >= 60 ? 'text-warning' : 'text-danger') }}">
                                        {{ $score['raw_score'] }}
                                    </div>
                                    <small class="text-muted">{{ __('Raw Score') }}</small>
                                </div>
                            </div>

                            <!-- Score Breakdown -->
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="h6 fw-bold mb-0 text-primary">{{ number_format($score['normalized_score'], 3) }}</div>
                                        <small class="text-muted">{{ __('Normalized') }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="h6 fw-bold mb-0 text-success">{{ number_format($score['weighted_score'], 3) }}</div>
                                        <small class="text-muted">{{ __('Weighted') }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-white rounded">
                                        <div class="h6 fw-bold mb-0 text-info">{{ number_format($score['contribution_percentage'], 1) }}%</div>
                                        <small class="text-muted">{{ __('Contribution') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Visualization -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">{{ __('Raw Score Progress') }}</small>
                                    <small class="text-muted">{{ $score['raw_score'] }}/100</small>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar {{ $score['raw_score'] >= 80 ? 'bg-success' : ($score['raw_score'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width: {{ $score['raw_score'] }}%"></div>
                                </div>
                            </div>

                            <!-- Contribution to Final Score -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">{{ __('Impact on Final Score') }}</small>
                                    <small class="text-muted">{{ number_format($score['contribution_percentage'], 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $score['contribution_percentage'] }}%"></div>
                                </div>
                            </div>

                            <!-- Performance Indicator -->
                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="small text-muted">{{ __('Performance Level') }}</span>
                                    @if($score['raw_score'] >= 90)
                                        <span class="badge bg-success">{{ __('Outstanding') }}</span>
                                    @elseif($score['raw_score'] >= 80)
                                        <span class="badge bg-primary">{{ __('Excellent') }}</span>
                                    @elseif($score['raw_score'] >= 70)
                                        <span class="badge bg-warning">{{ __('Good') }}</span>
                                    @elseif($score['raw_score'] >= 60)
                                        <span class="badge bg-info">{{ __('Satisfactory') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Needs Improvement') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="text-muted mb-3">{{ __('No Evaluation Data') }}</h5>
                <p class="text-muted mb-4">{{ __('No detailed evaluation data available for this period.') }}</p>
                <a href="{{ route('evaluations.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Create Evaluation') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Performance Insights & Recommendations -->
@if(count($normalizedScores) > 0)
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-lightbulb me-2 text-warning"></i>
                    {{ __('Performance Insights') }}
                </h6>
            </div>
            <div class="card-body">
                @php
                    $strongAreas = collect($normalizedScores)->where('raw_score', '>=', 80);
                    $improvementAreas = collect($normalizedScores)->where('raw_score', '<', 70);
                    $avgScore = collect($normalizedScores)->avg('raw_score');
                @endphp

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-star me-2"></i>{{ __('Strengths') }}
                            </h6>
                            @if($strongAreas->count() > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach($strongAreas as $area)
                                    <li>{{ $area['criteria']->name }} ({{ $area['raw_score'] }})</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mb-0 small">{{ __('Focus on building stronger performance areas.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-chart-line me-2"></i>{{ __('Growth Opportunities') }}
                            </h6>
                            @if($improvementAreas->count() > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach($improvementAreas as $area)
                                    <li>{{ $area['criteria']->name }} ({{ $area['raw_score'] }})</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mb-0 small">{{ __('Excellent! All areas performing well.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-pie me-3"></i>
                            <div>
                                <strong>{{ __('Overall Assessment') }}:</strong><br>
                                {{ __('Average performance score is') }} <strong>{{ number_format($avgScore, 1) }}</strong>.
                                @if($avgScore >= 85)
                                    {{ __('Outstanding performance across all criteria.') }}
                                @elseif($avgScore >= 75)
                                    {{ __('Good performance with room for growth in some areas.') }}
                                @elseif($avgScore >= 65)
                                    {{ __('Satisfactory performance. Focus on improving weaker areas.') }}
                                @else
                                    {{ __('Performance needs significant improvement across multiple criteria.') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-tasks me-2 text-primary"></i>
                    {{ __('Quick Actions') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('evaluations.create', ['employee_id' => $employee->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('New Evaluation') }}
                    </a>
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('Edit Employee') }}
                    </a>
                    <button class="btn btn-outline-info" onclick="printPerformanceReport()">
                        <i class="fas fa-print me-2"></i>
                        {{ __('Print Report') }}
                    </button>
                    <button class="btn btn-outline-secondary" onclick="sharePerformanceReport()">
                        <i class="fas fa-share me-2"></i>
                        {{ __('Share Results') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Performance Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-history me-2 text-secondary"></i>
                    {{ __('Recent Activity') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">{{ __('Evaluation Completed') }}</h6>
                            <p class="mb-0 small text-muted">{{ __('Period') }}: {{ $period }}</p>
                            <small class="text-muted">{{ $result->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">{{ __('SAW Calculation') }}</h6>
                            <p class="mb-0 small text-muted">{{ __('Final Score') }}: {{ number_format($result->total_score * 100, 1) }}%</p>
                            <small class="text-muted">{{ $result->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function exportPerformanceReport() {
    Swal.fire({
        title: '{{ __("Export Performance Report") }}',
        text: '{{ __("Choose export format") }}',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf me-1"></i>{{ __("PDF") }}',
        denyButtonText: '<i class="fas fa-file-excel me-1"></i>{{ __("Excel") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: '#dc3545',
        denyButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export as PDF
            const url = `{{ route('results.export-employee', $employee->id) }}?period={{ $period }}&format=pdf`;
            downloadFile(url, 'pdf');
        } else if (result.isDenied) {
            // Export as Excel
            const url = `{{ route('results.export-employee', $employee->id) }}?period={{ $period }}&format=excel`;
            downloadFile(url, 'excel');
        }
    });
}

function downloadFile(url, format) {
    // Show loading indicator
    const loadingMsg = format === 'pdf' ? '{{ __("Generating PDF...") }}' : '{{ __("Generating Excel...") }}';
    const loadingToast = Swal.fire({
        title: loadingMsg,
        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create temporary link for download
    const link = document.createElement('a');
    link.href = url;
    link.download = `performance-report-{{ $employee->employee_code }}-{{ $period }}.${format === 'pdf' ? 'pdf' : 'xls'}`;

    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Close loading and show success
    setTimeout(() => {
        loadingToast.close();
        Swal.fire({
            icon: 'success',
            title: '{{ __("Export Successful") }}',
            text: `{{ __("Performance report exported as") }} ${format.toUpperCase()}`,
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#198754',
            timer: 3000,
            timerProgressBar: true
        });
    }, 2000);
}

function printPerformanceReport() {
    window.print();
}

function sharePerformanceReport() {
    if (navigator.share) {
        navigator.share({
            title: '{{ __("Performance Report") }} - {{ $employee->name }}',
            text: '{{ __("Performance analysis for") }} {{ $employee->name }} - {{ $period }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showSuccess('{{ __("Link copied to clipboard") }}');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
/* Circular Progress Animation */
.circular-progress circle:nth-child(2) {
    transition: stroke-dashoffset 0.6s ease-in-out;
}

/* Timeline Styling */
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 6px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 18px;
    width: 2px;
    height: calc(100% + 8px);
    background-color: #dee2e6;
}

.timeline-content h6 {
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.timeline-content p {
    font-size: 0.8rem;
    margin-bottom: 2px;
}

.timeline-content small {
    font-size: 0.75rem;
}

/* Print Styles */
@media print {
    .btn, .card-header .btn, .d-flex.gap-2 {
        display: none !important;
    }

    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }

    .page-break {
        page-break-before: always;
    }
}

/* Performance Level Badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Alert Customizations */
.alert ul {
    margin-bottom: 0;
}

.alert li {
    margin-bottom: 0.25rem;
}

/* Progress Bar Enhancements */
.progress {
    border-radius: 0.375rem;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Card Hover Effects */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
}
</style>
@endpush
