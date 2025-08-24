@extends('layouts.main')

@section('title', __('Edit Criteria') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Edit Criteria'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Edit Criteria') }}</h1>
        <p class="text-gray-600">{{ __('Update criteria information') }}: <span class="font-semibold">{{ $criteria->name }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('criterias.show', $criteria->id) }}" variant="outline-info" icon="fas fa-eye">{{ __('View Details') }}</x-ui.button>
        <x-ui.button href="{{ route('criterias.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-warning-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">{{ __('Update Criteria Information') }}</h5>
                        <p class="text-sm text-gray-500">{{ __('Modify the fields you want to update') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">{{ __('Criteria Code') }}</p>
                    <span class="badge badge-primary text-base">{{ $criteria->code }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('criterias.update', $criteria->id) }}" method="POST" id="criteriaEditForm">
                @csrf
                @method('PUT')
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-info-circle"></i>{{ __('Basic Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Criteria Name') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('name') border-danger-500 @enderror" id="name" name="name" value="{{ old('name', $criteria->name) }}" required>
                            @error('name')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="code" class="form-label">{{ __('Criteria Code') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('code') border-danger-500 @enderror" id="code" name="code" value="{{ old('code', $criteria->code) }}" required>
                            @error('code')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label">{{ __('Type') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('type') border-danger-500 @enderror" id="type" name="type" required>
                                <option value="">{{ __('Select Type') }}</option>
                                <option value="benefit" {{ old('type', $criteria->type) == 'benefit' ? 'selected' : '' }}>{{ __('Benefit (Higher is Better)') }}</option>
                                <option value="cost" {{ old('type', $criteria->type) == 'cost' ? 'selected' : '' }}>{{ __('Cost (Lower is Better)') }}</option>
                            </select>
                            @error('type')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="weight" class="form-label">{{ __('Weight') }} <span class="text-danger-500">*</span></label>
                            <div class="relative">
                                <input type="number" class="form-control pr-12 @error('weight') border-danger-500 @enderror" id="weight" name="weight" value="{{ old('weight', $criteria->weight) }}" min="0" max="100" step="0.01" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            @error('weight')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="unit" class="form-label">{{ __('Unit') }}</label>
                            <input type="text" class="form-control @error('unit') border-danger-500 @enderror" id="unit" name="unit" value="{{ old('unit', $criteria->unit) }}" placeholder="{{ __('e.g., points, years, %') }}">
                            @error('unit')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('status') border-danger-500 @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $criteria->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('status', $criteria->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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
                        <textarea class="form-textarea @error('description') border-danger-500 @enderror" id="description" name="description" rows="4" placeholder="{{ __('Describe what this criteria evaluates...') }}">{{ old('description', $criteria->description) }}</textarea>
                        @error('description')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-8 p-4 bg-info-50 border border-info-200 rounded-lg">
                    <h6 class="flex items-center gap-2 text-info-800 font-semibold mb-3">
                        <i class="fas fa-history"></i>{{ __('Change History') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-info-700 font-medium">{{ __('Created') }}:</span>
                            <span class="text-info-600">{{ $criteria->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-info-700 font-medium">{{ __('Last Updated') }}:</span>
                            <span class="text-info-600">{{ $criteria->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200">
                    <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="button" variant="outline-warning" onclick="resetForm()">{{ __('Reset Changes') }}</x-ui.button>
                    <x-ui.button type="submit" variant="primary" id="submitBtn"><i class="fas fa-save mr-2"></i>{{ __('Update Criteria') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetForm() {
    if (confirm('{{ __("Are you sure you want to reset all changes?") }}')) {
        document.getElementById('criteriaEditForm').reset();
    }
}
</script>
@endpush