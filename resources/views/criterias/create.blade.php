@extends('layouts.main')

@section('title', __('Add Criteria') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Add Criteria'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-gray-600">{{ __('Create a new evaluation criteria for SAW method') }}</p>
    </div>
    <x-ui.button href="{{ route('criterias.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-sliders text-white text-lg"></i>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">{{ __('Criteria Information') }}</h5>
                    <p class="text-sm text-gray-500">{{ __('Fill in all required fields marked with *') }}</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('criterias.store') }}" method="POST" id="criteriaForm" x-data="criteriaForm()">
                @csrf
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-info-circle"></i>{{ __('Basic Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Criteria Name') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('name') border-danger-500 @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="code" class="form-label">{{ __('Criteria Code') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('code') border-danger-500 @enderror" id="code" name="code" value="{{ old('code') }}" required>
                            @error('code')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label">{{ __('Type') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('type') border-danger-500 @enderror" id="type" name="type" required>
                                <option value="">{{ __('Select Type') }}</option>
                                <option value="benefit" {{ old('type') == 'benefit' ? 'selected' : '' }}>{{ __('Benefit (Higher is Better)') }}</option>
                                <option value="cost" {{ old('type') == 'cost' ? 'selected' : '' }}>{{ __('Cost (Lower is Better)') }}</option>
                            </select>
                            @error('type')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="weight" class="form-label">{{ __('Weight') }} <span class="text-danger-500">*</span></label>
                            <div class="relative">
                                <input type="number" class="form-control pr-12 @error('weight') border-danger-500 @enderror" id="weight" name="weight" value="{{ old('weight') }}" min="0" max="100" step="0.01" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            @error('weight')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="unit" class="form-label">{{ __('Unit') }}</label>
                            <input type="text" class="form-control @error('unit') border-danger-500 @enderror" id="unit" name="unit" value="{{ old('unit') }}" placeholder="{{ __('e.g., points, years, %') }}">
                            @error('unit')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('status') border-danger-500 @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-align-left"></i>{{ __('Description') }}
                    </h6>
                    <div class="form-group">
                        <label for="description" class="form-label">{{ __('Detailed Description') }}</label>
                        <textarea class="form-textarea @error('description') border-danger-500 @enderror" id="description" name="description" rows="4" placeholder="{{ __('Describe what this criteria evaluates...') }}">{{ old('description') }}</textarea>
                        @error('description')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200">
                    <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="button" variant="outline-primary" onclick="resetForm()">{{ __('Reset Form') }}</x-ui.button>
                    <x-ui.button type="submit" variant="primary" id="submitBtn"><i class="fas fa-save mr-2"></i>{{ __('Save Criteria') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function criteriaForm() {
    return {
        init() {
            this.generateCode();
        },
        generateCode() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');
            
            nameInput.addEventListener('input', () => {
                if (!codeInput.value) {
                    const code = nameInput.value
                        .replace(/[^a-zA-Z0-9\s]/g, '')
                        .replace(/\s+/g, '_')
                        .toUpperCase()
                        .substring(0, 10);
                    codeInput.value = code;
                }
            });
        }
    }
}

function resetForm() {
    if (confirm('{{ __("Are you sure you want to reset the form?") }}')) {
        document.getElementById('criteriaForm').reset();
    }
}
</script>
@endpush