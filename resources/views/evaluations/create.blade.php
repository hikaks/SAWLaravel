@extends('layouts.main')

@section('title', __('Create Evaluation') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Create Evaluation'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Create New Evaluation') }}</h1>
        <p class="text-gray-600">{{ __('Evaluate employee performance based on defined criteria') }}</p>
    </div>
    <x-ui.button href="{{ route('evaluations.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="max-w-4xl mx-auto">
    <form action="{{ route('evaluations.store') }}" method="POST" x-data="evaluationForm()" @submit="handleSubmit">
        @csrf
        
        <!-- Employee Selection -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-user text-primary-500"></i>{{ __('Employee Selection') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="employee_id" class="form-label">{{ __('Employee') }} <span class="text-danger-500">*</span></label>
                        <select class="form-select @error('employee_id') border-danger-500 @enderror" id="employee_id" name="employee_id" required>
                            <option value="">{{ __('Select Employee') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->employee_code }}) - {{ $employee->department }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="evaluation_period" class="form-label">{{ __('Evaluation Period') }} <span class="text-danger-500">*</span></label>
                        <input type="text" class="form-control @error('evaluation_period') border-danger-500 @enderror" 
                               id="evaluation_period" name="evaluation_period" value="{{ old('evaluation_period') }}" 
                               placeholder="{{ __('e.g., 2024-Q1, January 2024') }}" required>
                        @error('evaluation_period')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Criteria Evaluation -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-sliders text-primary-500"></i>{{ __('Criteria Evaluation') }}
                </h6>
            </div>
            <div class="card-body">
                @if($criterias->count() > 0)
                    <div class="space-y-6">
                        @foreach($criterias as $criteria)
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h6 class="font-semibold text-gray-900">{{ $criteria->name }}</h6>
                                    <p class="text-sm text-gray-600">{{ $criteria->description }}</p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="badge {{ $criteria->type === 'benefit' ? 'badge-success' : 'badge-warning' }}">
                                            <i class="fas fa-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                            {{ $criteria->type === 'benefit' ? __('Benefit') : __('Cost') }}
                                        </span>
                                        <span class="text-sm text-gray-600">{{ __('Weight') }}: <span class="font-semibold">{{ $criteria->weight }}%</span></span>
                                        @if($criteria->unit)
                                            <span class="text-sm text-gray-600">{{ __('Unit') }}: {{ $criteria->unit }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="score_{{ $criteria->id }}" class="form-label">
                                    {{ __('Score') }} <span class="text-danger-500">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('scores.'.$criteria->id) border-danger-500 @enderror" 
                                       id="score_{{ $criteria->id }}" 
                                       name="scores[{{ $criteria->id }}]" 
                                       value="{{ old('scores.'.$criteria->id) }}"
                                       min="0" 
                                       step="0.01" 
                                       required
                                       placeholder="{{ __('Enter score value') }}">
                                @error('scores.'.$criteria->id)
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                                
                                <div class="form-group mt-3">
                                    <label for="notes_{{ $criteria->id }}" class="form-label">{{ __('Notes') }}</label>
                                    <textarea class="form-textarea" 
                                              id="notes_{{ $criteria->id }}" 
                                              name="notes[{{ $criteria->id }}]" 
                                              rows="2" 
                                              placeholder="{{ __('Optional notes for this criteria...') }}">{{ old('notes.'.$criteria->id) }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-warning-500 mb-4"></i>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ __('No Criteria Available') }}</h4>
                        <p class="text-gray-600 mb-4">{{ __('Please create evaluation criteria first before creating evaluations.') }}</p>
                        <x-ui.button href="{{ route('criterias.create') }}" variant="primary" icon="fas fa-plus">
                            {{ __('Create Criteria') }}
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-sticky-note text-primary-500"></i>{{ __('Additional Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="overall_notes" class="form-label">{{ __('Overall Notes') }}</label>
                    <textarea class="form-textarea @error('overall_notes') border-danger-500 @enderror" 
                              id="overall_notes" 
                              name="overall_notes" 
                              rows="4" 
                              placeholder="{{ __('General comments about this evaluation...') }}">{{ old('overall_notes') }}</textarea>
                    @error('overall_notes')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        @if($criterias->count() > 0)
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="button" variant="outline-primary" onclick="resetForm()">
                {{ __('Reset Form') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="primary" :loading="submitting">
                <i class="fas fa-save mr-2"></i>{{ __('Save Evaluation') }}
            </x-ui.button>
        </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
function evaluationForm() {
    return {
        submitting: false,
        
        handleSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }
            
            if (!this.validateForm()) {
                event.preventDefault();
                return;
            }
            
            this.submitting = true;
        },
        
        validateForm() {
            let isValid = true;
            
            // Check if employee is selected
            const employeeSelect = document.getElementById('employee_id');
            if (!employeeSelect.value) {
                this.showError(employeeSelect, '{{ __("Please select an employee") }}');
                isValid = false;
            }
            
            // Check if period is filled
            const periodInput = document.getElementById('evaluation_period');
            if (!periodInput.value.trim()) {
                this.showError(periodInput, '{{ __("Please enter evaluation period") }}');
                isValid = false;
            }
            
            // Check all score inputs
            const scoreInputs = document.querySelectorAll('input[name^="scores["]');
            scoreInputs.forEach(input => {
                if (!input.value || parseFloat(input.value) < 0) {
                    this.showError(input, '{{ __("Please enter a valid score") }}');
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        showError(element, message) {
            element.classList.add('border-danger-500');
            
            // Remove existing error message
            const existingError = element.parentNode.querySelector('.text-danger-600');
            if (existingError) {
                existingError.remove();
            }
            
            // Add new error message
            const errorDiv = document.createElement('p');
            errorDiv.className = 'text-sm text-danger-600 mt-1';
            errorDiv.textContent = message;
            element.parentNode.appendChild(errorDiv);
        }
    }
}

function resetForm() {
    if (confirm('{{ __("Are you sure you want to reset the form?") }}')) {
        document.querySelector('form').reset();
        
        // Clear all error states
        document.querySelectorAll('.border-danger-500').forEach(el => {
            el.classList.remove('border-danger-500');
        });
        document.querySelectorAll('.text-danger-600').forEach(el => {
            if (el.textContent.includes('Please')) {
                el.remove();
            }
        });
    }
}
</script>
@endpush