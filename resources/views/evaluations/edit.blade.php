@extends('layouts.main')

@section('title', __('Edit Evaluation') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Edit Evaluation'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Edit Evaluation') }}</h1>
        <p class="text-gray-600">{{ __('Update evaluation for') }}: <span class="font-semibold">{{ $evaluation->employee->name }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('evaluations.show', $evaluation->id) }}" variant="outline-info" icon="fas fa-eye">{{ __('View Details') }}</x-ui.button>
        <x-ui.button href="{{ route('evaluations.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<div class="max-w-4xl mx-auto">
    <form action="{{ route('evaluations.update', $evaluation->id) }}" method="POST" x-data="evaluationEditForm()">
        @csrf
        @method('PUT')
        
        <!-- Employee Information -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-user text-primary-500"></i>{{ __('Employee Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="employee_id" class="form-label">{{ __('Employee') }} <span class="text-danger-500">*</span></label>
                        <select class="form-select @error('employee_id') border-danger-500 @enderror" id="employee_id" name="employee_id" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $evaluation->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->employee_code }}) - {{ $employee->department }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="evaluation_period" class="form-label">{{ __('Evaluation Period') }} <span class="text-danger-500">*</span></label>
                        <input type="text" class="form-control @error('evaluation_period') border-danger-500 @enderror" 
                               id="evaluation_period" name="evaluation_period" 
                               value="{{ old('evaluation_period', $evaluation->evaluation_period) }}" required>
                        @error('evaluation_period')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Criteria Scores -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-sliders text-primary-500"></i>{{ __('Criteria Scores') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="space-y-6">
                    @foreach($evaluation->scores as $score)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h6 class="font-semibold text-gray-900">{{ $score->criteria->name }}</h6>
                                <p class="text-sm text-gray-600">{{ $score->criteria->description }}</p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="badge {{ $score->criteria->type === 'benefit' ? 'badge-success' : 'badge-warning' }}">
                                        <i class="fas fa-{{ $score->criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ $score->criteria->type === 'benefit' ? __('Benefit') : __('Cost') }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ __('Weight') }}: <span class="font-semibold">{{ $score->criteria->weight }}%</span></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="score_{{ $score->criteria->id }}" class="form-label">
                                {{ __('Score') }} <span class="text-danger-500">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('scores.'.$score->criteria->id) border-danger-500 @enderror" 
                                   id="score_{{ $score->criteria->id }}" 
                                   name="scores[{{ $score->criteria->id }}]" 
                                   value="{{ old('scores.'.$score->criteria->id, $score->score) }}"
                                   min="0" step="0.01" required>
                            @error('scores.'.$score->criteria->id)
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                            
                            <div class="form-group mt-3">
                                <label for="notes_{{ $score->criteria->id }}" class="form-label">{{ __('Notes') }}</label>
                                <textarea class="form-textarea" 
                                          id="notes_{{ $score->criteria->id }}" 
                                          name="notes[{{ $score->criteria->id }}]" 
                                          rows="2">{{ old('notes.'.$score->criteria->id, $score->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
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
                              id="overall_notes" name="overall_notes" rows="4">{{ old('overall_notes', $evaluation->overall_notes) }}</textarea>
                    @error('overall_notes')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Change History -->
        <div class="card mb-6">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-history text-info-500"></i>{{ __('Change History') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 font-medium">{{ __('Created') }}:</span>
                        <span class="text-gray-900">{{ $evaluation->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium">{{ __('Last Updated') }}:</span>
                        <span class="text-gray-900">{{ $evaluation->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="button" variant="outline-warning" onclick="resetForm()">
                {{ __('Reset Changes') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                <i class="fas fa-save mr-2"></i>{{ __('Update Evaluation') }}
            </x-ui.button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function evaluationEditForm() {
    return {
        originalData: {},
        
        init() {
            // Store original form data
            this.originalData = this.getFormData();
        },
        
        getFormData() {
            const formData = {};
            const form = document.querySelector('form');
            const formDataObj = new FormData(form);
            for (let [key, value] of formDataObj.entries()) {
                formData[key] = value;
            }
            return formData;
        }
    }
}

function resetForm() {
    if (confirm('{{ __("Are you sure you want to reset all changes?") }}')) {
        location.reload();
    }
}
</script>
@endpush