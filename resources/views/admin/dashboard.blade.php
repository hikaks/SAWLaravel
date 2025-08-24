@extends('layouts.main')

@section('title', __('Admin Dashboard') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('System Administration'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Admin Dashboard') }}</h1>
        <p class="text-gray-600">{{ __('System overview and administrative controls') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('admin.health') }}" variant="outline-info" icon="fas fa-heartbeat">{{ __('System Health') }}</x-ui.button>
        <x-ui.button href="{{ route('admin.cache.index') }}" variant="outline-warning" icon="fas fa-database">{{ __('Cache Management') }}</x-ui.button>
    </div>
</div>

<!-- System Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="stats-card bg-gradient-to-br from-green-500 to-green-600">
        <div class="stats-content">
            <div class="stats-number">{{ $systemStats['uptime'] ?? '99.9' }}%</div>
            <div class="stats-label">{{ __('System Uptime') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-server"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-blue-500 to-blue-600">
        <div class="stats-content">
            <div class="stats-number">{{ $systemStats['total_users'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Total Users') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-users"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600">
        <div class="stats-content">
            <div class="stats-number">{{ $systemStats['active_sessions'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Active Sessions') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-user-clock"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-red-500 to-red-600">
        <div class="stats-content">
            <div class="stats-number">{{ $systemStats['failed_jobs'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Failed Jobs') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-exclamation-triangle"></i></div>
    </div>
</div>

<!-- Admin Tools Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Cache Management -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-database text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Cache Management') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Manage application cache and optimize performance') }}</p>
            <x-ui.button href="{{ route('admin.cache.index') }}" variant="outline-primary" size="sm" class="w-full">
                {{ __('Manage Cache') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Job Monitoring -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tasks text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Job Monitoring') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Monitor background jobs and queue status') }}</p>
            <x-ui.button href="{{ route('admin.jobs.index') }}" variant="outline-success" size="sm" class="w-full">
                {{ __('View Jobs') }}
            </x-ui.button>
        </div>
    </div>

    <!-- System Health -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-heartbeat text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('System Health') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Check system health and performance metrics') }}</p>
            <x-ui.button href="{{ route('admin.health') }}" variant="outline-danger" size="sm" class="w-full">
                {{ __('Health Check') }}
            </x-ui.button>
        </div>
    </div>

    <!-- User Management -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-cog text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('User Management') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Manage system users and permissions') }}</p>
            <x-ui.button href="{{ route('users.index') }}" variant="outline-secondary" size="sm" class="w-full">
                {{ __('Manage Users') }}
            </x-ui.button>
        </div>
    </div>

    <!-- System Information -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-info-circle text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('System Information') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('View detailed system and server information') }}</p>
            <x-ui.button href="{{ route('admin.system-info') }}" variant="outline-warning" size="sm" class="w-full">
                {{ __('View Info') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bolt text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Quick Actions') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Perform common administrative tasks') }}</p>
            <div class="space-y-2">
                <x-ui.button onclick="clearCache()" variant="outline-primary" size="sm" class="w-full">
                    {{ __('Clear Cache') }}
                </x-ui.button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- System Logs -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-file-alt text-primary-500"></i>{{ __('Recent System Activity') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="space-y-3" id="systemActivity">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    <span class="text-sm text-gray-800">{{ __('Loading system activity...') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-tachometer-alt text-success-500"></i>{{ __('Performance Metrics') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Response Time') }}</span>
                    <span class="font-semibold text-success-600">{{ $performanceMetrics['response_time'] ?? '< 100ms' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Memory Usage') }}</span>
                    <span class="font-semibold text-warning-600">{{ $performanceMetrics['memory_usage'] ?? '45%' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('CPU Usage') }}</span>
                    <span class="font-semibold text-info-600">{{ $performanceMetrics['cpu_usage'] ?? '12%' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Disk Usage') }}</span>
                    <span class="font-semibold text-gray-600">{{ $performanceMetrics['disk_usage'] ?? '67%' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function clearCache() {
    Swal.fire({
        title: '{{ __("Clear Cache") }}',
        text: '{{ __("Are you sure you want to clear all application cache?") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __("Yes, Clear Cache") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("admin.cache.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? '{{ __("Success") }}' : '{{ __("Error") }}',
                    text: data.message
                });
            });
        }
    });
}
</script>
@endpush