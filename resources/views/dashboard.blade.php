@extends('layouts.main')

@section('title', __('Dashboard') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Dashboard Overview'))

@section('content')
<!-- Modern Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <!-- Total Employees -->
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_employees'] }}</div>
            <div class="stats-label">{{ __('Total Employees') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-user-group"></i>
        </div>
    </div>

    <!-- Evaluation Criteria -->
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_criterias'] }}</div>
            <div class="stats-label">{{ __('Evaluation Criteria') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-sliders"></i>
        </div>
    </div>

    <!-- Completed Evaluations -->
    <div class="stats-card bg-gradient-to-br from-info-500 to-info-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_evaluations'] }}</div>
            <div class="stats-label">{{ __('Completed Evaluations') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>
    </div>

    <!-- System Ready -->
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_weight'] ?? 100 }}%</div>
            <div class="stats-label">{{ __('System Ready') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Top 10 Performers Card -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                        <i class="fas fa-trophy text-warning-500"></i>
                        {{ __('Top 10 Performers') }}
                    </h6>
                    <x-ui.button 
                        href="{{ route('results.index') }}" 
                        variant="outline-primary" 
                        size="sm" 
                        icon="fas fa-eye">
                        {{ __('View All Results') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="card-body">
                @if($topPerformers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($topPerformers->take(10) as $index => $performer)
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0 mr-4">
                                <div class="w-10 h-10 {{ $index < 3 ? 'bg-success-500' : 'bg-primary-500' }} text-white rounded-full flex items-center justify-center text-sm font-semibold">
                                    #{{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">{{ $performer->employee->name }}</div>
                                <div class="text-sm text-gray-500">{{ $performer->employee->department }}</div>
                                <div class="flex items-center mt-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-1 mr-3">
                                        <div class="h-1 {{ $index < 3 ? 'bg-success-500' : 'bg-primary-500' }} rounded-full transition-all duration-300"
                                             style="width: {{ round($performer->total_score * 100, 2) }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ round($performer->total_score * 100, 2) }}%</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-chart-bar text-4xl mb-4 opacity-50"></i>
                        <p>{{ __('No evaluation results available yet') }}</p>
                        <p class="text-sm">{{ __('Complete some evaluations to see the top performers') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions & System Status -->
    <div class="space-y-6">
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-bolt text-warning-500"></i>
                    {{ __('Quick Actions') }}
                </h6>
            </div>
            <div class="card-body space-y-3">
                <x-ui.button 
                    href="{{ route('employees.create') }}" 
                    variant="outline-primary" 
                    size="sm" 
                    icon="fas fa-user-plus"
                    class="w-full justify-start">
                    {{ __('Add Employee') }}
                </x-ui.button>
                
                <x-ui.button 
                    href="{{ route('criterias.create') }}" 
                    variant="outline-success" 
                    size="sm" 
                    icon="fas fa-plus-circle"
                    class="w-full justify-start">
                    {{ __('Add Criteria') }}
                </x-ui.button>
                
                <x-ui.button 
                    href="{{ route('evaluations.create') }}" 
                    variant="outline-info" 
                    size="sm" 
                    icon="fas fa-clipboard-list"
                    class="w-full justify-start">
                    {{ __('New Evaluation') }}
                </x-ui.button>
                
                <x-ui.button 
                    href="{{ route('analysis.index') }}" 
                    variant="outline-warning" 
                    size="sm" 
                    icon="fas fa-chart-bar"
                    class="w-full justify-start">
                    {{ __('Advanced Analysis') }}
                </x-ui.button>
            </div>
        </div>

        <!-- System Status Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-cogs text-info-500"></i>
                    {{ __('System Status') }}
                </h6>
            </div>
            <div class="card-body space-y-4">
                <!-- Criteria Weight Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-balance-scale text-gray-400"></i>
                        <span class="text-sm text-gray-700">{{ __('Criteria Weight') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(($stats['total_weight'] ?? 0) == 100)
                            <span class="badge badge-success">{{ $stats['total_weight'] }}%</span>
                        @else
                            <span class="badge badge-warning">{{ $stats['total_weight'] ?? 0 }}%</span>
                        @endif
                    </div>
                </div>

                <!-- Data Completeness -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-database text-gray-400"></i>
                        <span class="text-sm text-gray-700">{{ __('Data Completeness') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $completeness = 0;
                            if ($stats['total_employees'] > 0) $completeness += 25;
                            if ($stats['total_criterias'] > 0) $completeness += 25;
                            if ($stats['total_evaluations'] > 0) $completeness += 25;
                            if (($stats['total_weight'] ?? 0) == 100) $completeness += 25;
                        @endphp
                        @if($completeness >= 75)
                            <span class="badge badge-success">{{ $completeness }}%</span>
                        @elseif($completeness >= 50)
                            <span class="badge badge-warning">{{ $completeness }}%</span>
                        @else
                            <span class="badge badge-danger">{{ $completeness }}%</span>
                        @endif
                    </div>
                </div>

                <!-- System Health -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-heart text-gray-400"></i>
                        <span class="text-sm text-gray-700">{{ __('System Health') }}</span>
                    </div>
                    <span class="badge badge-success">{{ __('Healthy') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Charts -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-8">
    <!-- Performance Chart -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-chart-line text-primary-500"></i>
                {{ __('Performance Trends') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="relative">
                <canvas id="performanceChart" class="w-full h-64"></canvas>
                <div id="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                    <div class="loading-spinner w-8 h-8"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-chart-pie text-success-500"></i>
                {{ __('Department Distribution') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="relative">
                <canvas id="departmentChart" class="w-full h-64"></canvas>
                <div id="deptChartLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                    <div class="loading-spinner w-8 h-8"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
@if(isset($recentActivities) && $recentActivities->count() > 0)
<div class="card mt-8">
    <div class="card-header">
        <h6 class="flex items-center gap-2 font-semibold text-gray-900">
            <i class="fas fa-clock text-info-500"></i>
            {{ __('Recent Activities') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="space-y-4">
            @foreach($recentActivities->take(5) as $activity)
            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-{{ $activity->icon ?? 'info-circle' }} text-sm"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Load dashboard data
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
});

function initializeCharts() {
    // Performance Chart
    const perfCtx = document.getElementById('performanceChart');
    if (perfCtx) {
        const performanceChart = new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '{{ __("Average Performance") }}',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        window.performanceChart = performanceChart;
    }

    // Department Chart
    const deptCtx = document.getElementById('departmentChart');
    if (deptCtx) {
        const departmentChart = new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        window.departmentChart = departmentChart;
    }
}

function loadDashboardData() {
    // Load chart data
    fetch('{{ route("dashboard.chart-data") }}')
        .then(response => response.json())
        .then(data => {
            updateCharts(data);
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        })
        .finally(() => {
            // Hide loading indicators
            document.getElementById('chartLoading')?.classList.add('hidden');
            document.getElementById('deptChartLoading')?.classList.add('hidden');
        });
}

function updateCharts(data) {
    // Update performance chart
    if (window.performanceChart && data.performance) {
        window.performanceChart.data.labels = data.performance.labels;
        window.performanceChart.data.datasets[0].data = data.performance.data;
        window.performanceChart.update();
    }
    
    // Update department chart
    if (window.departmentChart && data.departments) {
        window.departmentChart.data.labels = data.departments.labels;
        window.departmentChart.data.datasets[0].data = data.departments.data;
        window.departmentChart.update();
    }
}

// Stats card animations
document.addEventListener('DOMContentLoaded', function() {
    const statsCards = document.querySelectorAll('.stats-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('stats-card-animate');
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1
    });
    
    statsCards.forEach(card => {
        observer.observe(card);
    });
});
</script>
@endpush