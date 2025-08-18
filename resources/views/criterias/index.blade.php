@extends('layouts.main')

@section('title', 'Evaluation Criteria - SAW Employee Evaluation')
@section('page-title', 'Evaluation Criteria')

@section('content')
<!-- Weight Status Card -->
<div class="card mb-6 {{ $totalWeight == 100 ? 'bg-gradient-to-br from-success-500 to-success-600' : ($totalWeight < 100 ? 'bg-gradient-to-br from-warning-500 to-warning-600' : 'bg-gradient-to-br from-danger-500 to-danger-600') }} text-white overflow-hidden">
    <div class="card-body">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="w-15 h-15 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-{{ $totalWeight == 100 ? 'check-circle' : ($totalWeight < 100 ? 'exclamation-triangle' : 'times-circle') }} text-2xl"></i>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h5 class="text-xl font-bold mb-2">{{ __('Criteria Weight Status') }}</h5>
                <h2 class="text-3xl font-bold mb-2">{{ $totalWeight }}/100</h2>
                @if($totalWeight == 100)
                    <p class="text-white/90">
                        <i class="fas fa-star mr-2"></i>
                        <strong>{{ __('Perfect!') }}</strong> {{ __('Ready for SAW calculation') }}
                    </p>
                @elseif($totalWeight < 100)
                    <p class="text-white/90">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <strong>{{ 100 - $totalWeight }} {{ __('points more needed') }}</strong> {{ __('to reach 100%') }}
                    </p>
                @else
                    <p class="text-white/90">
                        <i class="fas fa-minus-circle mr-2"></i>
                        <strong>{{ __('Exceeds') }} {{ $totalWeight - 100 }} {{ __('points!') }}</strong> {{ __('Need to reduce') }}
                    </p>
                @endif
            </div>
            <div class="text-center">
                <div class="relative inline-block mb-3">
                    <svg width="80" height="80" class="transform -rotate-90">
                        <circle cx="40" cy="40" r="35" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="8"/>
                        <circle cx="40" cy="40" r="35" fill="none" stroke="white" stroke-width="8"
                                stroke-dasharray="{{ 2 * 3.14159 * 35 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 35 * (1 - min($totalWeight, 100) / 100) }}"
                                stroke-linecap="round"
                                class="transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-lg font-bold">{{ $totalWeight }}%</span>
                    </div>
                </div>
                <p class="text-sm text-white/75">{{ __('Weight Progress') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Evaluation Criteria') }}</h1>
        <p class="text-gray-600">{{ __('Manage evaluation criteria and their weights for SAW method') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            variant="outline-secondary" 
            size="sm" 
            icon="fas fa-sync-alt"
            onclick="refreshTable()" 
            id="refreshBtn">
            {{ __('Refresh') }}
        </x-ui.button>
        
        <div class="dropdown" x-data="{ open: false }">
            <x-ui.button 
                variant="success" 
                size="sm" 
                icon="fas fa-file-import"
                @click="open = !open"
                class="dropdown-toggle">
                {{ __('Import') }}
            </x-ui.button>
            <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                <a class="dropdown-item dropdown-item-icon" href="{{ route('criterias.import-template') }}">
                    <i class="fas fa-download text-success-600"></i>
                    {{ __('Download Template') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item dropdown-item-icon" href="#" onclick="showImportModal()">
                    <i class="fas fa-upload text-primary-600"></i>
                    {{ __('Upload Data') }}
                </a>
            </div>
        </div>
        
        <x-ui.button 
            href="{{ route('criterias.create') }}" 
            variant="primary" 
            icon="fas fa-plus">
            {{ __('Add Criteria') }}
        </x-ui.button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stats-card bg-gradient-to-br from-primary-500 to-primary-600">
        <div class="stats-content">
            <div class="stats-number" id="totalCriterias">{{ $totalCriterias }}</div>
            <div class="stats-label">{{ __('Total Criteria') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-sliders"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-success-500 to-success-600">
        <div class="stats-content">
            <div class="stats-number" id="benefitCriterias">{{ $benefitCriterias }}</div>
            <div class="stats-label">{{ __('Benefit Type') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-warning-500 to-warning-600">
        <div class="stats-content">
            <div class="stats-number" id="costCriterias">{{ $costCriterias }}</div>
            <div class="stats-label">{{ __('Cost Type') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
    </div>
    <div class="stats-card bg-gradient-to-br from-info-500 to-info-600">
        <div class="stats-content">
            <div class="stats-number" id="avgWeight">{{ round($avgWeight, 1) }}</div>
            <div class="stats-label">{{ __('Avg Weight') }}</div>
        </div>
        <div class="stats-icon">
            <i class="fas fa-balance-scale"></i>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-table text-primary-500"></i>
                {{ __('Criteria List') }}
            </h6>
            <div class="flex items-center gap-2">
                <div class="dropdown" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-outline-secondary btn-sm dropdown-toggle">
                        <i class="fas fa-download mr-2"></i>
                        {{ __('Export') }}
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('criterias.export-excel') }}">
                            <i class="fas fa-file-excel text-success-600"></i>
                            {{ __('Excel Format') }}
                        </a>
                        <a class="dropdown-item dropdown-item-icon" href="{{ route('criterias.export-pdf') }}">
                            <i class="fas fa-file-pdf text-danger-600"></i>
                            {{ __('PDF Format') }}
                        </a>
                    </div>
                </div>
                <button onclick="toggleBulkActions()" class="btn btn-outline-primary btn-sm" id="bulkActionToggle" style="display: none;">
                    <i class="fas fa-tasks mr-2"></i>
                    {{ __('Bulk Actions') }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="criteriasTable" class="table dataTable">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th width="50">{{ __('No') }}</th>
                        <th>{{ __('Criteria Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Weight') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th width="120">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Weight Validation Alert -->
<div id="weightAlert" class="fixed bottom-4 right-4 z-50" style="display: none;">
    <div class="alert alert-warning shadow-lg max-w-sm">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-warning-600"></i>
            <div>
                <p class="font-semibold">{{ __('Weight Warning') }}</p>
                <p class="text-sm" id="weightMessage"></p>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div x-data="{ showImportModal: false }" x-show="showImportModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Criteria') }}</h5>
                <button @click="showImportModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">{{ __('Select Excel File') }}</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <div class="text-sm text-gray-500 mt-2">
                            {{ __('Accepted formats: .xlsx, .xls, .csv') }}
                        </div>
                    </div>
                    <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-info-600"></i>
                            <div>
                                <h6 class="font-semibold text-info-800">{{ __('Import Instructions') }}</h6>
                                <ul class="text-sm text-info-700 mt-2 space-y-1">
                                    <li>• {{ __('Download the template first') }}</li>
                                    <li>• {{ __('Fill in the criteria data') }}</li>
                                    <li>• {{ __('Make sure weights sum to 100') }}</li>
                                    <li>• {{ __('Criteria codes must be unique') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showImportModal = false" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="fas fa-upload mr-2"></i>
                        {{ __('Import Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div x-data="{ showBulkModal: false }" x-show="showBulkModal" class="modal" x-transition>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Actions') }}</h5>
                <button @click="showBulkModal = false" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <p class="text-gray-700">
                        {{ __('Selected criteria: ') }}<span id="selectedCount" class="font-semibold">0</span>
                    </p>
                </div>
                <div class="space-y-3">
                    <button onclick="bulkAction('activate')" class="btn btn-success w-full justify-start">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ __('Activate Selected') }}
                    </button>
                    <button onclick="bulkAction('deactivate')" class="btn btn-warning w-full justify-start">
                        <i class="fas fa-pause-circle mr-2"></i>
                        {{ __('Deactivate Selected') }}
                    </button>
                    <button onclick="bulkAction('delete')" class="btn btn-danger w-full justify-start">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" @click="showBulkModal = false" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let criteriasTable;
let selectedCriterias = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeCriteriasPage();
    checkWeightStatus();
});

function initializeCriteriasPage() {
    initializeDataTable();
    loadStatistics();
    bindEvents();
}

function initializeDataTable() {
    criteriasTable = $('#criteriasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("criterias.index") }}',
            data: function (d) {
                // Add any additional filters here
            }
        },
        columns: [
            {
                data: 'checkbox',
                name: 'checkbox',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    return `<div class="font-medium">${data}</div>`;
                }
            },
            {
                data: 'code',
                name: 'code',
                render: function(data) {
                    return `<code class="badge badge-secondary">${data}</code>`;
                }
            },
            {
                data: 'type',
                name: 'type',
                render: function(data) {
                    if (data === 'benefit') {
                        return '<span class="badge badge-success"><i class="fas fa-arrow-up mr-1"></i>{{ __("Benefit") }}</span>';
                    } else {
                        return '<span class="badge badge-warning"><i class="fas fa-arrow-down mr-1"></i>{{ __("Cost") }}</span>';
                    }
                }
            },
            {
                data: 'weight',
                name: 'weight',
                render: function(data) {
                    return `<span class="font-semibold text-primary-600">${data}%</span>`;
                }
            },
            {
                data: 'unit',
                name: 'unit',
                render: function(data) {
                    return data || '<span class="text-gray-400">-</span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    if (data === 'active') {
                        return '<span class="badge badge-success">{{ __("Active") }}</span>';
                    } else {
                        return '<span class="badge badge-danger">{{ __("Inactive") }}</span>';
                    }
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="flex items-center gap-2"><div class="loading-spinner w-5 h-5"></div>{{ __("Loading...") }}</div>',
            search: '{{ __("Search:") }}',
            lengthMenu: '{{ __("Show _MENU_ entries") }}',
            info: '{{ __("Showing _START_ to _END_ of _TOTAL_ entries") }}',
            infoEmpty: '{{ __("No entries found") }}',
            infoFiltered: '{{ __("(filtered from _MAX_ total entries)") }}',
            paginate: {
                first: '{{ __("First") }}',
                last: '{{ __("Last") }}',
                next: '{{ __("Next") }}',
                previous: '{{ __("Previous") }}'
            }
        },
        drawCallback: function() {
            // Update weight status after each draw
            setTimeout(checkWeightStatus, 500);
        }
    });
}

function bindEvents() {
    // Select all checkbox
    $('#selectAll').change(function() {
        const isChecked = $(this).is(':checked');
        $('.criteria-checkbox').prop('checked', isChecked);
        updateSelectedCriterias();
    });

    // Individual checkboxes
    $(document).on('change', '.criteria-checkbox', function() {
        updateSelectedCriterias();
    });

    // Import form
    $('#importForm').submit(function(e) {
        e.preventDefault();
        handleImport();
    });
}

function loadStatistics() {
    // Statistics are already loaded from the controller
    // This function can be used to refresh stats if needed
}

function refreshTable() {
    const btn = $('#refreshBtn');
    btn.prop('disabled', true);
    btn.find('i').addClass('fa-spin');
    
    criteriasTable.ajax.reload(() => {
        btn.prop('disabled', false);
        btn.find('i').removeClass('fa-spin');
        loadStatistics();
        checkWeightStatus();
    });
}

function updateSelectedCriterias() {
    selectedCriterias = [];
    $('.criteria-checkbox:checked').each(function() {
        selectedCriterias.push($(this).val());
    });
    
    $('#selectedCount').text(selectedCriterias.length);
    $('#bulkActionToggle').toggle(selectedCriterias.length > 0);
}

function checkWeightStatus() {
    fetch('{{ route("criterias.total-weight") }}')
        .then(response => response.json())
        .then(data => {
            const totalWeight = data.total_weight;
            const alertDiv = $('#weightAlert');
            const messageDiv = $('#weightMessage');
            
            if (totalWeight !== 100) {
                if (totalWeight < 100) {
                    messageDiv.text(`{{ __('Need') }} ${100 - totalWeight} {{ __('more points to reach 100%') }}`);
                } else {
                    messageDiv.text(`{{ __('Exceeds by') }} ${totalWeight - 100} {{ __('points. Please reduce.') }}`);
                }
                alertDiv.show();
                
                // Auto-hide after 10 seconds
                setTimeout(() => {
                    alertDiv.fadeOut();
                }, 10000);
            } else {
                alertDiv.hide();
            }
        })
        .catch(error => {
            console.error('Error checking weight status:', error);
        });
}

function showImportModal() {
    Alpine.store('modals', { showImportModal: true });
}

function toggleBulkActions() {
    Alpine.store('modals', { showBulkModal: true });
}

function handleImport() {
    const formData = new FormData($('#importForm')[0]);
    const btn = $('#importBtn');
    
    btn.prop('disabled', true);
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("Importing...") }}');
    
    fetch('{{ route("criterias.import") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '{{ __("Success") }}',
                text: data.message
            });
            Alpine.store('modals', { showImportModal: false });
            criteriasTable.ajax.reload();
            loadStatistics();
            checkWeightStatus();
        } else {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Import error:', error);
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: '{{ __("An error occurred during import") }}'
        });
    })
    .finally(() => {
        btn.prop('disabled', false);
        btn.html('<i class="fas fa-upload mr-2"></i>{{ __("Import Data") }}');
    });
}

function bulkAction(action) {
    if (selectedCriterias.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Warning") }}',
            text: '{{ __("Please select at least one criteria") }}'
        });
        return;
    }
    
    let title, text, confirmText;
    
    switch(action) {
        case 'activate':
            title = '{{ __("Activate Criteria") }}';
            text = '{{ __("Are you sure you want to activate the selected criteria?") }}';
            confirmText = '{{ __("Activate") }}';
            break;
        case 'deactivate':
            title = '{{ __("Deactivate Criteria") }}';
            text = '{{ __("Are you sure you want to deactivate the selected criteria?") }}';
            confirmText = '{{ __("Deactivate") }}';
            break;
        case 'delete':
            title = '{{ __("Delete Criteria") }}';
            text = '{{ __("Are you sure you want to delete the selected criteria? This action cannot be undone.") }}';
            confirmText = '{{ __("Delete") }}';
            break;
    }
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction(action);
        }
    });
}

function performBulkAction(action) {
    fetch('{{ route("criterias.bulk-action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({
            action: action,
            criterias: selectedCriterias
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '{{ __("Success") }}',
                text: data.message
            });
            Alpine.store('modals', { showBulkModal: false });
            criteriasTable.ajax.reload();
            loadStatistics();
            checkWeightStatus();
            selectedCriterias = [];
            updateSelectedCriterias();
        } else {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Bulk action error:', error);
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: '{{ __("An error occurred") }}'
        });
    });
}

// Initialize Alpine.js stores
document.addEventListener('alpine:init', () => {
    Alpine.store('modals', {
        showImportModal: false,
        showBulkModal: false
    });
});
</script>
@endpush