@extends('layouts.main')

@section('title', 'Add New User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>Add New User
                    </h1>
                    <p class="text-muted mt-1">Create a new user account with appropriate role and permissions</p>
                </div>
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" id="userForm" novalidate>
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Basic Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-medium">
                                        Full Name <span class="text-danger">*</span>
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
                                               placeholder="Enter full name"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-medium">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               placeholder="Enter email address"
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-key me-2"></i>Security Settings
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="Enter password"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Minimum 8 characters required</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-medium">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               placeholder="Confirm password"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye" id="password_confirmation-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role & Access Section -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Role & Access Control
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="role" class="form-label fw-medium">
                                        User Role <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user-tag text-muted"></i>
                                        </span>
                                        <select class="form-select @error('role') is-invalid @enderror"
                                                id="role"
                                                name="role"
                                                required>
                                            <option value="">Select Role</option>
                                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>
                                                User - Basic access to view and create evaluations
                                            </option>
                                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>
                                                Manager - Can manage evaluations and view reports
                                            </option>
                                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                                Admin - Full system access and user management
                                            </option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Account Settings</label>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                <strong>Active Account</strong>
                                                <small class="d-block text-muted">User can login and access the system</small>
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="verify_email"
                                                   name="verify_email"
                                                   value="1"
                                                   {{ old('verify_email', false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="verify_email">
                                                <strong>Email Verified</strong>
                                                <small class="d-block text-muted">Mark email as verified immediately</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Information Alert -->
                        <div class="alert alert-info" id="role-info" style="display: none;">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-2"></i>Role Permissions
                            </h6>
                            <div id="role-description"></div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-1"></i>Create User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Enhanced form styling */
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.25rem 1.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.alert {
    border-radius: 0.75rem;
    border: none;
}

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Password strength indicator */
.password-strength {
    height: 4px;
    border-radius: 2px;
    margin-top: 0.25rem;
    background-color: #e9ecef;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-weak { background-color: #dc3545; }
.strength-fair { background-color: #ffc107; }
.strength-good { background-color: #20c997; }
.strength-strong { background-color: #198754; }

/* Role selection enhancements */
.form-select option {
    padding: 0.5rem;
}

/* Animation for form sections */
.card-body > div {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Role selection change handler
    $('#role').change(function() {
        const role = $(this).val();
        const roleInfo = $('#role-info');
        const roleDescription = $('#role-description');

        if (role) {
            let description = '';

            switch(role) {
                case 'user':
                    description = `
                        <ul class="mb-0">
                            <li>View and create employee evaluations</li>
                            <li>View evaluation results and reports</li>
                            <li>Access basic dashboard features</li>
                            <li>Update own profile information</li>
                        </ul>
                    `;
                    break;
                case 'manager':
                    description = `
                        <ul class="mb-0">
                            <li><strong>All User permissions, plus:</strong></li>
                            <li>Manage employee data and criteria</li>
                            <li>Generate and export detailed reports</li>
                            <li>View advanced analytics and charts</li>
                            <li>Manage evaluation periods and calculations</li>
                        </ul>
                    `;
                    break;
                case 'admin':
                    description = `
                        <ul class="mb-0">
                            <li><strong>All Manager permissions, plus:</strong></li>
                            <li>Full user management (create, edit, delete users)</li>
                            <li>System configuration and settings</li>
                            <li>Access to all administrative features</li>
                            <li>Backup and maintenance operations</li>
                        </ul>
                    `;
                    break;
            }

            roleDescription.html(description);
            roleInfo.slideDown();
        } else {
            roleInfo.slideUp();
        }
    });

    // Password strength checker
    $('#password').on('input', function() {
        const password = $(this).val();
        checkPasswordStrength(password);
    });

    // Form validation
    $('#userForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            // Show loading state
            $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-1"></i>Creating User...').prop('disabled', true);

            // Submit form
            this.submit();
        }
    });

    // Real-time email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        if (email) {
            checkEmailAvailability(email);
        }
    });
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');

    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        eye.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    const strengthBar = $('#password-strength-bar');
    let strength = 0;
    let className = '';
    let strengthText = '';

    // Check password criteria
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    // Remove existing strength indicator
    $('.password-strength').remove();

    if (password.length > 0) {
        // Add strength indicator
        const strengthHtml = `
            <div class="password-strength">
                <div class="password-strength-bar" id="password-strength-bar" style="width: ${strength * 20}%"></div>
            </div>
            <small class="text-muted" id="password-strength-text"></small>
        `;
        $('#password').parent().after(strengthHtml);

        // Set strength class and text
        switch(strength) {
            case 0:
            case 1:
                className = 'strength-weak';
                strengthText = 'Very Weak';
                break;
            case 2:
                className = 'strength-fair';
                strengthText = 'Weak';
                break;
            case 3:
                className = 'strength-good';
                strengthText = 'Good';
                break;
            case 4:
            case 5:
                className = 'strength-strong';
                strengthText = 'Strong';
                break;
        }

        $('#password-strength-bar').addClass(className);
        $('#password-strength-text').text(`Password Strength: ${strengthText}`);
    }
}

// Email availability checker
function checkEmailAvailability(email) {
    // This would typically make an AJAX call to check email availability
    // For now, we'll just validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const emailField = $('#email');

    if (!emailRegex.test(email)) {
        emailField.addClass('is-invalid');
        emailField.next('.invalid-feedback').remove();
        emailField.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
    } else {
        emailField.removeClass('is-invalid');
        emailField.next('.invalid-feedback').remove();
    }
}

// Form validation
function validateForm() {
    let isValid = true;

    // Clear previous validation
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    // Check required fields
    $('input[required], select[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">This field is required.</div>');
            isValid = false;
        }
    });

    // Check password confirmation
    const password = $('#password').val();
    const passwordConfirmation = $('#password_confirmation').val();

    if (password !== passwordConfirmation) {
        $('#password_confirmation').addClass('is-invalid');
        $('#password_confirmation').after('<div class="invalid-feedback">Passwords do not match.</div>');
        isValid = false;
    }

    return isValid;
}
</script>
@endpush


