@extends('layouts.main')

@section('title', __('Employee Details') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Employee Details'))

@section('content')
@php
    $latestResult = $employee->latestResult();
@endphp

<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Employee Profile') }}</h1>
        <p class="text-muted mb-0">{{ __('Complete information and performance overview') }}</p>
    </div>
    <div class="flex gap-2">
        <x-ui.button 
            href="{{ route('employees.edit', $employee->id) }}" 
            variant="warning" 
            icon="fas fa-edit">
            {{ __('Edit Employee') }}
        </x-ui.button>
        <x-ui.button 
            href="{{ route('employees.index') }}" 
            variant="outline-secondary" 
            icon="fas fa-arrow-left">
            {{ __('Back to List') }}
        </x-ui.button>
    </div>
</div>

<!-- Employee Profile Card -->
<div class="card mb-4" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
    <div class="card-body text-white py-5">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="position-relative">
                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                         style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 700; color: white;">
                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                    </div>
                    <span class="position-absolute bottom-0 end-0 translate-middle p-2 bg-success border border-white rounded-circle">
                        <span class="visually-hidden">{{ __('Active') }}</span>
                    </span>
                </div>
            </div>
            <div class="col">
                <h1 class="display-6 fw-bold mb-2">{{ $employee->name }}</h1>
                <p class="fs-5 mb-2 opacity-75">{{ $employee->position }}</p>
                <p class="mb-3 opacity-75">{{ $employee->department }}</p>
                <span class="badge bg-white text-primary fs-6 px-3 py-2 rounded-pill">{{ $employee->employee_code }}</span>
            </div>
            <div class="col-auto">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('evaluations.create', ['employee_id' => $employee->id]) }}" class="btn btn-success">
                        <i class="fas fa-clipboard-check me-2"></i>{{ __('Evaluate') }}
                    </a>
                    @if($latestResult)
                    <a href="{{ route('results.details', ['employee' => $employee->id, 'period' => $latestResult->evaluation_period]) }}" class="btn btn-light">
                        <i class="fas fa-chart-line me-2"></i>{{ __('Performance') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $employee->evaluations->groupBy('evaluation_period')->count() }}</div>
                <div class="stats-label">{{ __('Evaluation Periods') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-calendar"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $employee->evaluations->count() }}</div>
                <div class="stats-label">{{ __('Total Evaluations') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stats-content">
                <div class="stats-number">
                    {{ $latestResult ? '#'.$latestResult->ranking : '-' }}
                </div>
                <div class="stats-label">{{ __('Current Ranking') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $latestResult ? number_format($latestResult->total_score * 100, 1) . '%' : '-' }}</div>
                <div class="stats-label">{{ __('Latest Score') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Information Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-address-card me-2 text-primary"></i>
                    {{ __('Contact Information') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">{{ __('Email Address') }}</small>
                            <div class="fw-semibold text-break">
                                <a href="mailto:{{ $employee->email }}" class="text-decoration-none">{{ $employee->email }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">{{ __('Department') }}</small>
                            <div class="fw-semibold">{{ $employee->department }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">{{ __('Joined Date') }}</small>
                            <div class="fw-semibold">{{ $employee->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">{{ __('Last Updated') }}</small>
                            <div class="fw-semibold">{{ $employee->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        @if($latestResult)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-success"></i>
                    {{ __('Latest Performance') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-primary text-white rounded">
                            <div class="h4 fw-bold mb-1">{{ number_format($latestResult->total_score * 100, 1) }}%</div>
                            <small class="fw-medium">{{ __('Score') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <div class="h4 fw-bold mb-1">#{{ $latestResult->ranking }}</div>
                            <small class="fw-medium">{{ __('Rank') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-info text-white rounded">
                            <div class="h6 fw-bold mb-1">
                                @if($latestResult->ranking <= 3)
                                    {{ __('Excellent') }}
                                @elseif($latestResult->ranking <= 10)
                                    {{ __('Good') }}
                                @else
                                    {{ __('Average') }}
                                @endif
                            </div>
                            <small class="fw-medium">{{ __('Category') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-warning text-white rounded">
                            <div class="h6 fw-bold mb-1">{{ $latestResult->evaluation_period }}</div>
                            <small class="fw-medium">{{ __('Period') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No Performance Data') }}</h5>
                <p class="text-muted">{{ __('No evaluation results available yet') }}</p>
                <a href="{{ route('evaluations.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('Start Evaluation') }}
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Evaluation History -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-history me-2 text-primary"></i>
                {{ __('Evaluation History') }}
            </h5>
            @if($evaluationsByPeriod->count() > 1)
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-2"></i>
                    {{ __('Filter Period') }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterEvaluations('')">{{ __('All Periods') }}</a></li>
                    @foreach($evaluationsByPeriod->keys() as $period)
                    <li><a class="dropdown-item" href="#" onclick="filterEvaluations('{{ $period }}')">{{ $period }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($evaluationsByPeriod->count() > 0)
            @foreach($evaluationsByPeriod as $period => $evaluations)
            <div class="evaluation-period mb-4" data-period="{{ $period }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-primary fw-bold mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        {{ __('Period') }}: {{ $period }}
                    </h6>
                    <span class="badge bg-primary px-3 py-2">
                        {{ $evaluations->count() }} {{ __('criteria') }}
                    </span>
                </div>

                <div class="row g-3">
                    @foreach($evaluations as $evaluation)
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">{{ $evaluation->criteria->name }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge fs-6 px-3 py-2 {{ $evaluation->score >= 80 ? 'bg-success' : ($evaluation->score >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $evaluation->score }}
                                            </span>
                                            <span class="text-muted">/ 100</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted mb-1">{{ __('Weight') }}: {{ $evaluation->criteria->weight }}%</div>
                                        <span class="badge bg-secondary">{{ ucfirst($evaluation->criteria->type) }}</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $evaluation->score >= 80 ? 'bg-success' : ($evaluation->score >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width: {{ $evaluation->score }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(!$loop->last)
                <hr class="my-4">
                @endif
            </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                <h5 class="text-muted mb-3">{{ __('No Evaluations Yet') }}</h5>
                <p class="text-muted mb-4">{{ __('This employee has no evaluation data yet.') }}</p>
                <a href="{{ route('evaluations.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Create First Evaluation') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Performance Chart -->
@if($employee->evaluationResults->count() > 1)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0 fw-semibold">
            <i class="fas fa-chart-line me-2 text-success"></i>
            {{ __('Performance Trend') }}
        </h5>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 400px;">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    @if($employee->evaluationResults->count() > 1)
    // Performance Trend Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const results = @json($employee->evaluationResults->sortBy('evaluation_period')->values());

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: results.map(r => r.evaluation_period),
            datasets: [{
                label: '{{ __("Total Score (%)") }}',
                data: results.map(r => Math.round(r.total_score * 100, 2)),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 8
            }, {
                label: '{{ __("Ranking") }}',
                data: results.map(r => r.ranking),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y1',
                pointBackgroundColor: '#dc3545',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 14,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '{{ __("Score (%)") }}',
                        font: {
                            size: 14,
                            weight: '600'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '{{ __("Ranking") }}',
                        font: {
                            size: 14,
                            weight: '600'
                        }
                    },
                    reverse: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
    @endif
});

function filterEvaluations(period) {
    if (period === '') {
        $('.evaluation-period').show();
    } else {
        $('.evaluation-period').hide();
        $(`.evaluation-period[data-period="${period}"]`).show();
    }
}

function exportEmployeeData(employeeId) {
    // Show export options
    Swal.fire({
        title: '{{ __("Export Employee Data") }}',
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
            window.location.href = `/employees/${employeeId}/export?format=pdf`;
        } else if (result.isDenied) {
            // Export as Excel
            window.location.href = `/employees/${employeeId}/export?format=excel`;
        }
    });
}
</script>
@endpush
