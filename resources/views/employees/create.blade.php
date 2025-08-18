@extends('layouts.main')

@section('title', __('Add Employee') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Add Employee'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">{{ __('Create a new employee record with complete information') }}</p>
    </div>
    <x-ui.button 
        href="{{ route('employees.index') }}" 
        variant="outline-secondary" 
        icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-plus text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">{{ __('Employee Information') }}</h5>
                        <small class="text-muted">{{ __('Fill in all required fields marked with *') }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.store') }}" method="POST" id="employeeForm" novalidate>
                    @csrf

                    <!-- Basic Information Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-primary mb-3">
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
                                           value="{{ old('employee_code') }}"
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
                                           value="{{ old('name') }}"
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
                        <h6 class="fw-semibold text-primary mb-3">
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
                                           value="{{ old('position') }}"
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
                                           value="{{ old('department') }}"
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
                        <h6 class="fw-semibold text-primary mb-3">
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
                                           value="{{ old('email') }}"
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

                    <!-- Form Actions -->
                    <div class="border-top pt-4 mt-4">
                        <div class="flex justify-between items-center">
                            <x-ui.button 
                                href="{{ route('employees.index') }}" 
                                variant="outline-secondary" 
                                icon="fas fa-arrow-left">
                                {{ __('Back to List') }}
                            </x-ui.button>
                            <div class="flex gap-2">
                                <x-ui.button 
                                    variant="outline-secondary" 
                                    type="reset" 
                                    icon="fas fa-undo">
                                    {{ __('Reset Form') }}
                                </x-ui.button>
                                <x-ui.button 
                                    variant="primary" 
                                    type="submit" 
                                    icon="fas fa-save" 
                                    id="submitBtn" 
                                    class="px-4">
                                    {{ __('Create Employee') }}
                                </x-ui.button>
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
    // Real-time validation
    $('#employeeForm input').on('blur', function() {
        validateField(this);
    });

    // Form submission with validation
    $('#employeeForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            submitForm();
        }
    });

    // Auto-generate employee code suggestion
    $('#name').on('input', function() {
        if (!$('#employee_code').val()) {
            generateEmployeeCode();
        }
    });

    // Email domain suggestion
    $('#name, #department').on('input', function() {
        if (!$('#email').val()) {
            generateEmailSuggestion();
        }
    });
});

function validateField(field) {
    const fieldName = field.name;
    const fieldValue = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Remove existing validation classes
    $(field).removeClass('is-valid is-invalid');
    $(`#${fieldName}_error`).text('');

    switch(fieldName) {
        case 'employee_code':
            if (!fieldValue) {
                errorMessage = 'Kode karyawan wajib diisi';
                isValid = false;
            } else if (fieldValue.length < 3) {
                errorMessage = 'Kode karyawan minimal 3 karakter';
                isValid = false;
            } else if (fieldValue.length > 20) {
                errorMessage = 'Kode karyawan maksimal 20 karakter';
                isValid = false;
            } else if (!/^[A-Z0-9]+$/.test(fieldValue)) {
                errorMessage = 'Kode karyawan hanya boleh huruf besar dan angka';
                isValid = false;
            }
            break;

        case 'name':
            if (!fieldValue) {
                errorMessage = 'Nama lengkap wajib diisi';
                isValid = false;
            } else if (fieldValue.length < 2) {
                errorMessage = 'Nama minimal 2 karakter';
                isValid = false;
            } else if (fieldValue.length > 255) {
                errorMessage = 'Nama maksimal 255 karakter';
                isValid = false;
            } else if (!/^[a-zA-Z\s\.]+$/.test(fieldValue)) {
                errorMessage = 'Nama hanya boleh huruf, spasi, dan titik';
                isValid = false;
            }
            break;

        case 'position':
            if (!fieldValue) {
                errorMessage = 'Posisi wajib diisi';
                isValid = false;
            } else if (fieldValue.length < 2) {
                errorMessage = 'Posisi minimal 2 karakter';
                isValid = false;
            }
            break;

        case 'department':
            if (!fieldValue) {
                errorMessage = 'Department wajib diisi';
                isValid = false;
            } else if (fieldValue.length < 2) {
                errorMessage = 'Department minimal 2 karakter';
                isValid = false;
            }
            break;

        case 'email':
            if (!fieldValue) {
                errorMessage = 'Email wajib diisi';
                isValid = false;
            } else if (!isValidEmail(fieldValue)) {
                errorMessage = 'Format email tidak valid';
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

    $('#employeeForm input[required]').each(function() {
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
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Creating...") }}');

    // Submit form
    $('#employeeForm')[0].submit();
}

function generateEmployeeCode() {
    const name = $('#name').val().trim();
    const department = $('#department').val().trim();

    if (name.length >= 2) {
        // Generate code from name initials + random number
        const nameInitials = name.split(' ').map(word => word.charAt(0).toUpperCase()).join('');
        const randomNum = Math.floor(Math.random() * 999) + 1;
        const code = nameInitials + randomNum.toString().padStart(3, '0');

        $('#employee_code').val(code);
    }
}

function generateEmailSuggestion() {
    const name = $('#name').val().trim();
    const department = $('#department').val().trim();

    if (name.length >= 2) {
        // Generate email from name
        const emailName = name.toLowerCase().replace(/\s+/g, '.');
        const emailSuggestion = `${emailName}@company.com`;

        $('#email').attr('placeholder', emailSuggestion);
    }
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
    // Capitalize first letter of each word
    this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
});

// Reset form
$('button[type="reset"]').on('click', function() {
    $('#employeeForm input').removeClass('is-valid is-invalid');
    $('.invalid-feedback').text('');
});
</script>
@endpush
