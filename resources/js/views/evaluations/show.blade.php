@extends('layouts.main')

@section('title', 'Evaluation Details - SAW Employee Evaluation')
@section('page-title', 'Evaluation Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Evaluation Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Evaluation Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Employee:</label>
                            <div class="fs-5">
                                <i class="fas fa-user me-2 text-primary"></i>
                                {{ $evaluation->employee->name }}
                            </div>
                            <small class="text-muted">{{ $evaluation->employee->employee_code }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Criteria:</label>
                            <div class="fs-5">
                                <i class="fas fa-list-check me-2 text-info"></i>
                                {{ $evaluation->criteria->name }}
                            </div>
                            <small class="text-muted">
                                Weight: {{ $evaluation->criteria->weight }}% |
                                Type:
                                @if($evaluation->criteria->type === 'benefit')
                                    <span class="badge bg-success">Benefit</span>
                                @else
                                    <span class="badge bg-warning">Cost</span>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Score:</label>
                            <div class="fs-1 fw-bold">
                                @if($evaluation->score >= 80)
                                    <span class="text-success">{{ $evaluation->score }}</span>
                                @elseif($evaluation->score >= 60)
                                    <span class="text-warning">{{ $evaluation->score }}</span>
                                @else
                                    <span class="text-danger">{{ $evaluation->score }}</span>
                                @endif
                                <small class="text-muted">/100</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Period:</label>
                            <div class="fs-5">
                                <i class="fas fa-calendar me-2 text-secondary"></i>
                                {{ $evaluation->evaluation_period }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date:</label>
                            <div class="fs-5">
                                <i class="fas fa-clock me-2 text-secondary"></i>
                                {{ $evaluation->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Visualization -->
                <div class="mt-4">
                    <label class="form-label fw-bold">Score Visualization:</label>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar
                            @if($evaluation->score >= 80) bg-success
                            @elseif($evaluation->score >= 60) bg-warning
                            @else bg-danger
                            @endif"
                             style="width: {{ $evaluation->score }}%"
                             role="progressbar">
                            <span class="fw-bold">{{ $evaluation->score }}%</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>0</small>
                        <small>25</small>
                        <small>50</small>
                        <small>75</small>
                        <small>100</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user text-primary me-2"></i>
                    Employee Details
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Department:</label>
                            <div>{{ $evaluation->employee->department ?? 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Position:</label>
                            <div>{{ $evaluation->employee->position ?? 'Not specified' }}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email:</label>
                    <div>{{ $evaluation->employee->email ?? 'Not specified' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools text-primary me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('evaluations.edit', $evaluation->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Edit Evaluation
                    </a>
                    <a href="{{ route('evaluations.create') }}?employee_id={{ $evaluation->employee_id }}&criteria_id={{ $evaluation->criteria_id }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Add Similar Evaluation
                    </a>
                    <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Score Analysis Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Score Analysis
                </h6>
            </div>
            <div class="card-body">
                @if($evaluation->score >= 90)
                    <div class="text-center">
                        <i class="fas fa-star fa-3x text-warning mb-3"></i>
                        <h5 class="text-success">Excellent Performance</h5>
                        <p class="text-muted">Outstanding achievement in this criteria</p>
                    </div>
                @elseif($evaluation->score >= 80)
                    <div class="text-center">
                        <i class="fas fa-thumbs-up fa-3x text-success mb-3"></i>
                        <h5 class="text-primary">Good Performance</h5>
                        <p class="text-muted">Above average performance</p>
                    </div>
                @elseif($evaluation->score >= 70)
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                        <h5 class="text-warning">Average Performance</h5>
                        <p class="text-muted">Meets basic requirements</p>
                    </div>
                @elseif($evaluation->score >= 60)
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-warning">Below Average</h5>
                        <p class="text-muted">Needs improvement</p>
                    </div>
                @else
                    <div class="text-center">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Poor Performance</h5>
                        <p class="text-muted">Significant improvement needed</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Related Evaluations -->
@if($relatedEvaluations->count() > 1)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Other Evaluations for {{ $evaluation->employee->name }} ({{ $evaluation->evaluation_period }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Criteria</th>
                                <th>Weight</th>
                                <th>Score</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatedEvaluations as $relatedEvaluation)
                                @if($relatedEvaluation->id !== $evaluation->id)
                                <tr>
                                    <td>
                                        <strong>{{ $relatedEvaluation->criteria->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $relatedEvaluation->criteria->weight }}%</span>
                                    </td>
                                    <td>
                                        @if($relatedEvaluation->score >= 80)
                                            <span class="badge bg-success">{{ $relatedEvaluation->score }}</span>
                                        @elseif($relatedEvaluation->score >= 60)
                                            <span class="badge bg-warning">{{ $relatedEvaluation->score }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $relatedEvaluation->score }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($relatedEvaluation->criteria->type === 'benefit')
                                            <span class="badge bg-success">Benefit</span>
                                        @else
                                            <span class="badge bg-warning">Cost</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('evaluations.show', $relatedEvaluation->id) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('evaluations.edit', $relatedEvaluation->id) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- No Related Evaluations Message -->
@if($relatedEvaluations->count() <= 1)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Other Evaluations</h5>
                <p class="text-muted mb-3">
                    This is the only evaluation for {{ $evaluation->employee->name }} in period {{ $evaluation->evaluation_period }}.
                </p>
                <a href="{{ route('evaluations.create') }}?employee_id={{ $evaluation->employee_id }}&evaluation_period={{ $evaluation->evaluation_period }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add More Evaluations
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: none;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    border-radius: 0.375rem;
    font-weight: bold;
}

.badge {
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.fa-3x {
    font-size: 3em;
}
</style>
@endpush
