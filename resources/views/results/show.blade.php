@extends('layouts.main')

@section('title', __('Result Details') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Result Details'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Evaluation Result Details') }}</h1>
        <p class="text-muted mb-0">{{ __('Detailed SAW calculation results for') }} {{ $result->employee->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.show', $result->employee->id) }}" class="btn btn-outline-info">
            <i class="fas fa-user me-1"></i>
            {{ __('Employee Profile') }}
        </a>
        <a href="{{ route('results.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            {{ __('Back to Results') }}
        </a>
    </div>
</div>

<!-- Employee Summary Card -->
<div class="card mb-4" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
    <div class="card-body text-white">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700; color: white;">
                    {{ strtoupper(substr($result->employee->name, 0, 2)) }}
                </div>
            </div>
            <div class="col">
                <h2 class="fw-bold mb-2">{{ $result->employee->name }}</h2>
                <p class="fs-5 mb-2 opacity-75">{{ $result->employee->position }}</p>
                <div class="row">
                    <div class="col-md-4">
                        <small class="opacity-75">{{ __('Department') }}</small>
                        <div class="fw-semibold">{{ $result->employee->department }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="opacity-75">{{ __('Employee Code') }}</small>
                        <div class="fw-semibold">{{ $result->employee->employee_code }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="opacity-75">{{ __('Evaluation Period') }}</small>
                        <div class="fw-semibold">{{ $result->evaluation_period }}</div>
                    </div>
                </div>
            </div>
            <div class="col-auto text-center">
                <div class="mb-2">
                    <div class="h1 fw-bold mb-0">{{ number_format($result->total_score * 100, 1) }}%</div>
                    <small class="opacity-75">{{ __('Final Score') }}</small>
                </div>
                <div class="badge bg-white text-primary fs-6 px-3 py-2">
                    {{ __('Rank') }} #{{ $result->ranking }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Overview -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                    {{ __('Criteria Evaluation Details') }}
                </h5>
            </div>
            <div class="card-body">
                @if($evaluations->count() > 0)
                    <div class="row g-3">
                        @foreach($evaluations as $evaluation)
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">{{ $evaluation->criteria->name }}</h6>
                                            <small class="text-muted">{{ ucfirst($evaluation->criteria->type) }} Criteria</small>
                                        </div>
                                        <span class="badge bg-secondary">{{ $evaluation->criteria->weight }}%</span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-white rounded">
                                                <div class="h5 fw-bold mb-0 {{ $evaluation->score >= 80 ? 'text-success' : ($evaluation->score >= 60 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $evaluation->score }}
                                                </div>
                                                <small class="text-muted">{{ __('Raw Score') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-white rounded">
                                                <div class="h5 fw-bold mb-0 text-primary">
                                                    {{ number_format($evaluation->getNormalizedScore(
                                                        $evaluations->where('criteria_id', $evaluation->criteria->id)->pluck('score')->toArray()
                                                    ), 3) }}
                                                </div>
                                                <small class="text-muted">{{ __('Normalized') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar {{ $evaluation->score >= 80 ? 'bg-success' : ($evaluation->score >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width: {{ $evaluation->score }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ __('Weight Impact') }}: {{ number_format(($evaluation->criteria->weight / 100) * 100, 1) }}%</small>
                                        <small class="text-muted">{{ $evaluation->score }}/100</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                        <h6 class="text-muted">{{ __('No evaluation data found') }}</h6>
                        <p class="text-muted">{{ __('Please complete the evaluation for this employee first.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-trophy me-2 text-warning"></i>
                    {{ __('Performance Summary') }}
                </h6>
            </div>
            <div class="card-body">
                <!-- Score Distribution -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">{{ __('Overall Performance') }}</span>
                        <span class="badge bg-primary fs-6">{{ number_format($result->total_score * 100, 1) }}%</span>
                    </div>
                    <div class="progress mb-2" style="height: 12px;">
                        <div class="progress-bar bg-gradient"
                             style="width: {{ $result->total_score * 100 }}%; background: linear-gradient(90deg, #dc3545 0%, #ffc107 30%, #28a745 100%);">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">0%</small>
                        <small class="text-muted">100%</small>
                    </div>
                </div>

                <!-- Ranking Info -->
                <div class="mb-4">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="h3 mb-1 text-primary fw-bold">#{{ $result->ranking }}</div>
                        <div class="fw-semibold mb-2">{{ __('Current Ranking') }}</div>
                        <div class="small text-muted">
                            @if($result->ranking <= 3)
                                <span class="badge bg-success">{{ __('Excellent') }}</span>
                            @elseif($result->ranking <= 10)
                                <span class="badge bg-warning">{{ __('Good') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Average') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Performance Category -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">{{ __('Performance Indicators') }}</h6>
                    @php
                        $scorePercentage = $result->total_score * 100;
                    @endphp

                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-{{ $scorePercentage >= 90 ? 'success' : 'muted' }} me-2"></i>
                        <span class="{{ $scorePercentage >= 90 ? 'fw-semibold' : 'text-muted' }}">{{ __('Outstanding') }} (90%+)</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-{{ $scorePercentage >= 80 && $scorePercentage < 90 ? 'primary' : 'muted' }} me-2"></i>
                        <span class="{{ $scorePercentage >= 80 && $scorePercentage < 90 ? 'fw-semibold' : 'text-muted' }}">{{ __('Excellent') }} (80-89%)</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-{{ $scorePercentage >= 70 && $scorePercentage < 80 ? 'warning' : 'muted' }} me-2"></i>
                        <span class="{{ $scorePercentage >= 70 && $scorePercentage < 80 ? 'fw-semibold' : 'text-muted' }}">{{ __('Good') }} (70-79%)</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-circle text-{{ $scorePercentage >= 60 && $scorePercentage < 70 ? 'info' : 'muted' }} me-2"></i>
                        <span class="{{ $scorePercentage >= 60 && $scorePercentage < 70 ? 'fw-semibold' : 'text-muted' }}">{{ __('Satisfactory') }} (60-69%)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-circle text-{{ $scorePercentage < 60 ? 'danger' : 'muted' }} me-2"></i>
                        <span class="{{ $scorePercentage < 60 ? 'fw-semibold' : 'text-muted' }}">{{ __('Needs Improvement') }} (<60%)</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="{{ route('employees.show', $result->employee->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>
                        {{ __('View Employee Profile') }}
                    </a>
                    <a href="{{ route('evaluations.create', ['employee_id' => $result->employee->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('New Evaluation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SAW Calculation Breakdown -->
@if($evaluations->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semibold">
            <i class="fas fa-calculator me-2 text-success"></i>
            {{ __('SAW Calculation Breakdown') }}
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Criteria') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Weight') }}</th>
                        <th>{{ __('Raw Score') }}</th>
                        <th>{{ __('Normalized Score') }}</th>
                        <th>{{ __('Weighted Score') }}</th>
                        <th>{{ __('Contribution') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalWeightedScore = 0;
                    @endphp
                    @foreach($evaluations as $evaluation)
                    @php
                        $normalizedScore = $evaluation->getNormalizedScore(
                            $evaluations->where('criteria_id', $evaluation->criteria->id)->pluck('score')->toArray()
                        );
                        $weightedScore = $normalizedScore * ($evaluation->criteria->weight / 100);
                        $totalWeightedScore += $weightedScore;
                        $contribution = ($weightedScore / $result->total_score) * 100;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $evaluation->criteria->name }}</td>
                        <td>
                            <span class="badge bg-{{ $evaluation->criteria->type == 'benefit' ? 'success' : 'warning' }}">
                                {{ ucfirst($evaluation->criteria->type) }}
                            </span>
                        </td>
                        <td>{{ $evaluation->criteria->weight }}%</td>
                        <td>
                            <span class="badge bg-{{ $evaluation->score >= 80 ? 'success' : ($evaluation->score >= 60 ? 'warning' : 'danger') }}">
                                {{ $evaluation->score }}
                            </span>
                        </td>
                        <td>{{ number_format($normalizedScore, 4) }}</td>
                        <td>{{ number_format($weightedScore, 4) }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $contribution }}%"></div>
                                </div>
                                <small class="fw-semibold">{{ number_format($contribution, 1) }}%</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="5">{{ __('Total Final Score') }}</th>
                        <th>{{ number_format($result->total_score, 4) }}</th>
                        <th>100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-3">
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3"></i>
                    <div>
                        <strong>{{ __('SAW Calculation Formula') }}:</strong><br>
                        <code>{{ __('Final Score') }} = Σ({{ __('Normalized Score') }} × {{ __('Weight') }})</code><br>
                        <small class="text-muted">
                            {{ __('This employee scored') }} <strong>{{ number_format($result->total_score * 100, 1) }}%</strong>
                            {{ __('and ranked') }} <strong>#{{ $result->ranking }}</strong> {{ __('among all employees for period') }} {{ $result->evaluation_period }}.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection


