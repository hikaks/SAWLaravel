@extends('layouts.main')

@section('title', __('Advanced Analysis') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Advanced Analysis Dashboard'))

@section('content')
<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Advanced Analytics') }}</h1>
        <p class="text-gray-600">{{ __('Comprehensive analysis tools for decision support system') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button variant="outline-info" icon="fas fa-history" onclick="showAnalysisHistory()">{{ __('Analysis History') }}</x-ui.button>
        <x-ui.button variant="outline-primary" icon="fas fa-download" onclick="exportDashboard()">{{ __('Export Dashboard') }}</x-ui.button>
        <x-ui.button href="{{ route('analysis.debug') }}" variant="outline-danger" icon="fas fa-bug">{{ __('Debug') }}</x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600">
        <div class="stats-content">
            <div class="stats-number">{{ $availablePeriods->count() }}</div>
            <div class="stats-label">{{ __('Available Periods') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-calendar-alt"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-indigo-500 to-indigo-600">
        <div class="stats-content">
            <div class="stats-number">{{ $totalEmployees }}</div>
            <div class="stats-label">{{ __('Analyzed Employees') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-users"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-green-500 to-green-600">
        <div class="stats-content">
            <div class="stats-number">{{ $activeCriterias }}</div>
            <div class="stats-label">{{ __('Active Criteria') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-sliders-h"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-red-500 to-red-600">
        <div class="stats-content">
            <div class="stats-number">{{ $totalAnalyses ?? 0 }}</div>
            <div class="stats-label">{{ __('Total Analyses') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-chart-bar"></i></div>
    </div>
</div>

<!-- Analysis Tools Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Sensitivity Analysis -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Sensitivity Analysis') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Analyze how changes in criteria weights affect rankings') }}</p>
            <x-ui.button href="{{ route('analysis.sensitivity.view') }}" variant="outline-primary" size="sm" class="w-full">
                {{ __('Start Analysis') }}
            </x-ui.button>
        </div>
    </div>

    <!-- What-If Scenarios -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-question-circle text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('What-If Scenarios') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Simulate different scoring scenarios and outcomes') }}</p>
            <x-ui.button href="{{ route('analysis.what-if.view') }}" variant="outline-success" size="sm" class="w-full">
                {{ __('Create Scenario') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Multi-Period Comparison -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-balance-scale text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Period Comparison') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Compare performance across different evaluation periods') }}</p>
            <x-ui.button href="{{ route('analysis.comparison.view') }}" variant="outline-warning" size="sm" class="w-full">
                {{ __('Compare Periods') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Performance Forecast -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-crystal-ball text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Performance Forecast') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Predict future performance based on historical data') }}</p>
            <x-ui.button href="{{ route('analysis.forecast.view') }}" variant="outline-info" size="sm" class="w-full">
                {{ __('Generate Forecast') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Advanced Statistics -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calculator text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Advanced Statistics') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Detailed statistical analysis and insights') }}</p>
            <x-ui.button onclick="performAdvancedStats()" variant="outline-secondary" size="sm" class="w-full">
                {{ __('View Statistics') }}
            </x-ui.button>
        </div>
    </div>

    <!-- Custom Analysis -->
    <div class="card hover:shadow-lg transition-shadow duration-300">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-cogs text-2xl"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Custom Analysis') }}</h5>
            <p class="text-gray-600 text-sm mb-4">{{ __('Create custom analysis with specific parameters') }}</p>
            <x-ui.button onclick="showCustomAnalysis()" variant="outline-primary" size="sm" class="w-full">
                {{ __('Configure') }}
            </x-ui.button>
        </div>
    </div>
</div>

<!-- Quick Insights -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Performance Overview Chart -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-chart-area text-primary-500"></i>{{ __('Performance Overview') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="relative">
                <canvas id="performanceOverviewChart" class="w-full h-64"></canvas>
                <div id="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                    <div class="loading-spinner w-8 h-8"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Insights -->
    <div class="card">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-lightbulb text-warning-500"></i>{{ __('Key Insights') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="space-y-4" id="insightsContainer">
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    <span class="text-sm text-blue-800">{{ __('Loading insights...') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAnalysisDashboard();
});

function initializeAnalysisDashboard() {
    loadPerformanceChart();
    loadInsights();
}

function loadPerformanceChart() {
    const ctx = document.getElementById('performanceOverviewChart');
    if (ctx) {
        const chart = new Chart(ctx, {
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
                maintainAspectRatio: false
            }
        });
        
        // Load data
        fetch('{{ route("results.chart-data") }}')
            .then(response => response.json())
            .then(data => {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data;
                chart.update();
                document.getElementById('chartLoading').classList.add('hidden');
            });
    }
}

function loadInsights() {
    const container = document.getElementById('insightsContainer');
    
    fetch('{{ route("analysis.statistics") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        container.innerHTML = '';
        if (data.insights && data.insights.length > 0) {
            data.insights.forEach(insight => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg';
                div.innerHTML = `
                    <i class="fas fa-lightbulb text-yellow-500"></i>
                    <span class="text-sm text-gray-800">${insight}</span>
                `;
                container.appendChild(div);
            });
        }
    })
    .catch(error => {
        console.error('Error loading insights:', error);
    });
}

function showAnalysisHistory() {
    // Implementation for analysis history
}

function exportDashboard() {
    // Implementation for dashboard export
}

function performAdvancedStats() {
    // Implementation for advanced statistics
}

function showCustomAnalysis() {
    // Implementation for custom analysis
}
</script>
@endpush