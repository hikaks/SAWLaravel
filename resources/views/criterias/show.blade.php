@extends('layouts.main')

@section('title', __('Criteria Details') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Criteria Details'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Criteria Details') }}</h1>
        <p class="text-gray-600">{{ __('Complete information about evaluation criteria') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('criterias.edit', $criteria->id) }}" variant="warning" icon="fas fa-edit">{{ __('Edit Criteria') }}</x-ui.button>
        <x-ui.button href="{{ route('criterias.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<!-- Criteria Profile Card -->
<div class="card mb-6 bg-gradient-to-br from-primary-500 to-primary-600 text-white overflow-hidden">
    <div class="card-body py-8">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="w-24 h-24 bg-white/25 rounded-full flex items-center justify-center text-3xl font-bold text-white">
                {{ strtoupper(substr($criteria->code, 0, 2)) }}
            </div>
            <div class="text-center md:text-left flex-1">
                <h1 class="text-3xl font-bold mb-2">{{ $criteria->name }}</h1>
                <p class="text-xl mb-2 text-white/75">{{ __('Code') }}: {{ $criteria->code }}</p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-white/90">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-{{ $criteria->type == 'benefit' ? 'arrow-up' : 'arrow-down' }}"></i>
                        <span>{{ $criteria->type == 'benefit' ? __('Benefit Type') : __('Cost Type') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-weight-hanging"></i>
                        <span>{{ $criteria->weight }}% {{ __('Weight') }}</span>
                    </div>
                    @if($criteria->unit)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-ruler"></i>
                        <span>{{ $criteria->unit }}</span>
                    </div>
                    @endif
                </div>
                <div class="mt-4 p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-white/90">{{ __('Weight Contribution') }}</span>
                        <span class="text-xl font-bold">{{ $criteria->weight }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2 mt-2">
                        <div class="bg-white h-2 rounded-full transition-all duration-300" style="width: {{ $criteria->weight }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Criteria Information -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-primary-500"></i>{{ __('Basic Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Criteria Name') }}</label>
                            <p class="text-gray-900 font-medium">{{ $criteria->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Criteria Code') }}</label>
                            <p class="font-mono bg-gray-100 px-2 py-1 rounded text-gray-900 inline-block">{{ $criteria->code }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Type') }}</label>
                            @if($criteria->type == 'benefit')
                                <span class="badge badge-success"><i class="fas fa-arrow-up mr-1"></i>{{ __('Benefit (Higher is Better)') }}</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-arrow-down mr-1"></i>{{ __('Cost (Lower is Better)') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Weight') }}</label>
                            <p class="text-gray-900 font-bold text-lg">{{ $criteria->weight }}%</p>
                        </div>
                        @if($criteria->unit)
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Unit') }}</label>
                            <p class="text-gray-900">{{ $criteria->unit }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Status') }}</label>
                            @if($criteria->status === 'active')
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($criteria->description)
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-align-left text-primary-500"></i>{{ __('Description') }}
                </h6>
            </div>
            <div class="card-body">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $criteria->description }}</p>
            </div>
        </div>
        @endif

        <!-- Usage Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-chart-bar text-primary-500"></i>{{ __('Usage Statistics') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-primary-600">{{ $criteria->evaluations_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">{{ __('Total Evaluations') }}</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-success-600">{{ $criteria->active_evaluations_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">{{ __('Active Evaluations') }}</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-info-600">{{ round($criteria->average_score ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-600">{{ __('Average Score') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-bolt text-warning-500"></i>{{ __('Quick Actions') }}
                </h6>
            </div>
            <div class="card-body space-y-3">
                <x-ui.button href="{{ route('criterias.edit', $criteria->id) }}" variant="outline-warning" size="sm" icon="fas fa-edit" class="w-full justify-start">
                    {{ __('Edit Information') }}
                </x-ui.button>
                <x-ui.button href="{{ route('evaluations.create', ['criteria' => $criteria->id]) }}" variant="outline-primary" size="sm" icon="fas fa-plus" class="w-full justify-start">
                    {{ __('Create Evaluation') }}
                </x-ui.button>
                <div class="pt-2 border-t border-gray-200">
                    <x-ui.button onclick="confirmDelete()" variant="outline-danger" size="sm" icon="fas fa-trash" class="w-full justify-start">
                        {{ __('Delete Criteria') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Weight Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-balance-scale text-info-500"></i>{{ __('Weight Information') }}
                </h6>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Current Weight') }}</span>
                    <span class="font-semibold text-gray-900">{{ $criteria->weight }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Total System Weight') }}</span>
                    <span class="font-semibold text-gray-900">{{ $totalWeight ?? 0 }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Remaining Weight') }}</span>
                    <span class="font-semibold {{ (100 - ($totalWeight ?? 0)) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ 100 - ($totalWeight ?? 0) }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-info text-gray-500"></i>{{ __('System Information') }}
                </h6>
            </div>
            <div class="card-body space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Created') }}</span>
                    <span class="text-gray-900">{{ $criteria->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Last Updated') }}</span>
                    <span class="text-gray-900">{{ $criteria->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('ID') }}</span>
                    <span class="text-gray-900 font-mono">#{{ $criteria->id }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-data="{ showDeleteModal: false }" x-show="showDeleteModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content modal-confirm modal-danger">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Criteria') }}</h5>
                <button @click="showDeleteModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Are you sure?') }}</h4>
                <p class="text-gray-600 mb-4">
                    {{ __('This will permanently delete') }} <strong>{{ $criteria->name }}</strong> {{ __('and all associated evaluation data. This action cannot be undone.') }}
                </p>
            </div>
            <div class="modal-footer">
                <button @click="showDeleteModal = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                <form action="{{ route('criterias.destroy', $criteria->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>{{ __('Delete Criteria') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    Alpine.store('modals', { showDeleteModal: true });
}

document.addEventListener('alpine:init', () => {
    Alpine.store('modals', { showDeleteModal: false });
});
</script>
@endpush