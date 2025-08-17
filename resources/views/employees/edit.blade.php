@extends('layouts.main')

@section('title', __('Edit Employee') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Edit Employee'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Edit Employee') }}</h1>
        <p class="text-muted mb-0">{{ __('Update employee information') }}: <span class="fw-semibold">{{ $employee->name }}</span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i>
            {{ __('View Details') }}
        </a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            {{ __('Back to List') }}
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-edit text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 fw-semibold">{{ __('Update Employee Information') }}</h5>
                        <small class="text-muted">{{ __('Modify the fields you want to update') }}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">{{ __('Employee Code') }}</small>
                        <span class="badge bg-primary fs-6">{{ $employee->employee_code }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.update', $employee->id) }}" method="POST" id="employeeEditForm" novalidate>
                    @csrf
                    @method('PUT')

                    <!-- Basic Information Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-warning mb-3">
                            <i class="fas fa-user me-2"></i>{{ __('Basic Information') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="employee_code" class="form-label fw-medium">
                                    {{ __('Employee Code') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-hashtag text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control @error('employee_code') is-invalid @enderror"
                                           id="employee_code"
                                           name="employee_code"
                                           value="{{ old('employee_code', $employee->employee_code) }}"
                                           placeholder="{{ __('e.g., EMP001') }}"
                                           required>
                                    <div class="invalid-feedback" id="employee_code_error">
                                        @error('employee_code'){{ $message }}@enderror
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('Unique code for each employee (max 20 characters)') }}
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">
                                    {{ __('Full Name') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $employee->name) }}"
                                           placeholder="{{ __('Enter full name') }}"
                                           required>
                                    <div class="invalid-feedback" id="name_error">
                                        @error('name'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-warning mb-3">
                            <i class="fas fa-briefcase me-2"></i>{{ __('Professional Information') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="position" class="form-label fw-medium">
                                    {{ __('Position/Role') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user-tie text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control @error('position') is-invalid @enderror"
                                           id="position"
                                           name="position"
                                           value="{{ old('position', $employee->position) }}"
                                           placeholder="{{ __('e.g., Senior Developer') }}"
                                           list="positionList"
                                           required>
                                    <div class="invalid-feedback" id="position_error">
                                        @error('position'){{ $message }}@enderror
                                    </div>
                                </div>
                                <datalist id="positionList">
                                    <option value="Manager">
                                    <option value="Senior Developer">
                                    <option value="Junior Developer">
                                    <option value="UI/UX Designer">
                                    <option value="Business Analyst">
                                    <option value="Project Manager">
                                    <option value="Quality Assurance">
                                    <option value="DevOps Engineer">
                                    <option value="HR Specialist">
                                    <option value="Finance Analyst">
                                    <option value="Marketing Specialist">
                                </datalist>
                            </div>

                            <div class="col-md-6">
                                <label for="department" class="form-label fw-medium">
                                    {{ __('Department') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-building text-muted"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control @error('department') is-invalid @enderror"
                                           id="department"
                                           name="department"
                                           value="{{ old('department', $employee->department) }}"
                                           placeholder="{{ __('e.g., IT Development') }}"
                                           list="departmentList"
                                           required>
                                    <div class="invalid-feedback" id="department_error">
                                        @error('department'){{ $message }}@enderror
                                    </div>
                                </div>
                                <datalist id="departmentList">
                                    <option value="IT Development">
                                    <option value="Design">
                                    <option value="Project Management">
                                    <option value="Business Development">
                                    <option value="IT Infrastructure">
                                    <option value="QA Testing">
                                    <option value="Human Resources">
                                    <option value="Marketing">
                                    <option value="Finance">
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-warning mb-3">
                            <i class="fas fa-envelope me-2"></i>{{ __('Contact Information') }}
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <label for="email" class="form-label fw-medium">
                                    {{ __('Email Address') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-at text-muted"></i>
                                    </span>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $employee->email) }}"
                                           placeholder="{{ __('example@company.com') }}"
                                           required>
                                    <div class="invalid-feedback" id="email_error">
                                        @error('email'){{ $message }}@enderror
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('Professional email address for communication') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading fw-semibold">
                                    <i class="fas fa-info-circle me-2"></i>{{ __('Additional Information') }}
                                </h6>
                                <div class="small text-muted">
                                    <div class="mb-2">
                                        <strong>{{ __('Created') }}:</strong> {{ $employee->created_at->format('d M Y, H:i') }}
                                    </div>
                                    <div>
                                        <strong>{{ __('Last Updated') }}:</strong> {{ $employee->updated_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6 class="alert-heading fw-semibold">
                                    <i class="fas fa-chart-line me-2"></i>{{ __('Evaluation Status') }}
                                </h6>
                                <div class="small text-muted">
                                    @php
                                        $latestResult = $employee->latestResult();
                                    @endphp
                                    @if($latestResult)
                                        <div class="mb-2">
                                            <strong>{{ __('Latest Period') }}:</strong> {{ $latestResult->evaluation_period }}
                                        </div>
                                        <div>
                                            <strong>{{ __('Current Ranking') }}:</strong>
                                            <span class="badge bg-success">#{{ $latestResult->ranking }}</span>
                                        </div>
                                    @else
                                        <div class="text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ __('No evaluation yet') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="border-top pt-4 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                {{ __('Back to List') }}
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>
                                    {{ __('View Details') }}
                                </a>
                                <button type="submit" class="btn btn-warning px-4" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>
                                    {{ __('Update Employee') }}
                                </button>
                            </div>
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
    // Store original data for change detection
    const originalData = getFormData();

    // Real-time validation
    $('#employeeEditForm input').on('blur', function() {
        validateField(this);
    });

    // Form submission with validation
    $('#employeeEditForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            submitForm();
        }
    });

    // Check if data has changed
    $('#employeeEditForm input').on('input', function() {
        checkForChanges(originalData);
    });
});

function getFormData() {
    return {
        employee_code: $('#employee_code').val(),
        name: $('#name').val(),
        position: $('#position').val(),
        department: $('#department').val(),
        email: $('#email').val()
    };
}

function checkForChanges(originalData) {
    const currentData = getFormData();
    const hasChanges = JSON.stringify(originalData) !== JSON.stringify(currentData);

    if (hasChanges) {
        $('#submitBtn').removeClass('btn-secondary').addClass('btn-warning');
        $('#submitBtn').find('i').removeClass('fa-save').addClass('fa-edit');
    } else {
        $('#submitBtn').removeClass('btn-warning').addClass('btn-secondary');
        $('#submitBtn').find('i').removeClass('fa-edit').addClass('fa-save');
    }
}

function validateField(field) {
    const fieldName = field.name;
    const fieldValue = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    $(field).removeClass('is-valid is-invalid');
    $(`#${fieldName}_error`).text('');

    switch(fieldName) {
        case 'employee_code':
            if (!fieldValue) {
                errorMessage = '{{ __("Employee code is required") }}';
                isValid = false;
            } else if (fieldValue.length < 3) {
                errorMessage = '{{ __("Employee code must be at least 3 characters") }}';
                isValid = false;
            } else if (fieldValue.length > 20) {
                errorMessage = '{{ __("Employee code must not exceed 20 characters") }}';
                isValid = false;
            } else if (!/^[A-Z0-9]+$/.test(fieldValue)) {
                errorMessage = '{{ __("Employee code must contain only uppercase letters and numbers") }}';
                isValid = false;
            }
            break;

        case 'name':
            if (!fieldValue) {
                errorMessage = '{{ __("Full name is required") }}';
                isValid = false;
            } else if (fieldValue.length < 2) {
                errorMessage = '{{ __("Name must be at least 2 characters") }}';
                isValid = false;
            } else if (fieldValue.length > 255) {
                errorMessage = '{{ __("Name must not exceed 255 characters") }}';
                isValid = false;
            }
            break;

        case 'position':
            if (!fieldValue) {
                errorMessage = '{{ __("Position is required") }}';
                isValid = false;
            }
            break;

        case 'department':
            if (!fieldValue) {
                errorMessage = '{{ __("Department is required") }}';
                isValid = false;
            }
            break;

        case 'email':
            if (!fieldValue) {
                errorMessage = '{{ __("Email is required") }}';
                isValid = false;
            } else if (!isValidEmail(fieldValue)) {
                errorMessage = '{{ __("Please enter a valid email address") }}';
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

    $('#employeeEditForm input[required]').each(function() {
        if (!validateField(this)) {
            isValid = false;
        }
    });

    return isValid;
}

function submitForm() {
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Updating...") }}');

    $('#employeeEditForm')[0].submit();
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Auto-format inputs
$('#employee_code').on('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});

$('#name').on('input', function() {
    this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
});
</script>
@endpush
