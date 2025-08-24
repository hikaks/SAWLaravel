@extends('layouts.main')

@section('title', __('Edit Employee') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Edit Employee'))

@section('content')
<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Edit Employee') }}</h1>
        <p class="text-gray-600">{{ __('Update employee information') }}: <span class="font-semibold">{{ $employee->name }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            href="{{ route('employees.show', $employee->id) }}" 
            variant="outline-info" 
            icon="fas fa-eye">
            {{ __('View Details') }}
        </x-ui.button>
        <x-ui.button 
            href="{{ route('employees.index') }}" 
            variant="outline-secondary" 
            icon="fas fa-arrow-left">
            {{ __('Back to List') }}
        </x-ui.button>
    </div>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-warning-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-white text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">{{ __('Update Employee Information') }}</h5>
                        <p class="text-sm text-gray-500">{{ __('Modify the fields you want to update') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">{{ __('Employee Code') }}</p>
                    <span class="badge badge-primary text-base">{{ $employee->employee_code }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('employees.update', $employee->id) }}" method="POST" id="employeeEditForm" x-data="employeeEditForm()" @submit="handleSubmit">
                @csrf
                @method('PUT')

                <!-- Basic Information Section -->
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-user"></i>{{ __('Basic Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="employee_code" class="form-label">
                                {{ __('Employee Code') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-hashtag text-gray-400"></i>
                                </div>
                                <input type="text"
                                       class="form-control pl-10 @error('employee_code') border-danger-500 @enderror"
                                       id="employee_code"
                                       name="employee_code"
                                       value="{{ old('employee_code', $employee->employee_code) }}"
                                       placeholder="{{ __('e.g., EMP001') }}"
                                       x-model="form.employee_code"
                                       required>
                            </div>
                            @error('employee_code')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">
                                {{ __('Unique code for each employee (max 20 characters)') }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="name" class="form-label">
                                {{ __('Full Name') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text"
                                       class="form-control pl-10 @error('name') border-danger-500 @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $employee->name) }}"
                                       placeholder="{{ __('Enter full name') }}"
                                       x-model="form.name"
                                       required>
                            </div>
                            @error('name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-briefcase"></i>{{ __('Professional Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="position" class="form-label">
                                {{ __('Position/Role') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-briefcase text-gray-400"></i>
                                </div>
                                <input type="text"
                                       class="form-control pl-10 @error('position') border-danger-500 @enderror"
                                       id="position"
                                       name="position"
                                       value="{{ old('position', $employee->position) }}"
                                       placeholder="{{ __('e.g., Software Engineer') }}"
                                       x-model="form.position"
                                       required>
                            </div>
                            @error('position')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="department" class="form-label">
                                {{ __('Department') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <select class="form-select pl-10 @error('department') border-danger-500 @enderror"
                                        id="department"
                                        name="department"
                                        x-model="form.department"
                                        required>
                                    <option value="">{{ __('Select Department') }}</option>
                                    <option value="IT" {{ old('department', $employee->department) == 'IT' ? 'selected' : '' }}>{{ __('Information Technology') }}</option>
                                    <option value="HR" {{ old('department', $employee->department) == 'HR' ? 'selected' : '' }}>{{ __('Human Resources') }}</option>
                                    <option value="Finance" {{ old('department', $employee->department) == 'Finance' ? 'selected' : '' }}>{{ __('Finance') }}</option>
                                    <option value="Marketing" {{ old('department', $employee->department) == 'Marketing' ? 'selected' : '' }}>{{ __('Marketing') }}</option>
                                    <option value="Operations" {{ old('department', $employee->department) == 'Operations' ? 'selected' : '' }}>{{ __('Operations') }}</option>
                                    <option value="Sales" {{ old('department', $employee->department) == 'Sales' ? 'selected' : '' }}>{{ __('Sales') }}</option>
                                </select>
                            </div>
                            @error('department')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hire_date" class="form-label">
                                {{ __('Hire Date') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date"
                                       class="form-control pl-10 @error('hire_date') border-danger-500 @enderror"
                                       id="hire_date"
                                       name="hire_date"
                                       value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                                       x-model="form.hire_date"
                                       required>
                            </div>
                            @error('hire_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="salary" class="form-label">
                                {{ __('Salary') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-dollar-sign text-gray-400"></i>
                                </div>
                                <input type="number"
                                       class="form-control pl-10 @error('salary') border-danger-500 @enderror"
                                       id="salary"
                                       name="salary"
                                       value="{{ old('salary', $employee->salary) }}"
                                       placeholder="{{ __('Enter salary amount') }}"
                                       x-model="form.salary"
                                       min="0"
                                       step="0.01">
                            </div>
                            @error('salary')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-address-book"></i>{{ __('Contact Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                {{ __('Email Address') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email"
                                       class="form-control pl-10 @error('email') border-danger-500 @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $employee->email) }}"
                                       placeholder="{{ __('employee@company.com') }}"
                                       x-model="form.email"
                                       required>
                            </div>
                            @error('email')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">
                                {{ __('Phone Number') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="tel"
                                       class="form-control pl-10 @error('phone') border-danger-500 @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $employee->phone) }}"
                                       placeholder="{{ __('e.g., +1234567890') }}"
                                       x-model="form.phone">
                            </div>
                            @error('phone')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group md:col-span-2">
                            <label for="address" class="form-label">
                                {{ __('Address') }}
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <textarea class="form-textarea pl-10 @error('address') border-danger-500 @enderror"
                                          id="address"
                                          name="address"
                                          rows="3"
                                          placeholder="{{ __('Enter full address') }}"
                                          x-model="form.address">{{ old('address', $employee->address) }}</textarea>
                            </div>
                            @error('address')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-info-circle"></i>{{ __('Additional Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="birth_date" class="form-label">
                                {{ __('Date of Birth') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-birthday-cake text-gray-400"></i>
                                </div>
                                <input type="date"
                                       class="form-control pl-10 @error('birth_date') border-danger-500 @enderror"
                                       id="birth_date"
                                       name="birth_date"
                                       value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"
                                       x-model="form.birth_date">
                            </div>
                            @error('birth_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">
                                {{ __('Employment Status') }} <span class="text-danger-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-toggle-on text-gray-400"></i>
                                </div>
                                <select class="form-select pl-10 @error('status') border-danger-500 @enderror"
                                        id="status"
                                        name="status"
                                        x-model="form.status"
                                        required>
                                    <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                            @error('status')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group md:col-span-2">
                            <label for="notes" class="form-label">
                                {{ __('Notes') }}
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-sticky-note text-gray-400"></i>
                                </div>
                                <textarea class="form-textarea pl-10 @error('notes') border-danger-500 @enderror"
                                          id="notes"
                                          name="notes"
                                          rows="4"
                                          placeholder="{{ __('Additional notes about the employee...') }}"
                                          x-model="form.notes">{{ old('notes', $employee->notes) }}</textarea>
                            </div>
                            @error('notes')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Change Tracking -->
                <div class="mb-8 p-4 bg-info-50 border border-info-200 rounded-lg">
                    <h6 class="flex items-center gap-2 text-info-800 font-semibold mb-3">
                        <i class="fas fa-history"></i>{{ __('Change History') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-info-700 font-medium">{{ __('Created') }}:</span>
                            <span class="text-info-600">{{ $employee->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-info-700 font-medium">{{ __('Last Updated') }}:</span>
                            <span class="text-info-600">{{ $employee->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200">
                    <x-ui.button 
                        type="button"
                        variant="outline-secondary"
                        onclick="window.history.back()">
                        {{ __('Cancel') }}
                    </x-ui.button>
                    <x-ui.button 
                        type="button"
                        variant="outline-warning"
                        onclick="resetForm()"
                        id="resetBtn">
                        <i class="fas fa-undo mr-2"></i>
                        {{ __('Reset Changes') }}
                    </x-ui.button>
                    <x-ui.button 
                        type="submit"
                        variant="primary"
                        :loading="submitting"
                        id="submitBtn">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('Update Employee') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function employeeEditForm() {
    return {
        submitting: false,
        originalData: {},
        form: {
            employee_code: '{{ old('employee_code', $employee->employee_code) }}',
            name: '{{ old('name', $employee->name) }}',
            position: '{{ old('position', $employee->position) }}',
            department: '{{ old('department', $employee->department) }}',
            hire_date: '{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}',
            salary: '{{ old('salary', $employee->salary) }}',
            email: '{{ old('email', $employee->email) }}',
            phone: '{{ old('phone', $employee->phone) }}',
            address: '{{ old('address', $employee->address) }}',
            birth_date: '{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}',
            status: '{{ old('status', $employee->status) }}',
            notes: '{{ old('notes', $employee->notes) }}'
        },
        
        init() {
            // Store original data for reset functionality
            this.originalData = { ...this.form };
        },
        
        handleSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }
            
            // Check if there are any changes
            if (!this.hasChanges()) {
                event.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: '{{ __("No Changes") }}',
                    text: '{{ __("No changes have been made to update.") }}'
                });
                return;
            }
            
            // Basic client-side validation
            if (!this.validateForm()) {
                event.preventDefault();
                return;
            }
            
            this.submitting = true;
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            
            // Form will submit normally
        },
        
        hasChanges() {
            return JSON.stringify(this.form) !== JSON.stringify(this.originalData);
        },
        
        validateForm() {
            let isValid = true;
            const errors = {};
            
            // Required fields validation
            if (!this.form.employee_code.trim()) {
                errors.employee_code = '{{ __("Employee code is required") }}';
                isValid = false;
            }
            
            if (!this.form.name.trim()) {
                errors.name = '{{ __("Name is required") }}';
                isValid = false;
            }
            
            if (!this.form.position.trim()) {
                errors.position = '{{ __("Position is required") }}';
                isValid = false;
            }
            
            if (!this.form.department) {
                errors.department = '{{ __("Department is required") }}';
                isValid = false;
            }
            
            if (!this.form.hire_date) {
                errors.hire_date = '{{ __("Hire date is required") }}';
                isValid = false;
            }
            
            if (!this.form.email.trim()) {
                errors.email = '{{ __("Email is required") }}';
                isValid = false;
            } else if (!this.isValidEmail(this.form.email)) {
                errors.email = '{{ __("Please enter a valid email address") }}';
                isValid = false;
            }
            
            // Display errors
            this.displayErrors(errors);
            
            return isValid;
        },
        
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        
        displayErrors(errors) {
            // Clear previous errors
            document.querySelectorAll('.text-danger-600').forEach(el => {
                if (el.textContent.includes('required') || el.textContent.includes('valid')) {
                    el.remove();
                }
            });
            
            // Display new errors
            Object.keys(errors).forEach(field => {
                const input = document.getElementById(field);
                if (input) {
                    input.classList.add('border-danger-500');
                    const errorDiv = document.createElement('p');
                    errorDiv.className = 'text-sm text-danger-600 mt-1';
                    errorDiv.textContent = errors[field];
                    input.parentNode.appendChild(errorDiv);
                }
            });
        }
    }
}

function resetForm() {
    Swal.fire({
        title: '{{ __("Reset Changes") }}',
        text: '{{ __("Are you sure you want to reset all changes to original values?") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __("Yes, Reset") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset Alpine.js data to original values
            const component = Alpine.$data(document.getElementById('employeeEditForm'));
            if (component) {
                component.form = { ...component.originalData };
            }
            
            // Reset form fields
            Object.keys(component.originalData).forEach(key => {
                const field = document.getElementById(key);
                if (field) {
                    field.value = component.originalData[key] || '';
                }
            });
            
            // Clear validation errors
            document.querySelectorAll('.border-danger-500').forEach(el => {
                el.classList.remove('border-danger-500');
            });
            document.querySelectorAll('.text-danger-600').forEach(el => {
                if (el.textContent.includes('required') || el.textContent.includes('valid')) {
                    el.remove();
                }
            });
            
            Swal.fire({
                icon: 'success',
                title: '{{ __("Changes Reset") }}',
                text: '{{ __("All changes have been reset to original values") }}',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Real-time email validation
document.getElementById('email').addEventListener('input', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        this.classList.add('border-warning-500');
    } else {
        this.classList.remove('border-warning-500', 'border-danger-500');
    }
});

// Highlight changed fields
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employeeEditForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const component = Alpine.$data(form);
            if (component && component.originalData[this.name] !== this.value) {
                this.classList.add('border-warning-300', 'bg-warning-25');
            } else {
                this.classList.remove('border-warning-300', 'bg-warning-25');
            }
        });
    });
});
</script>
@endpush