@extends('layouts.main')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-user-edit text-primary me-2"></i>Edit User
                    </h1>
                    <p class="text-muted mt-1">Update user information, role and permissions</p>
                </div>
                <div class="flex gap-2">
                    <x-ui.button 
                        href="{{ route('users.show', $user->id) }}" 
                        variant="info" 
                        icon="fas fa-eye">
                        View Profile
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('users.index') }}" 
                        variant="secondary" 
                        icon="fas fa-arrow-left">
                        Back to Users
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Info Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="avatar me-3">
                        <div class="avatar-circle bg-primary text-white">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1"><strong>{{ $user->name }}</strong></h6>
                        <p class="mb-1">{{ $user->email }}</p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Unverified</span>
                            @endif
                        </div>
                    </div>
                    <div class="ms-auto">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Joined {{ $user->created_at->format('M d, Y') }}
                        </small>
                    </div>
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
                        <i class="fas fa-user-edit me-2"></i>Update User Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST" id="userEditForm" novalidate>
                        @csrf
                        @method('PUT')

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
                                               value="{{ old('name', $user->name) }}"
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
                                               value="{{ old('email', $user->email) }}"
                                               placeholder="Enter email address"
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Update Section -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-key me-2"></i>Password Update
                                <small class="text-muted fw-normal">(Leave blank to keep current password)</small>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">
                                        New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="Enter new password">
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
                                        Confirm New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               placeholder="Confirm new password">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye" id="password_confirmation-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin())
                        <!-- Admin-only: Role & Access Section -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Role & Access Control
                                <span class="badge bg-warning text-dark">Admin Only</span>
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
                                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                                                User - Basic access to view and create evaluations
                                            </option>
                                            <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>
                                                Manager - Can manage evaluations and view reports
                                            </option>
                                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
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
                                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                                   {{ auth()->user()->id === $user->id ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                <strong>Active Account</strong>
                                                <small class="d-block text-muted">
                                                    {{ auth()->user()->id === $user->id ? 'Cannot deactivate your own account' : 'User can login and access the system' }}
                                                </small>
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="verify_email"
                                                   name="verify_email"
                                                   value="1"
                                                   {{ $user->email_verified_at ? 'checked' : '' }}>
                                            <label class="form-check-label" for="verify_email">
                                                <strong>Email Verified</strong>
                                                <small class="d-block text-muted">Mark email as verified</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Warning -->
                        @if($user->role === 'admin' && $user->id === auth()->user()->id)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Important Notice
                            </h6>
                            <p class="mb-0">You are editing your own admin account. Be careful when changing your role or status to avoid losing access to the system.</p>
                        </div>
                        @endif
                        @endif

                        <!-- Change Detection Alert -->
                        <div class="alert alert-secondary d-none" id="changes-alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="changes-count">0</span> field(s) have been modified.
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button 
                                        href="{{ route('users.index') }}" 
                                        variant="secondary" 
                                        icon="fas fa-times">
                                        Cancel
                                    </x-ui.button>
                                    <x-ui.button 
                                        variant="outline-warning" 
                                        type="button" 
                                        icon="fas fa-undo"
                                        onclick="resetForm()">
                                        Reset Changes
                                    </x-ui.button>
                                    <x-ui.button 
                                        variant="primary" 
                                        type="submit" 
                                        icon="fas fa-save" 
                                        id="submitBtn">
                                        Update User
                                    </x-ui.button>
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
/* Enhanced styling */
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
}

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

.alert {
    border-radius: 0.75rem;
    border: none;
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

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Form change detection */
.form-control.changed, .form-select.changed {
    border-left: 4px solid #ffc107;
    background-color: #fff9e6;
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

/* Animation */
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
let originalValues = {};
let changedFields = new Set();

$(document).ready(function() {
    // Store original form values
    storeOriginalValues();

    // Track form changes
    $('#userEditForm input, #userEditForm select').on('input change', function() {
        trackChanges(this);
    });

    // Password strength checker
    $('#password').on('input', function() {
        const password = $(this).val();
        if (password.length > 0) {
            checkPasswordStrength(password);
        } else {
            $('.password-strength').remove();
        }
    });

    // Form validation
    $('#userEditForm').on('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            // Show loading state
            $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-1"></i>Updating User...').prop('disabled', true);

            // Submit form
            this.submit();
        }
    });

    // Role change handler (admin only)
    $('#role').change(function() {
        const role = $(this).val();
        if (role === 'admin' && {{ auth()->user()->id }} === {{ $user->id }}) {
            Swal.fire({
                icon: 'warning',
                title: 'Changing Your Own Role',
                text: 'You are about to change your own admin role. This may affect your access to the system.',
                showCancelButton: true,
                confirmButtonText: 'I Understand',
                cancelButtonText: 'Cancel Change'
            }).then((result) => {
                if (!result.isConfirmed) {
                    $(this).val('{{ $user->role }}').trigger('change');
                }
            });
        }
    });
});

// Store original form values
function storeOriginalValues() {
    $('#userEditForm input, #userEditForm select').each(function() {
        const field = $(this);
        const fieldName = field.attr('name');

        if (field.attr('type') === 'checkbox') {
            originalValues[fieldName] = field.is(':checked');
        } else {
            originalValues[fieldName] = field.val();
        }
    });
}

// Track form changes
function trackChanges(field) {
    const $field = $(field);
    const fieldName = $field.attr('name');
    let currentValue;

    if ($field.attr('type') === 'checkbox') {
        currentValue = $field.is(':checked');
    } else {
        currentValue = $field.val();
    }

    // Check if value changed from original
    if (originalValues[fieldName] !== currentValue) {
        changedFields.add(fieldName);
        $field.addClass('changed');
    } else {
        changedFields.delete(fieldName);
        $field.removeClass('changed');
    }

    // Update changes alert
    updateChangesAlert();
}

// Update changes alert
function updateChangesAlert() {
    const changesAlert = $('#changes-alert');
    const changesCount = $('#changes-count');

    if (changedFields.size > 0) {
        changesCount.text(changedFields.size);
        changesAlert.removeClass('d-none');
    } else {
        changesAlert.addClass('d-none');
    }
}

// Reset form to original values
function resetForm() {
    if (changedFields.size === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'No changes have been made to reset.',
            timer: 2000
        });
        return;
    }

    Swal.fire({
        title: 'Reset Changes?',
        text: `This will reset ${changedFields.size} changed field(s) to their original values.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Reset',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset all fields to original values
            Object.keys(originalValues).forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);

                if (field.attr('type') === 'checkbox') {
                    field.prop('checked', originalValues[fieldName]);
                } else {
                    field.val(originalValues[fieldName]);
                }

                field.removeClass('changed');
            });

            // Clear changes tracking
            changedFields.clear();
            updateChangesAlert();

            // Remove password strength indicator
            $('.password-strength').remove();

            Swal.fire({
                icon: 'success',
                title: 'Changes Reset',
                text: 'All changes have been reset to original values.',
                timer: 2000
            });
        }
    });
}

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

    // Add strength indicator
    const strengthHtml = `
        <div class="password-strength">
            <div class="password-strength-bar" style="width: ${strength * 20}%"></div>
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

    $('.password-strength-bar').addClass(className);
    $('#password-strength-text').text(`Password Strength: ${strengthText}`);
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

    // Check password confirmation if password is provided
    const password = $('#password').val();
    const passwordConfirmation = $('#password_confirmation').val();

    if (password && password !== passwordConfirmation) {
        $('#password_confirmation').addClass('is-invalid');
        $('#password_confirmation').after('<div class="invalid-feedback">Passwords do not match.</div>');
        isValid = false;
    }

    return isValid;
}
</script>
@endpush


