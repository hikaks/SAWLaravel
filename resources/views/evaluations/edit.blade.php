@extends('layouts.main')

@section('title', 'Edit Evaluation - SAW Employee Evaluation')
@section('page-title', 'Edit Employee Evaluation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Edit Employee Evaluation Form
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('evaluations.update', $evaluation->id) }}" method="POST" id="evaluationForm" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">
                                    Employee <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('employee_id') is-invalid @enderror"
                                        id="employee_id"
                                        name="employee_id"
                                        required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                                {{ old('employee_id', $evaluation->employee_id) == $employee->id ? 'selected' : '' }}
                                                data-department="{{ $employee->department }}"
                                                data-position="{{ $employee->position }}">
                                            {{ $employee->name }} ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="employee_id_error">
                                    @error('employee_id'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="criteria_id" class="form-label">
                                    Criteria <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('criteria_id') is-invalid @enderror"
                                        id="criteria_id"
                                        name="criteria_id"
                                        required>
                                    <option value="">-- Select Criteria --</option>
                                    @foreach($criterias as $criteria)
                                        <option value="{{ $criteria->id }}"
                                                {{ old('criteria_id', $evaluation->criteria_id) == $criteria->id ? 'selected' : '' }}
                                                data-weight="{{ $criteria->weight }}"
                                                data-type="{{ $criteria->type }}">
                                            {{ $criteria->name }} ({{ $criteria->weight }}%)
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="criteria_id_error">
                                    @error('criteria_id'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Info Display -->
                    <div class="row mb-4" id="employeeInfo" style="display: none;">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Employee Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Department:</small>
                                            <div id="employeeDepartment">-</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Position:</small>
                                            <div id="employeePosition">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Criteria Info Display -->
                    <div class="row mb-4" id="criteriaInfo" style="display: none;">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Criteria Information</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Weight:</small>
                                            <div><span id="criteriaWeight">-</span>%</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Type:</small>
                                            <div>
                                                <span id="criteriaType" class="badge">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Explanation:</small>
                                            <div><small id="criteriaExplanation">-</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="score" class="form-label">
                                    Evaluation Score <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('score') is-invalid @enderror"
                                           id="score"
                                           name="score"
                                           value="{{ old('score', $evaluation->score) }}"
                                           min="1"
                                           max="100"
                                           placeholder="1-100"
                                           required>
                                    <span class="input-group-text">/100</span>
                                </div>
                                <div class="invalid-feedback" id="score_error">
                                    @error('score'){{ $message }}@enderror
                                </div>
                                <div class="form-text">
                                    <small>Score range: 1-100 (1 = Very Poor, 100 = Very Good)</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="evaluation_period" class="form-label">
                                    Evaluation Period <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('evaluation_period') is-invalid @enderror"
                                       id="evaluation_period"
                                       name="evaluation_period"
                                       value="{{ old('evaluation_period', $evaluation->evaluation_period) }}"
                                       placeholder="YYYY-MM (example: 2024-01)"
                                       pattern="[0-9]{4}-[0-9]{2}"
                                       required>
                                <div class="invalid-feedback" id="evaluation_period_error">
                                    @error('evaluation_period'){{ $message }}@enderror
                                </div>
                                <div class="form-text">
                                    <small>Format: YYYY-MM (example: 2024-01 for January 2024)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Score Visualization -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>
                                Score Visualization
                            </h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar"
                                             id="scoreBar"
                                             style="width: {{ $evaluation->score }}%"
                                             role="progressbar">
                                            <span id="scoreText">{{ $evaluation->score }}</span>
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
                                <div class="col-md-4 text-center">
                                    <div class="fs-4 fw-bold" id="scoreCategory">
                                        @if($evaluation->score >= 90) Excellent
                                        @elseif($evaluation->score >= 80) Good
                                        @elseif($evaluation->score >= 70) Average
                                        @elseif($evaluation->score >= 60) Poor
                                        @else Very Poor
                                        @endif
                                    </div>
                                    <small class="text-muted">Category</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back
                        </a>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-undo me-1"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>
                                Update Evaluation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Real-time validation
    $('#evaluationForm input, #evaluationForm select').on('change input', function() {
        validateField(this);
        updateScoreVisualization();
    });

    // Form submission with validation
    $('#evaluationForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            submitForm();
        }
    });

    // Employee selection change
    $('#employee_id').change(function() {
        updateEmployeeInfo();
    });

    // Criteria selection change
    $('#criteria_id').change(function() {
        updateCriteriaInfo();
    });

    // Score input change
    $('#score').on('input', function() {
        updateScoreVisualization();
    });

    // Initial setup
    if ($('#employee_id').val()) {
        updateEmployeeInfo();
    }
    if ($('#criteria_id').val()) {
        updateCriteriaInfo();
    }
    updateScoreVisualization();
});

function updateEmployeeInfo() {
    const selectedOption = $('#employee_id option:selected');

    if (selectedOption.val()) {
        $('#employeeDepartment').text(selectedOption.data('department') || 'Not specified');
        $('#employeePosition').text(selectedOption.data('position') || 'Not specified');
        $('#employeeInfo').show();
    } else {
        $('#employeeInfo').hide();
    }
}

function updateCriteriaInfo() {
    const selectedOption = $('#criteria_id option:selected');

    if (selectedOption.val()) {
        const weight = selectedOption.data('weight');
        const type = selectedOption.data('type');

        $('#criteriaWeight').text(weight);
        $('#criteriaType').text(type.charAt(0).toUpperCase() + type.slice(1))
                          .removeClass('bg-success bg-warning')
                          .addClass(type === 'benefit' ? 'bg-success' : 'bg-warning');

        const explanation = type === 'benefit' ?
            'Higher value is better' :
            'Lower value is better';
        $('#criteriaExplanation').text(explanation);

        $('#criteriaInfo').show();
    } else {
        $('#criteriaInfo').hide();
    }
}

function updateScoreVisualization() {
    const score = parseInt($('#score').val()) || 0;

    // Update progress bar
    $('#scoreBar').css('width', score + '%');
    $('#scoreText').text(score);

    // Update color and category
    let category, colorClass;

    if (score >= 90) {
        category = 'Excellent';
        colorClass = 'bg-success';
    } else if (score >= 80) {
        category = 'Good';
        colorClass = 'bg-primary';
    } else if (score >= 70) {
        category = 'Average';
        colorClass = 'bg-warning';
    } else if (score >= 60) {
        category = 'Poor';
        colorClass = 'bg-danger';
    } else {
        category = 'Very Poor';
        colorClass = 'bg-dark';
    }

    $('#scoreBar').removeClass('bg-success bg-primary bg-warning bg-danger bg-dark')
                  .addClass(colorClass);
    $('#scoreCategory').text(category);
}

function validateField(field) {
    const fieldName = field.name;
    const fieldValue = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Remove existing validation classes
    $(field).removeClass('is-valid is-invalid');
    $(`#${fieldName}_error`).text('');

    switch(fieldName) {
        case 'employee_id':
            if (!fieldValue) {
                errorMessage = 'Employee is required';
                isValid = false;
            }
            break;

        case 'criteria_id':
            if (!fieldValue) {
                errorMessage = 'Criteria is required';
                isValid = false;
            }
            break;

        case 'score':
            const score = parseInt(fieldValue);
            if (!fieldValue || isNaN(score)) {
                errorMessage = 'Score is required';
                isValid = false;
            } else if (score < 1 || score > 100) {
                errorMessage = 'Score must be between 1-100';
                isValid = false;
            }
            break;

        case 'evaluation_period':
            if (!fieldValue) {
                errorMessage = 'Evaluation period is required';
                isValid = false;
            } else if (!/^\d{4}-\d{2}$/.test(fieldValue)) {
                errorMessage = 'Invalid period format (YYYY-MM)';
                isValid = false;
            }
            break;
    }

    if (isValid) {
        $(field).addClass('is-valid');
    } else {
        $(field).addClass('is-invalid');
        $(`#${fieldName}_error`).text(errorMessage);
    }

    return isValid;
}

function validateForm() {
    let isValid = true;

    $('#evaluationForm input[required], #evaluationForm select[required]').each(function() {
        if (!validateField(this)) {
            isValid = false;
        }
    });

    return isValid;
}

function submitForm() {
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();

    // Disable button and show loading
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');

    // Submit form
    $('#evaluationForm')[0].submit();
}

// Reset form
$('button[type="reset"]').on('click', function() {
    $('#evaluationForm input, #evaluationForm select').removeClass('is-valid is-invalid');
    $('.invalid-feedback').text('');
    $('#employeeInfo, #criteriaInfo').hide();
    updateScoreVisualization();
});

// Auto-format period input
$('#evaluation_period').on('input', function() {
    let value = this.value.replace(/[^0-9]/g, '');
    if (value.length >= 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 6);
    }
    this.value = value;
});

// Quick score buttons
function setScore(score) {
    $('#score').val(score).trigger('input');
}

// Add quick score buttons after page load
$(document).ready(function() {
    const quickScoreButtons = `
        <div class="mt-2">
            <small class="text-muted">Quick Select:</small><br>
            <button type="button" class="btn btn-outline-success btn-sm me-1" onclick="setScore(100)">100</button>
            <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="setScore(90)">90</button>
            <button type="button" class="btn btn-outline-info btn-sm me-1" onclick="setScore(80)">80</button>
            <button type="button" class="btn btn-outline-warning btn-sm me-1" onclick="setScore(70)">70</button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="setScore(60)">60</button>
        </div>
    `;
    $('#score').parent().parent().append(quickScoreButtons);
});
</script>
@endpush

@push('styles')
<style>
.form-control.is-valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.79-.79.79.79.79-.79L7.62 8.5 8 8.21 4.5 4.71 4.21 5 .5 1.29.21 1.58l-.21.21.79.79z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.187rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4m0-2.4-2.4 2.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.187rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.progress {
    border-radius: 0.375rem;
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease, background-color 0.3s ease;
    border-radius: 0.375rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush
