@extends('layouts.main')

@section('title', __('Employee Details') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Employee Details'))

@section('content')
@php
    $latestResult = $employee->latestResult();
@endphp

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Employee Profile') }}</h1>
        <p class="text-gray-600">{{ __('Complete information and performance overview') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            href="{{ route('employees.edit', $employee->id) }}" 
            variant="warning" 
            icon="fas fa-edit">
            {{ __('Edit Employee') }}
        </x-ui.button>
        <x-ui.button 
            href="{{ route('employees.index') }}" 
            variant="outline-secondary" 
            icon="fas fa-arrow-left">
            {{ __('Back to List') }}
        </x-ui.button>
    </div>
</div>

<!-- Employee Profile Card -->
<div class="card mb-6 bg-gradient-to-br from-primary-500 to-primary-600 text-white overflow-hidden">
    <div class="card-body py-8">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="relative">
                <div class="w-24 h-24 bg-white/25 rounded-full flex items-center justify-center text-3xl font-bold text-white">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                @if($employee->status === 'active')
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-success-500 border-2 border-white rounded-full"></span>
                @else
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-gray-400 border-2 border-white rounded-full"></span>
                @endif
            </div>
            <div class="text-center md:text-left flex-1">
                <h1 class="text-3xl font-bold mb-2">{{ $employee->name }}</h1>
                <p class="text-xl mb-2 text-white/75">{{ $employee->position }}</p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-white/90">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-building"></i>
                        <span>{{ $employee->department }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-hashtag"></i>
                        <span>{{ $employee->employee_code }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar"></i>
                        <span>{{ __('Since') }} {{ $employee->hire_date?->format('M Y') }}</span>
                    </div>
                </div>
                @if($latestResult)
                    <div class="mt-4 p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-white/90">{{ __('Latest Performance Score') }}</span>
                            <span class="text-xl font-bold">{{ round($latestResult->total_score * 100, 2) }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2 mt-2">
                            <div class="bg-white h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ round($latestResult->total_score * 100, 2) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Personal Information -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-user text-primary-500"></i>
                    {{ __('Basic Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Full Name') }}</label>
                            <p class="text-gray-900 font-medium">{{ $employee->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Employee Code') }}</label>
                            <p class="font-mono bg-gray-100 px-2 py-1 rounded text-gray-900 inline-block">{{ $employee->employee_code }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Position') }}</label>
                            <p class="text-gray-900 font-medium">{{ $employee->position }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Department') }}</label>
                            <span class="badge badge-primary">{{ $employee->department }}</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Hire Date') }}</label>
                            <p class="text-gray-900">{{ $employee->hire_date?->format('M d, Y') }}</p>
                        </div>
                        @if($employee->birth_date)
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</label>
                            <p class="text-gray-900">{{ $employee->birth_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                        @if($employee->salary)
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Salary') }}</label>
                            <p class="text-gray-900 font-medium">${{ number_format($employee->salary, 2) }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Status') }}</label>
                            @if($employee->status === 'active')
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-address-book text-primary-500"></i>
                    {{ __('Contact Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Email Address') }}</label>
                        <p class="text-gray-900">
                            <a href="mailto:{{ $employee->email }}" class="text-primary-600 hover:text-primary-700">
                                {{ $employee->email }}
                            </a>
                        </p>
                    </div>
                    @if($employee->phone)
                    <div>
                        <label class="text-sm font-medium text-gray-500">{{ __('Phone Number') }}</label>
                        <p class="text-gray-900">
                            <a href="tel:{{ $employee->phone }}" class="text-primary-600 hover:text-primary-700">
                                {{ $employee->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($employee->address)
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500">{{ __('Address') }}</label>
                        <p class="text-gray-900">{{ $employee->address }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance History -->
        @if($employee->evaluationResults->count() > 0)
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                        <i class="fas fa-chart-line text-primary-500"></i>
                        {{ __('Performance History') }}
                    </h6>
                    <x-ui.button 
                        href="{{ route('results.details', ['employee' => $employee->id, 'period' => 'all']) }}" 
                        variant="outline-primary" 
                        size="sm">
                        {{ __('View All') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    @foreach($employee->evaluationResults->take(5) as $result)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">{{ $result->evaluation_period }}</div>
                            <div class="text-sm text-gray-500">{{ $result->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">{{ round($result->total_score * 100, 2) }}%</div>
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ round($result->total_score * 100, 2) }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($employee->notes)
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-sticky-note text-primary-500"></i>
                    {{ __('Notes') }}
                </h6>
            </div>
            <div class="card-body">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $employee->notes }}</p>
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
                    <i class="fas fa-bolt text-warning-500"></i>
                    {{ __('Quick Actions') }}
                </h6>
            </div>
            <div class="card-body space-y-3">
                <x-ui.button 
                    href="{{ route('evaluations.create', ['employee' => $employee->id]) }}" 
                    variant="primary" 
                    size="sm"
                    icon="fas fa-plus"
                    class="w-full justify-start">
                    {{ __('New Evaluation') }}
                </x-ui.button>
                
                <x-ui.button 
                    href="{{ route('employees.edit', $employee->id) }}" 
                    variant="outline-warning" 
                    size="sm"
                    icon="fas fa-edit"
                    class="w-full justify-start">
                    {{ __('Edit Information') }}
                </x-ui.button>
                
                @if($latestResult)
                <x-ui.button 
                    href="{{ route('results.export-employee', $employee->id) }}" 
                    variant="outline-success" 
                    size="sm"
                    icon="fas fa-download"
                    class="w-full justify-start">
                    {{ __('Export Report') }}
                </x-ui.button>
                @endif
                
                <div class="pt-2 border-t border-gray-200">
                    <x-ui.button 
                        onclick="confirmDelete()" 
                        variant="outline-danger" 
                        size="sm"
                        icon="fas fa-trash"
                        class="w-full justify-start">
                        {{ __('Delete Employee') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-info-500"></i>
                    {{ __('Statistics') }}
                </h6>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Total Evaluations') }}</span>
                    <span class="font-semibold text-gray-900">{{ $employee->evaluationResults->count() }}</span>
                </div>
                
                @if($employee->evaluationResults->count() > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Average Score') }}</span>
                    <span class="font-semibold text-gray-900">{{ round($employee->evaluationResults->avg('total_score') * 100, 2) }}%</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Best Score') }}</span>
                    <span class="font-semibold text-success-600">{{ round($employee->evaluationResults->max('total_score') * 100, 2) }}%</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Last Evaluation') }}</span>
                    <span class="text-gray-900">{{ $employee->evaluationResults->first()?->created_at->diffForHumans() }}</span>
                </div>
                @endif
                
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Years of Service') }}</span>
                    <span class="font-semibold text-gray-900">{{ $employee->hire_date?->diffInYears(now()) ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if($employee->evaluationResults->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-clock text-gray-500"></i>
                    {{ __('Recent Activity') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    @foreach($employee->evaluationResults->take(3) as $result)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 bg-primary-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 font-medium">{{ __('Evaluation completed') }}</p>
                            <p class="text-xs text-gray-500">{{ $result->evaluation_period }} • {{ $result->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-sm font-medium text-primary-600">{{ round($result->total_score * 100, 2) }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-data="{ showDeleteModal: false }" x-show="showDeleteModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content modal-confirm modal-danger">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Employee') }}</h5>
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
                    {{ __('This will permanently delete') }} <strong>{{ $employee->name }}</strong> {{ __('and all associated evaluation data. This action cannot be undone.') }}
                </p>
                <div class="bg-danger-50 border border-danger-200 rounded-lg p-3">
                    <p class="text-sm text-danger-700">
                        <strong>{{ __('Warning') }}:</strong> {{ __('All evaluation results and performance data will be lost.') }}
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="showDeleteModal = false" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('Delete Employee') }}
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

// Initialize Alpine.js stores
document.addEventListener('alpine:init', () => {
    Alpine.store('modals', {
        showDeleteModal: false
    });
});
</script>
@endpush