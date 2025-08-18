@extends('layouts.main')

@section('title', 'Edit Criteria - SAW Employee Evaluation')
@section('page-title', 'Edit Evaluation Criteria')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Weight Status Alert -->
        <div class="mb-6">
            <x-ui.alert type="info" title="Criteria Weight Status">
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span>Current total: <strong class="text-blue-800">{{ $totalWeight }}%</strong></span>
                        <span>Available remaining: <strong class="text-blue-800">{{ $remainingWeight }}%</strong></span>
                    </div>
                    <div class="w-full bg-blue-200 rounded-full h-2.5">
                        <div class="bg-{{ $totalWeight == 100 ? 'success' : 'primary' }}-600 h-2.5 rounded-full transition-all duration-300" 
                             style="width: {{ $totalWeight }}%">
                        </div>
                    </div>
                    @if($remainingWeight <= 0)
                        <p class="text-sm text-blue-700 mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Weight limit reached. Consider adjusting existing criteria weights.
                        </p>
                    @endif
                </div>
            </x-ui.alert>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Edit Evaluation Criteria Form
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('criterias.update', $criteria->id) }}" method="POST" id="criteriaForm" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Criteria Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $criteria->name) }}"
                                       placeholder="Example: Work Performance"
                                       required>
                                <div class="invalid-feedback" id="name_error">
                                    @error('name'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">
                                    Name that describes the employee evaluation aspect
                                </small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="weight" class="form-label">
                                    Weight (%) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('weight') is-invalid @enderror"
                                           id="weight"
                                           name="weight"
                                           value="{{ old('weight', $criteria->weight) }}"
                                           min="1"
                                           max="{{ $remainingWeight + $criteria->weight }}"
                                           placeholder="1-{{ $remainingWeight + $criteria->weight }}"
                                           required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback" id="weight_error">
                                    @error('weight'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted" id="weightHelp">
                                    Maximum: {{ $remainingWeight + $criteria->weight }}%
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="type" class="form-label">
                            Criteria Type <span class="text-danger">*</span>
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="type"
                                           id="typeBenefit"
                                           value="benefit"
                                           {{ old('type', $criteria->type) == 'benefit' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="typeBenefit">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2 fs-6">Benefit</span>
                                            <div>
                                                <strong>Benefit (Positive)</strong>
                                                <div class="text-muted small">Higher value is better</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="mt-2 ms-4">
                                    <small class="text-muted">
                                        <strong>Examples:</strong> Performance, Attendance, Technical Skills, etc.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="type"
                                           id="typeCost"
                                           value="cost"
                                           {{ old('type', $criteria->type) == 'cost' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="typeCost">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning me-2 fs-6">Cost</span>
                                            <div>
                                                <strong>Cost (Negative)</strong>
                                                <div class="text-muted small">Lower value is better</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="mt-2 ms-4">
                                    <small class="text-muted">
                                        <strong>Examples:</strong> Error Count, Lateness, etc.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="type_error">
                            @error('type'){{ $message }}@enderror
                        </div>
                    </div>

                    <!-- Weight Visualization -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Weight Distribution Preview
                            </h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="mb-2">
                                        <span>Other criteria weight: <strong>{{ $totalWeight - $criteria->weight }}%</strong></span>
                                    </div>
                                    <div class="mb-2">
                                        <span>This criteria weight: <strong id="newWeightDisplay">{{ $criteria->weight }}%</strong></span>
                                    </div>
                                    <div class="mb-2">
                                        <span>Total after update: <strong id="totalAfterDisplay">{{ $totalWeight }}%</strong></span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-secondary"
                                             style="width: {{ $totalWeight - $criteria->weight }}%"
                                             title="Other Criteria: {{ $totalWeight - $criteria->weight }}%">
                                        </div>
                                        <div class="progress-bar bg-primary"
                                             id="newWeightBar"
                                             style="width: {{ $criteria->weight }}%"
                                             title="This Criteria: {{ $criteria->weight }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="fs-4 fw-bold" id="remainingDisplay">{{ $remainingWeight }}%</div>
                                    <small class="text-muted">Available Remaining</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <x-ui.button 
                            href="{{ route('criterias.index') }}" 
                            variant="secondary" 
                            icon="fas fa-arrow-left">
                            Back
                        </x-ui.button>
                        <div class="flex gap-2">
                            <x-ui.button 
                                variant="outline-secondary" 
                                type="reset" 
                                icon="fas fa-undo">
                                Reset
                            </x-ui.button>
                            <x-ui.button 
                                variant="primary" 
                                type="submit" 
                                icon="fas fa-save" 
                                id="submitBtn">
                                Update Criteria
                            </x-ui.button>
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
const maxWeight = {{ $remainingWeight + $criteria->weight }};
const currentTotal = {{ $totalWeight - $criteria->weight }};
const currentWeight = {{ $criteria->weight }};

$(document).ready(function() {
    // Real-time validation
    $('#criteriaForm input, #criteriaForm input[type="radio"]').on('input change', function() {
        validateField(this);
        updateWeightVisualization();
    });

    // Weight input validation with real-time API check
    $('#weight').on('input', function() {
        const weight = parseInt($(this).val()) || 0;
        validateWeightRealTime(weight);
        updateWeightVisualization();
    });

    // Form submission with validation
    $('#criteriaForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            submitForm();
        }
    });

    // Initial visualization update
    updateWeightVisualization();
});

function validateField(field) {
    const fieldName = field.name;
    const fieldValue = field.type === 'radio' ? $(`input[name="${fieldName}"]:checked`).val() : field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Remove existing validation classes
    $(field).removeClass('is-valid is-invalid');
    $(`#${fieldName}_error`).text('');

    switch(fieldName) {
        case 'name':
            if (!fieldValue) {
                errorMessage = 'Criteria name is required';
                isValid = false;
            } else if (fieldValue.length < 3) {
                errorMessage = 'Criteria name must be at least 3 characters';
                isValid = false;
            } else if (fieldValue.length > 255) {
                errorMessage = 'Criteria name must not exceed 255 characters';
                isValid = false;
            }
            break;

        case 'weight':
            const weight = parseInt(fieldValue) || 0;
            if (!fieldValue || weight <= 0) {
                errorMessage = 'Weight is required and must be greater than 0';
                isValid = false;
            } else if (weight > maxWeight) {
                errorMessage = `Maximum weight is ${maxWeight}% (available remaining)`;
                isValid = false;
            } else if (weight > 50) {
                errorMessage = 'Maximum weight is 50% per criteria';
                isValid = false;
            }
            break;

        case 'type':
            if (!fieldValue) {
                errorMessage = 'Criteria type is required';
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

function validateWeightRealTime(weight) {
    if (weight > 0) {
        $.post("{{ route('criterias.validate-weight') }}", {
            weight: weight,
            exclude_id: {{ $criteria->id }},
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            const weightField = $('#weight')[0];

            if (response.is_valid) {
                $(weightField).removeClass('is-invalid').addClass('is-valid');
                $('#weight_error').text('');
                $('#submitBtn').prop('disabled', false);
            } else {
                $(weightField).removeClass('is-valid').addClass('is-invalid');
                $('#weight_error').text(response.message);
                $('#submitBtn').prop('disabled', true);
            }
        }).fail(function() {
            // Fallback validation
            validateField($('#weight')[0]);
        });
    }
}

function updateWeightVisualization() {
    const newWeight = parseInt($('#weight').val()) || currentWeight;
    const totalAfter = currentTotal + newWeight;
    const remaining = Math.max(0, 100 - totalAfter);

    // Update displays
    $('#newWeightDisplay').text(newWeight + '%');
    $('#totalAfterDisplay').text(totalAfter + '%');
    $('#remainingDisplay').text(remaining + '%');

    // Update progress bar
    const otherWeight = currentTotal;
    $('#newWeightBar').css('width', newWeight + '%')
                      .attr('title', `This Criteria: ${newWeight}%`);

    // Update colors based on status
    if (totalAfter > 100) {
        $('#newWeightBar').removeClass('bg-primary bg-warning').addClass('bg-danger');
        $('#totalAfterDisplay').addClass('text-danger').removeClass('text-success text-warning');
    } else if (totalAfter === 100) {
        $('#newWeightBar').removeClass('bg-danger bg-warning').addClass('bg-success');
        $('#totalAfterDisplay').addClass('text-success').removeClass('text-danger text-warning');
    } else {
        $('#newWeightBar').removeClass('bg-danger bg-success').addClass('bg-primary');
        $('#totalAfterDisplay').removeClass('text-danger text-success').addClass('text-primary');
    }
}

function validateForm() {
    let isValid = true;

    // Validate all required fields
    $('#criteriaForm input[required], #criteriaForm input[type="radio"][required]').each(function() {
        if (this.type === 'radio') {
            if (!$(`input[name="${this.name}"]:checked`).length) {
                isValid = false;
                $(`#${this.name}_error`).text('Field this is required');
            }
        } else {
            if (!validateField(this)) {
                isValid = false;
            }
        }
    });

    // Additional check for total weight
    const newWeight = parseInt($('#weight').val()) || 0;
    const totalAfter = currentTotal + newWeight;

    if (totalAfter > 100) {
        $('#weight').removeClass('is-valid').addClass('is-invalid');
        $('#weight_error').text(`Total weight will become ${totalAfter}%, exceeding maximum 100%`);
        isValid = false;
    }

    return isValid;
}

function submitForm() {
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();

    // Disable button and show loading
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');

    // Submit form
    $('#criteriaForm')[0].submit();
}

// Reset form
$('button[type="reset"]').on('click', function() {
    $('#criteriaForm input').removeClass('is-valid is-invalid');
    $('.invalid-feedback').text('');
    $('#weight').val(currentWeight).trigger('input');
    updateWeightVisualization();
});

// Auto-format name
$('#name').on('input', function() {
    // Capitalize first letter of each word
    this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
});
</script>
@endpush

@push('styles')
<style>
.form-check-lg .form-check-input {
    width: 1.5rem;
    height: 1.5rem;
    margin-top: 0.125rem;
}

.form-check-lg .form-check-label {
    font-size: 1rem;
    margin-left: 0.5rem;
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    transition: width 0.3s ease;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.form-control.is-valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.79-.79.79.79.79-.79L7.62 8.5 8 8.21 4.5 4.71 4.21 5 .5 1.29.21 1.58l-.21.21.79.79z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.187rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4m0-2.4-2.4 2.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.187rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>
@endpush
