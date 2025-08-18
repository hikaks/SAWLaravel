@extends('layouts.main')

@section('title', 'Evaluation Details - SAW Employee Evaluation')
@section('page-title', 'Evaluation Details')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Evaluation Details') }}</h1>
        <p class="text-gray-600">{{ __('Complete evaluation information and scoring details') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('evaluations.edit', $evaluation->id) }}" variant="warning" icon="fas fa-edit">{{ __('Edit Evaluation') }}</x-ui.button>
        <x-ui.button href="{{ route('evaluations.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Evaluation Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-clipboard-check text-primary-500"></i>{{ __('Evaluation Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Employee') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <i class="fas fa-user text-primary-500"></i>
                            <span class="text-lg font-medium text-gray-900">{{ $evaluation->employee->name }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $evaluation->employee->employee_code }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Criteria') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <i class="fas fa-list-check text-info-500"></i>
                            <span class="text-lg font-medium text-gray-900">{{ $evaluation->criteria->name }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-sm text-gray-500">{{ __('Weight') }}: {{ $evaluation->criteria->weight }}%</span>
                            @if($evaluation->criteria->type === 'benefit')
                                <span class="badge badge-success">{{ __('Benefit') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('Cost') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Score') }}</label>
                        <p class="text-2xl font-bold text-primary-600">{{ $evaluation->score }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Normalized Score') }}</label>
                        <p class="text-2xl font-bold text-success-600">{{ round($evaluation->normalized_score, 4) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Weighted Score') }}</label>
                        <p class="text-2xl font-bold text-warning-600">{{ round($evaluation->weighted_score, 4) }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="text-sm font-medium text-gray-500">{{ __('Evaluation Period') }}</label>
                    <p class="text-lg font-medium text-gray-900">{{ $evaluation->evaluation_period }}</p>
                </div>
            </div>
        </div>

        <!-- Score Details -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-calculator text-primary-500"></i>{{ __('Score Calculation') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h6 class="font-semibold text-gray-900 mb-3">{{ __('SAW Method Calculation') }}</h6>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-sm text-gray-500">{{ __('Raw Score') }}</div>
                                <div class="text-xl font-bold text-gray-900">{{ $evaluation->score }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">{{ __('Normalization') }}</div>
                                <div class="text-lg text-gray-700">÷ {{ $maxScore ?? 1 }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">{{ __('Weight Applied') }}</div>
                                <div class="text-lg text-gray-700">× {{ $evaluation->criteria->weight }}%</div>
                            </div>
                        </div>
                        <div class="text-center mt-4 pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-500">{{ __('Final Weighted Score') }}</div>
                            <div class="text-2xl font-bold text-primary-600">{{ round($evaluation->weighted_score, 4) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($evaluation->notes)
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-sticky-note text-primary-500"></i>{{ __('Notes') }}
                </h6>
            </div>
            <div class="card-body">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $evaluation->notes }}</p>
            </div>
        </div>
        @endif
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
                <x-ui.button href="{{ route('evaluations.edit', $evaluation->id) }}" variant="outline-warning" size="sm" icon="fas fa-edit" class="w-full justify-start">
                    {{ __('Edit Evaluation') }}
                </x-ui.button>
                <x-ui.button href="{{ route('results.details', ['employee' => $evaluation->employee->id, 'period' => $evaluation->evaluation_period]) }}" variant="outline-primary" size="sm" icon="fas fa-chart-bar" class="w-full justify-start">
                    {{ __('View Results') }}
                </x-ui.button>
                <x-ui.button onclick="exportEvaluation()" variant="outline-success" size="sm" icon="fas fa-download" class="w-full justify-start">
                    {{ __('Export Details') }}
                </x-ui.button>
                <div class="pt-2 border-t border-gray-200">
                    <x-ui.button onclick="confirmDelete()" variant="outline-danger" size="sm" icon="fas fa-trash" class="w-full justify-start">
                        {{ __('Delete Evaluation') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Evaluation Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-info-500"></i>{{ __('Statistics') }}
                </h6>
            </div>
            <div class="card-body space-y-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Criteria Weight') }}</span>
                    <span class="font-semibold text-gray-900">{{ $evaluation->criteria->weight }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Score Rank') }}</span>
                    <span class="font-semibold text-gray-900">#{{ $evaluation->rank ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Created') }}</span>
                    <span class="text-gray-900">{{ $evaluation->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Last Updated') }}</span>
                    <span class="text-gray-900">{{ $evaluation->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    Swal.fire({
        title: '{{ __("Delete Evaluation") }}',
        text: '{{ __("Are you sure you want to delete this evaluation? This action cannot be undone.") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __("Yes, Delete") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("evaluations.destroy", $evaluation->id) }}';
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function exportEvaluation() {
    window.open('{{ route("evaluations.export", $evaluation->id) }}', '_blank');
}
</script>
@endpush