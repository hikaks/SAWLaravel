@extends('layouts.main')

@section('title', 'Criteria Details - SAW Employee Evaluation')
@section('page-title', 'Criteria Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Criteria Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Criteria Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Criteria Name:</label>
                            <div class="fs-5">{{ $criteria->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Weight:</label>
                            <div class="fs-5">
                                <span class="badge bg-primary fs-6">{{ $criteria->weight }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type:</label>
                            <div class="fs-5">
                                @if($criteria->type === 'benefit')
                                    <span class="badge bg-success fs-6">Benefit</span>
                                @else
                                    <span class="badge bg-warning fs-6">Cost</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created:</label>
                            <div>{{ $criteria->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated:</label>
                            <div>{{ $criteria->updated_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description:</label>
                    <div class="text-muted">
                        @if($criteria->type === 'benefit')
                            <i class="fas fa-arrow-up text-success me-2"></i>
                            Higher values are better for this criteria
                        @else
                            <i class="fas fa-arrow-down text-warning me-2"></i>
                            Lower values are better for this criteria
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Evaluation Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-1">{{ $evaluationStats['total_evaluations'] }}</h3>
                            <small class="text-muted">Total Evaluations</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-1">{{ number_format($evaluationStats['avg_score'], 1) }}</h3>
                            <small class="text-muted">Average Score</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning mb-1">{{ $evaluationStats['min_score'] ?? 'N/A' }}</h3>
                            <small class="text-muted">Minimum Score</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-info mb-1">{{ $evaluationStats['max_score'] ?? 'N/A' }}</h3>
                            <small class="text-muted">Maximum Score</small>
                        </div>
                    </div>
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
                    <a href="{{ route('criterias.edit', $criteria->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Edit Criteria
                    </a>
                    <a href="{{ route('evaluations.create') }}?criteria_id={{ $criteria->id }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Add Evaluation
                    </a>
                    <a href="{{ route('criterias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Weight Impact Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-balance-scale text-primary me-2"></i>
                    Weight Impact
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="fs-1 fw-bold text-primary">{{ $criteria->weight }}%</div>
                    <small class="text-muted">of total criteria weight</small>
                </div>

                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-primary" style="width: {{ $criteria->weight }}%"></div>
                </div>

                <div class="small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    This criteria has a {{ $criteria->weight }}% impact on final SAW calculations.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evaluations by Period -->
@if($evaluationsByPeriod->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Evaluations by Period
                </h5>
            </div>
            <div class="card-body">
                @foreach($evaluationsByPeriod as $period => $evaluations)
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-calendar me-2"></i>
                        Period: {{ $period }}
                        <span class="badge bg-secondary ms-2">{{ $evaluations->count() }} evaluations</span>
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evaluations->take(5) as $evaluation)
                                <tr>
                                    <td>
                                        <strong>{{ $evaluation->employee->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $evaluation->employee->employee_code }}</small>
                                    </td>
                                    <td>
                                        @if($evaluation->score >= 80)
                                            <span class="badge bg-success">{{ $evaluation->score }}</span>
                                        @elseif($evaluation->score >= 60)
                                            <span class="badge bg-warning">{{ $evaluation->score }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $evaluation->score }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $evaluation->created_at->format('d M Y') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($evaluations->count() > 5)
                    <div class="text-center">
                        <small class="text-muted">
                            Showing first 5 of {{ $evaluations->count() }} evaluations
                        </small>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- No Evaluations Message -->
@if($evaluationsByPeriod->count() === 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Evaluations Yet</h5>
                <p class="text-muted mb-3">
                    This criteria hasn't been used in any evaluations yet.
                </p>
                <a href="{{ route('evaluations.create') }}?criteria_id={{ $criteria->id }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add First Evaluation
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

.border.rounded {
    border-color: #dee2e6 !important;
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    border-radius: 0.375rem;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

.badge {
    font-size: 0.875rem;
}
</style>
@endpush
