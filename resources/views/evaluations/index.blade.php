@extends('layouts.main')

@section('title', __('Evaluations') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Evaluations'))

@section('content')
<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Employee Evaluations') }}</h1>
        <p class="text-gray-600">{{ __('Manage and track employee performance evaluations') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button variant="outline-secondary" size="sm" icon="fas fa-sync-alt" onclick="refreshTable()" id="refreshBtn">{{ __('Refresh') }}</x-ui.button>
        <div class="dropdown" x-data="{ open: false }">
            <x-ui.button variant="success" size="sm" icon="fas fa-file-import" @click="open = !open" class="dropdown-toggle">{{ __('Import') }}</x-ui.button>
            <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                <a class="dropdown-item dropdown-item-icon" href="{{ route('evaluations.import-template') }}">
                    <i class="fas fa-download text-success-600"></i>{{ __('Download Template') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item dropdown-item-icon" href="#" onclick="showImportModal()">
                    <i class="fas fa-upload text-primary-600"></i>{{ __('Upload Data') }}
                </a>
            </div>
        </div>
        <x-ui.button href="{{ route('evaluations.batch-create') }}" variant="warning" icon="fas fa-users">{{ __('Batch Evaluation') }}</x-ui.button>
        <x-ui.button href="{{ route('evaluations.create') }}" variant="primary" icon="fas fa-plus">{{ __('New Evaluation') }}</x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number" id="totalEvaluations">{{ $stats['total'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Total Evaluations') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-clipboard-list"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number" id="completedEvaluations">{{ $stats['completed'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Completed') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number" id="pendingEvaluations">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stats-label">{{ __('Pending') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stats-card bg-gradient-to-br from-info-500 to-info-600">
        <div class="stats-content">
            <div class="stats-number" id="avgScore">{{ round($stats['avg_score'] ?? 0, 1) }}</div>
            <div class="stats-label">{{ __('Avg Score') }}</div>
        </div>
        <div class="stats-icon"><i class="fas fa-chart-line"></i></div>
    </div>
</div>

<!-- Filters Section -->
<div class="card mb-6">
    <div class="card-header">
        <h6 class="flex items-center gap-2 font-semibold text-gray-900">
            <i class="fas fa-filter text-primary-500"></i>{{ __('Filters & Search') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="form-group">
                <label class="form-label">{{ __('Period') }}</label>
                <select id="periodFilter" class="form-select">
                    <option value="">{{ __('All Periods') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Department') }}</label>
                <select id="departmentFilter" class="form-select">
                    <option value="">{{ __('All Departments') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Status') }}</label>
                <select id="statusFilter" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Date Range') }}</label>
                <input type="date" id="dateFilter" class="form-control">
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-table text-primary-500"></i>{{ __('Evaluation List') }}
            </h6>
            <div class="flex items-center gap-2">
                <div class="dropdown" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-outline-secondary btn-sm dropdown-toggle">
                        <i class="fas fa-download mr-2"></i>{{ __('Export') }}
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('evaluations.export-excel') }}">
                            <i class="fas fa-file-excel text-success-600"></i>{{ __('Excel Format') }}
                        </a>
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('evaluations.export-pdf') }}">
                            <i class="fas fa-file-pdf text-danger-600"></i>{{ __('PDF Format') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="evaluationsTable" class="table dataTable">
                <thead>
                    <tr>
                        <th width="50">{{ __('No') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Total Score') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Evaluated At') }}</th>
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
let evaluationsTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeEvaluationsPage();
});

function initializeEvaluationsPage() {
    initializeDataTable();
    loadFilterOptions();
    bindEvents();
}

function initializeDataTable() {
    evaluationsTable = $('#evaluationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("evaluations.index") }}',
            data: function (d) {
                d.period = $('#periodFilter').val();
                d.department = $('#departmentFilter').val();
                d.status = $('#statusFilter').val();
                d.date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'employee_name', name: 'employee.name' },
            { data: 'evaluation_period', name: 'evaluation_period' },
            { data: 'department', name: 'employee.department' },
            { 
                data: 'total_score', 
                name: 'total_score',
                render: function(data) {
                    return `<span class="font-semibold text-primary-600">${(data * 100).toFixed(2)}%</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    return data === 'completed' 
                        ? '<span class="badge badge-success">{{ __("Completed") }}</span>'
                        : '<span class="badge badge-warning">{{ __("Pending") }}</span>';
                }
            },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

function bindEvents() {
    $('#periodFilter, #departmentFilter, #statusFilter, #dateFilter').change(function() {
        evaluationsTable.ajax.reload();
    });
}

function loadFilterOptions() {
    fetch('{{ route("evaluations.periods") }}')
        .then(response => response.json())
        .then(data => {
            const periodSelect = $('#periodFilter');
            data.forEach(period => {
                periodSelect.append(`<option value="${period}">${period}</option>`);
            });
        });
}

function refreshTable() {
    const btn = $('#refreshBtn');
    btn.prop('disabled', true).find('i').addClass('fa-spin');
    evaluationsTable.ajax.reload(() => {
        btn.prop('disabled', false).find('i').removeClass('fa-spin');
    });
}

function showImportModal() {
    // Implementation for import modal
}
</script>
@endpush