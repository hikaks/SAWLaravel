@extends('layouts.main')

@section('title', __('Dashboard') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Dashboard Overview'))

@section('content')
<!-- Modern Statistics Grid -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $stats['total_employees'] }}</div>
                <div class="stats-label">{{ __('Total Employees') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-user-group"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $stats['total_criterias'] }}</div>
                <div class="stats-label">{{ __('Evaluation Criteria') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-sliders"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $stats['total_evaluations'] }}</div>
                <div class="stats-label">{{ __('Completed Evaluations') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stats-content">
                <div class="stats-number">{{ $stats['total_weight'] ?? 100 }}%</div>
                <div class="stats-label">{{ __('System Ready') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid - Improved Layout -->
<div class="row g-4">
    <!-- Top 10 Performers Card - Simplified -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-trophy me-2" style="color: #f59e0b;"></i>
                        {{ __('Top 10 Performers') }}
                    </h6>
                    <a href="{{ route('results.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View All Results') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($topPerformers->count() > 0)
                    <div class="row g-3">
                        @foreach($topPerformers->take(10) as $index => $performer)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-{{ $index < 3 ? 'success' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; font-size: 14px; font-weight: 600;">
                                        #{{ $index + 1 }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $performer->employee->name }}</div>
                                    <div class="text-muted small">{{ $performer->employee->department }}</div>
                                    <div class="d-flex align-items-center mt-1">
                                        <div class="progress flex-grow-1 me-2" style="height: 4px;">
                                            <div class="progress-bar bg-{{ $index < 3 ? 'success' : 'primary' }}"
                                                 style="width: {{ round($performer->total_score * 100, 2) }}%"></div>
                                        </div>
                                        <small class="fw-semibold">{{ round($performer->total_score * 100, 2) }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-chart-line text-muted" style="font-size: 48px;"></i>
                        </div>
                        <h6 class="text-muted">{{ __('No Evaluation Results Yet') }}</h6>
                        <p class="text-muted mb-4">{{ __('Complete employee evaluations to see performance rankings here.') }}</p>
                        <a href="{{ route('evaluations.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Start Evaluating') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar - Single Card -->
    <div class="col-lg-4">
        <!-- Recent Activity Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-clock me-2" style="color: #06b6d4;"></i>
                    {{ __('Recent Activity') }}
                </h6>
            </div>
            <div class="card-body">
                @if($latestEvaluations->count() > 0)
                    @foreach($latestEvaluations as $evaluation)
                    <div class="d-flex align-items-start mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 36px; height: 36px;">
                                <i class="fas fa-clipboard-check text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $evaluation->employee->name }}</div>
                            <div class="text-muted small">{{ $evaluation->criteria->name }}</div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span class="badge bg-{{ $evaluation->score >= 80 ? 'success' : ($evaluation->score >= 60 ? 'warning' : 'danger') }}">
                                    {{ $evaluation->score }}
                                </span>
                                <small class="text-muted">{{ $evaluation->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times text-muted mb-2" style="font-size: 32px;"></i>
                        <p class="text-muted mb-0">{{ __('No recent activity') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section - Four Equal Columns for Better Balance -->
<div class="row g-4 mt-3">
    <!-- Department Comparison -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-chart-bar me-2" style="color: #10b981;"></i>
                    {{ __('Department Comparison') }}
                </h6>
            </div>
            <div class="card-body">
                @if($departmentStats->count() > 0)
                    @foreach($departmentStats->take(5) as $dept)
                    @php
                        $percentage = $stats['total_employees'] > 0 ? round(($dept->count / $stats['total_employees']) * 100, 1) : 0;
                        $color = $percentage >= 30 ? 'success' : ($percentage >= 20 ? 'warning' : 'info');
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">{{ $dept->department }}</span>
                            <span class="badge bg-{{ $color }}">{{ $dept->count }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $color }}" 
                                 style="width: {{ $percentage }}%"
                                 title="{{ $percentage }}% of total employees"></div>
                        </div>
                        <small class="text-muted">{{ $percentage }}% {{ __('of total') }}</small>
                    </div>
                    @endforeach
                    @if($departmentStats->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">+{{ $departmentStats->count() - 5 }} {{ __('more departments') }}</small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-building text-muted mb-2" style="font-size: 24px;"></i>
                        <p class="text-muted mb-0 small">{{ __('No department data') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Criteria Distribution -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-sliders me-2" style="color: #f59e0b;"></i>
                    {{ __('Criteria') }}
                </h6>
            </div>
            <div class="card-body">
                @if($criteriaStats->count() > 0)
                    @foreach($criteriaStats as $criteria)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center me-2"
                                 style="width: 32px; height: 32px;">
                                <i class="fas fa-sliders text-warning" style="font-size: 14px;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ ucfirst($criteria->type) }}</div>
                                <small class="text-muted">{{ $criteria->count }} {{ __('criteria') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-sliders text-muted mb-2" style="font-size: 24px;"></i>
                        <p class="text-muted mb-0 small">{{ __('No criteria data') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-chart-line me-2" style="color: #06b6d4;"></i>
                    {{ __('System Status') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-server text-primary" style="font-size: 32px;"></i>
                    </div>
                    <div class="fw-bold text-primary mb-1">{{ $stats['total_weight'] ?? 100 }}%</div>
                    <small class="text-muted">{{ __('System Ready') }}</small>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ __('Employees') }}</span>
                        <span class="fw-semibold">{{ $stats['total_employees'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ __('Criteria') }}</span>
                        <span class="fw-semibold">{{ $stats['total_criterias'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>{{ __('Evaluations') }}</span>
                        <span class="fw-semibold">{{ $stats['total_evaluations'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-tachometer-alt me-2" style="color: #8b5cf6;"></i>
                    {{ __('Quick Stats') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fw-bold text-success">{{ $topPerformers->count() }}</div>
                            <small class="text-muted">{{ __('Top Performers') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fw-bold text-primary">{{ $recentPeriods->count() }}</div>
                            <small class="text-muted">{{ __('Periods') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fw-bold text-warning">{{ $latestEvaluations->count() }}</div>
                            <small class="text-muted">{{ __('Recent') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fw-bold text-info">{{ $departmentStats->count() }}</div>
                            <small class="text-muted">{{ __('Depts') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
