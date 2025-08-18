@extends('layouts.main')

@section('title', __('Results & Ranking') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Results & Ranking'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Evaluation Results') }}</h1>
        <p class="text-gray-600">{{ __('SAW method calculation results and employee ranking') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button variant="outline-secondary" size="sm" icon="fas fa-sync-alt" onclick="refreshResults()">{{ __('Refresh') }}</x-ui.button>
        <div class="dropdown" x-data="{ open: false }">
            <x-ui.button variant="success" size="sm" icon="fas fa-download" @click="open = !open">{{ __('Export') }}</x-ui.button>
            <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                <a class="dropdown-item dropdown-item-icon" href="{{ route('results.export-excel') }}">
                    <i class="fas fa-file-excel text-success-600"></i>{{ __('Excel Report') }}
                </a>
                <a class="dropdown-item dropdown-item-icon" href="{{ route('results.export-pdf') }}">
                    <i class="fas fa-file-pdf text-danger-600"></i>{{ __('PDF Report') }}
                </a>
            </div>
        </div>
        <x-ui.button href="{{ route('evaluations.create') }}" variant="primary" icon="fas fa-plus">{{ __('New Evaluation') }}</x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_results'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Total Results') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-trophy"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number">{{ round($stats['highest_score'] ?? 0, 2) }}%</div>
            <div class="stats-label">{{ __('Highest Score') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-crown"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number">{{ round($stats['average_score'] ?? 0, 2) }}%</div>
            <div class="stats-label">{{ __('Average Score') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-chart-line"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-info-500 to-info-600">
        <div class="stats-content">
            <div class="stats-number">{{ $stats['total_periods'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Evaluation Periods') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-calendar"></i></div>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h6 class="flex items-center gap-2 font-semibold text-gray-900">
            <i class="fas fa-table text-primary-500"></i>{{ __('Ranking Results') }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="resultsTable" class="table dataTable">
                <thead>
                    <tr>
                        <th width="60">{{ __('Rank') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Total Score') }}</th>
                        <th>{{ __('Performance') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let resultsTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeResultsTable();
});

function initializeResultsTable() {
    resultsTable = $('#resultsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("results.index") }}',
        columns: [
            { 
                data: 'rank', 
                name: 'rank',
                className: 'text-center',
                render: function(data) {
                    let badgeClass = 'badge-secondary';
                    let icon = '';
                    if (data <= 3) {
                        badgeClass = data === 1 ? 'badge-warning' : 'badge-secondary';
                        icon = data === 1 ? '<i class="fas fa-crown mr-1"></i>' : '<i class="fas fa-medal mr-1"></i>';
                    }
                    return `<span class="badge ${badgeClass}">${icon}#${data}</span>`;
                }
            },
            { data: 'employee_name', name: 'employee.name' },
            { data: 'department', name: 'employee.department' },
            { data: 'evaluation_period', name: 'evaluation_period' },
            { 
                data: 'total_score', 
                name: 'total_score',
                render: function(data) {
                    const percentage = (data * 100).toFixed(2);
                    let colorClass = 'text-danger-600';
                    if (percentage >= 80) colorClass = 'text-success-600';
                    else if (percentage >= 60) colorClass = 'text-warning-600';
                    return `<span class="font-bold ${colorClass}">${percentage}%</span>`;
                }
            },
            {
                data: 'total_score',
                name: 'performance',
                orderable: false,
                render: function(data) {
                    const percentage = data * 100;
                    let colorClass = 'bg-danger-500';
                    if (percentage >= 80) colorClass = 'bg-success-500';
                    else if (percentage >= 60) colorClass = 'bg-warning-500';
                    
                    return `
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="${colorClass} h-2 rounded-full transition-all duration-300" style="width: ${percentage}%"></div>
                        </div>
                    `;
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

function refreshResults() {
    resultsTable.ajax.reload();
}
</script>
@endpush