@extends('layouts.main')

@section('title', __('Add Employee') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Add Employee'))

@section('content')
<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-gray-600">{{ __('Create a new employee record with complete information') }}</p>
    </div>
    <x-ui.button 
        href="{{ route('employees.index') }}" 
        variant="outline-secondary" 
        icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">{{ __('Employee Information') }}</h5>
                    <p class="text-sm text-gray-500">{{ __('Fill in all required fields marked with *') }}</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('employees.store') }}" method="POST" id="employeeForm" x-data="employeeForm()" @submit="handleSubmit">
                @csrf

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
                                       value="{{ old('employee_code') }}"
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
                                       value="{{ old('name') }}"
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
                                       value="{{ old('position') }}"
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
                                    <option value="IT" {{ old('department') == 'IT' ? 'selected' : '' }}>{{ __('Information Technology') }}</option>
                                    <option value="HR" {{ old('department') == 'HR' ? 'selected' : '' }}>{{ __('Human Resources') }}</option>
                                    <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>{{ __('Finance') }}</option>
                                    <option value="Marketing" {{ old('department') == 'Marketing' ? 'selected' : '' }}>{{ __('Marketing') }}</option>
                                    <option value="Operations" {{ old('department') == 'Operations' ? 'selected' : '' }}>{{ __('Operations') }}</option>
                                    <option value="Sales" {{ old('department') == 'Sales' ? 'selected' : '' }}>{{ __('Sales') }}</option>
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
                                       value="{{ old('hire_date') }}"
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
                                       value="{{ old('salary') }}"
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
                                       value="{{ old('email') }}"
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
                                       value="{{ old('phone') }}"
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
                                          x-model="form.address">{{ old('address') }}</textarea>
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
                                       value="{{ old('birth_date') }}"
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
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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
                                          x-model="form.notes">{{ old('notes') }}</textarea>
                            </div>
                            @error('notes')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
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
                        variant="outline-primary"
                        onclick="resetForm()"
                        id="resetBtn">
                        <i class="fas fa-undo mr-2"></i>
                        {{ __('Reset Form') }}
                    </x-ui.button>
                    <x-ui.button 
                        type="submit"
                        variant="primary"
                        :loading="submitting"
                        id="submitBtn">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('Save Employee') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function employeeForm() {
    return {
        submitting: false,
        form: {
            employee_code: '',
            name: '',
            position: '',
            department: '',
            hire_date: '',
            salary: '',
            email: '',
            phone: '',
            address: '',
            birth_date: '',
            status: 'active',
            notes: ''
        },
        
        init() {
            // Initialize form with old values if any
            @if(old())
                this.form = {
                    employee_code: '{{ old('employee_code') }}',
                    name: '{{ old('name') }}',
                    position: '{{ old('position') }}',
                    department: '{{ old('department') }}',
                    hire_date: '{{ old('hire_date') }}',
                    salary: '{{ old('salary') }}',
                    email: '{{ old('email') }}',
                    phone: '{{ old('phone') }}',
                    address: '{{ old('address') }}',
                    birth_date: '{{ old('birth_date') }}',
                    status: '{{ old('status', 'active') }}',
                    notes: '{{ old('notes') }}'
                };
            @endif
        },
        
        handleSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
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
        title: '{{ __("Reset Form") }}',
        text: '{{ __("Are you sure you want to reset all form data?") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __("Yes, Reset") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('employeeForm').reset();
            
            // Clear Alpine.js data
            const component = Alpine.$data(document.getElementById('employeeForm'));
            if (component) {
                component.form = {
                    employee_code: '',
                    name: '',
                    position: '',
                    department: '',
                    hire_date: '',
                    salary: '',
                    email: '',
                    phone: '',
                    address: '',
                    birth_date: '',
                    status: 'active',
                    notes: ''
                };
            }
            
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
                title: '{{ __("Form Reset") }}',
                text: '{{ __("Form has been reset successfully") }}',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Auto-generate employee code based on department
document.getElementById('department').addEventListener('change', function() {
    const department = this.value;
    const employeeCodeInput = document.getElementById('employee_code');
    
    if (department && !employeeCodeInput.value) {
        // Generate code based on department
        const departmentCodes = {
            'IT': 'IT',
            'HR': 'HR', 
            'Finance': 'FN',
            'Marketing': 'MK',
            'Operations': 'OP',
            'Sales': 'SL'
        };
        
        const code = departmentCodes[department] || 'EMP';
        const timestamp = Date.now().toString().slice(-4);
        employeeCodeInput.value = `${code}${timestamp}`;
        
        // Update Alpine.js model
        const component = Alpine.$data(document.getElementById('employeeForm'));
        if (component) {
            component.form.employee_code = employeeCodeInput.value;
        }
    }
});

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
</script>
@endpush