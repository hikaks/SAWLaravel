@extends('layouts.main')

@section('title', __('SAW Evaluation Results') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('SAW Evaluation Results'))

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">{{ __('Evaluation Results') }}</h1>
        <p class="text-muted mb-0">{{ __('Employee ranking results based on SAW calculation') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button 
            variant="outline-info" 
            icon="fas fa-info-circle"
            data-bs-toggle="modal" 
            data-bs-target="#calculationModal">
            {{ __('How SAW Works') }}
        </x-ui.button>
        <x-ui.button 
            variant="success" 
            icon="fas fa-calculator"
            onclick="generateResults()"
            id="generateBtn">
            {{ __('Generate Results') }}
        </x-ui.button>
        <x-ui.button 
            variant="outline-secondary" 
            icon="fas fa-sync-alt"
            onclick="refreshResults()"
            id="refreshResultsBtn">
            {{ __('Refresh') }}
        </x-ui.button>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #0366d6 0%, #0256c7 100%);">
            <div class="stats-content">
                <div class="stats-number" id="totalEmployees">0</div>
                <div class="stats-label">{{ __('Total Employees') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stats-content">
                <div class="stats-number" id="topPerformers">0</div>
                <div class="stats-label">{{ __('Top Performers') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
            <div class="stats-content">
                <div class="stats-number" id="totalPeriods">{{ $periods->count() }}</div>
                <div class="stats-label">{{ __('Evaluation Periods') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-calendar"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stats-content">
                <div class="stats-number" id="avgScore">0%</div>
                <div class="stats-label">{{ __('Average Score') }}</div>
            </div>
            <div class="stats-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-ranking-star me-2 text-primary"></i>
                {{ __('Employee Rankings') }}
            </h5>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <select class="form-select" id="periodFilter" style="min-width: 200px;">
                    <option value="all">{{ __('All Periods') }}</option>
                    @foreach($periods as $period)
                        <option value="{{ $period }}">{{ $period }}</option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button 
                        variant="danger" 
                        icon="fas fa-file-pdf"
                        onclick="exportResults('pdf')"
                        data-format="pdf"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Export comprehensive SAW results as PDF report with rankings, statistics, and criteria information') }}"
                        id="exportPdfBtn">
                        Export PDF
                    </x-ui.button>
                    <x-ui.button 
                        variant="success" 
                        icon="fas fa-file-excel"
                        onclick="exportResults('excel')"
                        data-format="excel"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Export detailed SAW results as Excel spreadsheet with performance analysis and statistics') }}"
                        id="exportExcelBtn">
                        Export Excel
                    </x-ui.button>
                    @if(app()->environment('local') || auth()->user()->isAdmin())
                    <x-ui.button 
                        variant="outline-secondary" 
                        icon="fas fa-bug"
                        onclick="debugPdfTemplate()"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Debug PDF template - view HTML before PDF conversion') }}">
                        Debug
                    </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($periods->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="resultsTable">
                    <thead>
                        <tr>
                            <th width="5%">{{ __('Rank') }}</th>
                            <th width="15%">{{ __('Employee Code') }}</th>
                            <th width="25%">{{ __('Employee Name') }}</th>
                            <th width="15%">{{ __('Department') }}</th>
                            <th width="10%">{{ __('Score') }}</th>
                            <th width="15%">{{ __('Period') }}</th>
                            <th width="15%">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5 class="text-muted mb-3">{{ __('No Results Available') }}</h5>
                <p class="text-muted mb-4">{{ __('No evaluation results found. Please complete evaluations first.') }}</p>
                <x-ui.button 
                    href="{{ route('evaluations.index') }}" 
                    variant="primary" 
                    icon="fas fa-plus">
                    {{ __('Start Evaluations') }}
                </x-ui.button>
            </div>
        @endif
    </div>
</div>

<!-- Charts Section -->
@if($periods->count() > 0)
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-success"></i>
                    {{ __('Top 10 Performers') }}
                </h6>
            </div>
            <div class="card-body">
                <canvas id="topPerformersChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-pie me-2 text-info"></i>
                    {{ __('Department Comparison') }}
                </h6>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- SAW Calculation Info Modal -->
<div class="modal fade" id="calculationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>
                    {{ __('Simple Additive Weighting (SAW) Method') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">{{ __('Normalization Process') }}</h6>
                        <p class="mb-3">{{ __('SAW method uses different normalization for different criteria types:') }}</p>

                        <div class="alert alert-success">
                            <strong>{{ __('Benefit Criteria') }}:</strong><br>
                            <code>Rij = Xij / Max(Xij)</code><br>
                            <small class="text-muted">{{ __('Higher values are better') }}</small>
                        </div>

                        <div class="alert alert-warning">
                            <strong>{{ __('Cost Criteria') }}:</strong><br>
                            <code>Rij = Min(Xij) / Xij</code><br>
                            <small class="text-muted">{{ __('Lower values are better') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">{{ __('Final Calculation') }}</h6>
                        <p class="mb-3">{{ __('The final score is calculated using weighted sum:') }}</p>

                        <div class="alert alert-info">
                            <strong>{{ __('Final Score') }}:</strong><br>
                            <code>Vi = Σ(Wj × Rij)</code><br>
                            <small class="text-muted">{{ __('Where Wj is criteria weight and Rij is normalized score') }}</small>
                        </div>

                        <h6 class="text-primary mb-3 mt-4">{{ __('Ranking') }}</h6>
                        <p>{{ __('Employees are ranked based on their final scores from highest to lowest.') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Export Button Enhancements */
.export-buttons {
    border-radius: 0.375rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.export-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    font-weight: 600;
    border: none;
    padding: 0.5rem 1rem;
}

.export-btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.export-btn:hover:before {
    left: 100%;
}

.export-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.export-btn:active {
    transform: translateY(0);
    transition: transform 0.1s;
}

.export-btn[data-format="pdf"]:hover {
    background: linear-gradient(135deg, #dc3545, #bb2d3b);
}

.export-btn[data-format="excel"]:hover {
    background: linear-gradient(135deg, #198754, #157347);
}

.export-btn .fas {
    transition: transform 0.3s ease;
}

.export-btn:hover .fas {
    transform: scale(1.1) rotate(5deg);
}

/* Loading Animation for Export */
.export-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.export-btn.loading .fas {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* DataTable Action Buttons */
.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.btn-group .btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.dropdown-item:hover {
    background: linear-gradient(90deg, #f8f9fa, #e9ecef);
    transform: translateX(5px);
}

.dropdown-item .fas {
    width: 16px;
}

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    color: white;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stats-content {
    position: relative;
    z-index: 2;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.stats-label {
    font-size: 0.875rem;
    font-weight: 500;
    opacity: 0.9;
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.5rem;
    opacity: 0.3;
    z-index: 1;
}

/* Enhanced Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounceIn {
    0%, 20%, 40%, 60%, 80% {
        transform: translateY(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateY(-10px);
    }
}

.animated {
    animation-duration: 0.5s;
    animation-fill-mode: both;
}

.fadeInUp {
    animation-name: fadeInUp;
}

.bounceIn {
    animation-name: bounceIn;
}

/* Period Filter Enhancement */
#periodFilter {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    font-weight: 500;
}

#periodFilter:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    transform: scale(1.02);
}

/* Table Enhancements */
.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: scale(1.01);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Badge Enhancements */
.badge {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
}

/* Card Enhancements */
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.25rem 1.5rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .export-buttons {
        flex-direction: column;
        width: 100%;
    }

    .export-btn {
        margin-bottom: 0.5rem;
        width: 100%;
    }

    .stats-card {
        margin-bottom: 1rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn-sm {
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
}

/* Print Styles */
@media print {
    .export-buttons,
    .btn-group,
    .card-header .btn {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let resultsTable;
let topPerformersChart;
let departmentChart;

$(document).ready(function() {
    @if($periods->count() > 0)
    // Initialize DataTable
    resultsTable = $('#resultsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('results.index') }}",
            data: function (d) {
                d.period = $('#periodFilter').val();
                console.log('DataTable AJAX request with period:', d.period);
            },
            dataSrc: function(json) {
                console.log('DataTable response:', json);
                console.log('Data count:', json.data ? json.data.length : 0);
                return json.data;
            }
        },
        columns: [
            {data: 'ranking_badge', name: 'ranking', orderable: false, searchable: false},
            {data: 'employee_code', name: 'employee.employee_code'},
            {data: 'employee_name', name: 'employee.name'},
            {data: 'department', name: 'employee.department'},
            {data: 'score_percentage', name: 'total_score', searchable: false},
            {data: 'evaluation_period', name: 'evaluation_period'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[4, 'desc']], // Sort by score descending
        pageLength: 25,
        responsive: true,
        language: {
            processing: "{{ __('Processing...') }}",
            search: "{{ __('Search:') }}",
            lengthMenu: "{{ __('Show _MENU_ entries per page') }}",
            info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
            infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
            infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
            loadingRecords: "{{ __('Loading...') }}",
            zeroRecords: "{{ __('No data found') }}",
            emptyTable: "{{ __('No data available in table') }}",
            paginate: {
                first: "{{ __('First') }}",
                previous: "{{ __('Previous') }}",
                next: "{{ __('Next') }}",
                last: "{{ __('Last') }}"
            }
        },
        drawCallback: function() {
            updateStats();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Period filter change
    $('#periodFilter').change(function() {
        const period = $(this).val();

        // Update export button state and tooltips
        if (period) {
            $('.export-btn').removeClass('disabled').prop('disabled', false);
            $('.export-btn[data-format="pdf"]').attr('title', '{{ __("Export comprehensive SAW results as PDF report with rankings, statistics, and criteria information") }}').tooltip('dispose').tooltip();
            $('.export-btn[data-format="excel"]').attr('title', '{{ __("Export detailed SAW results as Excel spreadsheet with performance analysis and statistics") }}').tooltip('dispose').tooltip();

            // Show selection feedback
            showSuccess(`{{ __("Period selected") }}: ${period}. {{ __("Export buttons are now available") }}!`);
        } else {
            $('.export-btn').addClass('disabled').prop('disabled', true);
            $('.export-btn').attr('title', '{{ __("Please select a period first") }}').tooltip('dispose').tooltip();
        }

        resultsTable.ajax.reload();
        updateCharts();
    });

    // Export button enhancements
    $('.export-btn').on('mouseenter', function() {
        const format = $(this).data('format');
        const icon = $(this).find('.fas');

        if (format === 'pdf') {
            icon.removeClass('fa-file-pdf').addClass('fa-file-pdf');
        } else {
            icon.removeClass('fa-file-excel').addClass('fa-file-excel');
        }

        $(this).addClass('animated bounceIn');
    });

    $('.export-btn').on('mouseleave', function() {
        $(this).removeClass('animated bounceIn');
    });

    // Initialize export button state
    const initialPeriod = $('#periodFilter').val();
    if (!initialPeriod) {
        $('.export-btn').addClass('disabled').prop('disabled', true);
        $('.export-btn').attr('title', '{{ __("Please select a period first") }}').tooltip('dispose').tooltip();
    }

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Add keyboard shortcuts for export
    $(document).keydown(function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.which) {
                case 80: // Ctrl+P for PDF
                    e.preventDefault();
                    if (!$('.export-btn[data-format="pdf"]').is(':disabled')) {
                        exportResults('pdf');
                    }
                    break;
                case 69: // Ctrl+E for Excel
                    e.preventDefault();
                    if (!$('.export-btn[data-format="excel"]').is(':disabled')) {
                        exportResults('excel');
                    }
                    break;
            }
        }
    });

    // Add visual feedback for keyboard shortcuts
    $('.export-btn[data-format="pdf"]').append('<small class="d-block" style="font-size: 0.7rem; opacity: 0.7;">Ctrl+P</small>');
    $('.export-btn[data-format="excel"]').append('<small class="d-block" style="font-size: 0.7rem; opacity: 0.7;">Ctrl+E</small>');

    // Initialize charts
    updateCharts();
    @endif
});

function updateStats() {
    // Update stats from current table data
    const data = resultsTable.data();
    const totalEmployees = data.length;
    const topPerformers = data.toArray().filter(row => parseInt(row.ranking_badge.match(/\d+/)[0]) <= 3).length;

    $('#totalEmployees').text(totalEmployees);
    $('#topPerformers').text(topPerformers);

    // Calculate average score
    if (totalEmployees > 0) {
        const totalScore = data.toArray().reduce((sum, row) => {
            const percentage = parseFloat(row.score_percentage.replace('%', ''));
            return sum + percentage;
        }, 0);
        const avgScore = Math.round(totalScore / totalEmployees);
        $('#avgScore').text(avgScore + '%');
    } else {
        $('#avgScore').text('0%');
    }
}

function updateCharts() {
    const period = $('#periodFilter').val();

    // Update top performers chart
    $.get("{{ route('results.chart-data') }}", {
        type: 'top_performers',
        period: period
    }, function(data) {
        updateTopPerformersChart(data);
    });

    // Update department chart
    $.get("{{ route('results.chart-data') }}", {
        type: 'department_comparison',
        period: period
    }, function(data) {
        updateDepartmentChart(data);
    });
}

function updateTopPerformersChart(data) {
    const ctx = document.getElementById('topPerformersChart').getContext('2d');

    if (topPerformersChart) {
        topPerformersChart.destroy();
    }

    topPerformersChart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });
}

function updateDepartmentChart(data) {
    const ctx = document.getElementById('departmentChart').getContext('2d');

    if (departmentChart) {
        departmentChart.destroy();
    }

    departmentChart = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
}

function refreshResults() {
    @if($periods->count() > 0)
    resultsTable.ajax.reload();
    updateCharts();
    @endif
    showSuccess('{{ __("Data refreshed successfully") }}');
}

function generateResults() {
    const period = $('#periodFilter').val();

    if (!period) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Period Required") }}',
            text: '{{ __("Please select a period to generate results") }}',
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    // Prepare confirmation message based on period selection
    const isAllPeriods = period === 'all';
    const confirmTitle = isAllPeriods ? '{{ __("Generate SAW Results for All Periods") }}' : '{{ __("Generate SAW Results") }}';
    const confirmText = isAllPeriods 
        ? '{{ __("Generate SAW calculation results for ALL available periods? This may take longer to complete.") }}'
        : `{{ __("Generate SAW calculation results for period") }} ${period}?`;

    // Show confirmation dialog
    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __("Yes, Generate") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: '{{ route("evaluations.generate-results") }}',
                type: 'POST',
                data: {
                    evaluation_period: period,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json'
            }).then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Generation failed');
                }
                return response;
            }).catch(error => {
                console.error('Generation error:', error);
                Swal.showValidationMessage(
                    error.responseJSON?.message || error.message || 'Generation failed'
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const response = result.value;
            
            // Handle different response types for single period vs all periods
            if (response.periods_processed !== undefined) {
                // All periods response
                let detailsHtml = `
                    <div class="text-start">
                        <p class="mb-2">${response.message}</p>
                        <ul class="list-unstyled mb-2">
                            <li><i class="fas fa-check-circle text-success me-2"></i><strong>{{ __("Periods Processed") }}:</strong> ${response.periods_processed}/${response.total_periods}</li>
                        </ul>
                `;
                
                if (response.errors && Object.keys(response.errors).length > 0) {
                    detailsHtml += `<div class="alert alert-warning mt-2"><small><strong>{{ __("Periods with errors") }}:</strong><br>`;
                    Object.keys(response.errors).forEach(period => {
                        detailsHtml += `• ${period}<br>`;
                    });
                    detailsHtml += `</small></div>`;
                }
                
                detailsHtml += `</div>`;
                
                Swal.fire({
                    icon: response.success ? 'success' : 'warning',
                    title: response.success ? '{{ __("All Periods Processed") }}' : '{{ __("Partially Completed") }}',
                    html: detailsHtml,
                    confirmButtonText: '{{ __("OK") }}',
                    confirmButtonColor: '#198754'
                });
            } else {
                // Single period response
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("Success") }}',
                    text: response.message,
                    confirmButtonText: '{{ __("OK") }}',
                    confirmButtonColor: '#198754'
                });
            }
            
            // Refresh the results table and charts
            @if($periods->count() > 0)
            resultsTable.ajax.reload();
            updateCharts();
            @endif
        }
    });
}

function showSuccess(message) {
    if (window.utils?.showSuccessToast) {
        window.utils.showSuccessToast(message);
    } else {
        // Fallback to SweetAlert if utils is not available
        Swal.fire({
            icon: 'success',
            title: '{{ __("Success") }}',
            text: message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

function exportResults(format) {
    const period = $('#periodFilter').val();

    if (!period) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Period Required") }}',
            text: '{{ __("Please select a period to export") }}',
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#0d6efd',
            customClass: {
                popup: 'animated bounceIn'
            }
        });
        return;
    }

    // Check if there's data for the selected period
    const tableData = resultsTable.data();
    if (tableData.length === 0) {
        Swal.fire({
            icon: 'info',
            title: '{{ __("No Data Available") }}',
            text: `{{ __("No evaluation results found for period") }} ${period}`,
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    // Show confirmation with details
    const formatIcon = format === 'pdf' ? 'fa-file-pdf' : 'fa-file-excel';
    const formatColor = format === 'pdf' ? '#dc3545' : '#198754';

    Swal.fire({
        title: `{{ __("Export Results as") }} ${format.toUpperCase()}`,
        html: `
            <div class="text-start">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas ${formatIcon} fa-2x me-3" style="color: ${formatColor}"></i>
                    <div>
                        <h6 class="mb-1">{{ __("Export Configuration") }}</h6>
                        <small class="text-muted">{{ __("SAW Evaluation Results") }}</small>
                    </div>
                </div>
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-calendar text-primary me-2"></i><strong>{{ __("Period") }}:</strong> ${period}</li>
                    <li><i class="fas fa-users text-success me-2"></i><strong>{{ __("Total Employees") }}:</strong> ${tableData.length}</li>
                    <li><i class="fas fa-file-${format === 'pdf' ? 'pdf' : 'excel'} me-2"></i><strong>{{ __("Format") }}:</strong> ${format.toUpperCase()}</li>
                    <li><i class="fas fa-clock text-info me-2"></i><strong>{{ __("Generated") }}:</strong> ${new Date().toLocaleString()}</li>
                </ul>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `<i class="fas fa-download me-1"></i>{{ __("Download") }} ${format.toUpperCase()}`,
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: formatColor,
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'animated fadeInDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performExport(format, period);
        }
    });
}

function performExport(format, period) {
    // Add loading state to the clicked button
    const targetBtn = $(`.export-btn[data-format="${format}"]`);
    const originalContent = targetBtn.html();

    targetBtn.addClass('loading').prop('disabled', true);
    targetBtn.html(`<i class="fas fa-spinner fa-spin me-1"></i><span class="btn-text">Generating...</span>`);

    // Show detailed loading indicator
    const loadingMsg = format === 'pdf' ? '{{ __("Generating PDF Report...") }}' : '{{ __("Generating Excel Spreadsheet...") }}';
    const loadingToast = Swal.fire({
        title: loadingMsg,
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-2">{{ __("Processing SAW calculation results...") }}</p>
                <small class="text-muted">{{ __("This may take a few moments") }}</small>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        customClass: {
            popup: 'animated pulse'
        },
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = format === 'pdf'
        ? "{{ route('results.export-pdf') }}"
        : "{{ route('results.export-excel') }}";

    // Enhanced error handling with fetch
    const exportUrl = url + '?period=' + encodeURIComponent(period);

    fetch(exportUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': format === 'pdf' ? 'application/pdf' : 'application/vnd.ms-excel'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Try to get error message from response
            return response.text().then(text => {
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = JSON.parse(text);
                    errorMessage = errorData.message || errorMessage;
                } catch (e) {
                    // If not JSON, use the text as error message
                    errorMessage = text || errorMessage;
                }
                throw new Error(errorMessage);
            });
        }

        // Check if response is actually a PDF/Excel file
        const contentType = response.headers.get('content-type');
        if (format === 'pdf' && !contentType.includes('application/pdf')) {
            throw new Error('Invalid PDF response received');
        }
        if (format === 'excel' && !contentType.includes('application/vnd.ms-excel')) {
            throw new Error('Invalid Excel response received');
        }

        return response.blob();
    })
    .then(blob => {
        // Create download link
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `hasil-ranking-saw-${period}-${new Date().toISOString().slice(0,19).replace(/[:]/g, '-')}.${format === 'pdf' ? 'pdf' : 'xls'}`;

        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Clean up
        window.URL.revokeObjectURL(downloadUrl);

        // Restore button state
        targetBtn.removeClass('loading').prop('disabled', false);
        targetBtn.html(originalContent);

        // Close loading and show success
        setTimeout(() => {
            loadingToast.close();
            Swal.fire({
                icon: 'success',
                title: '{{ __("Export Successful!") }}',
                html: `
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="mb-2"><strong>{{ __("SAW results exported successfully") }}</strong></p>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __("File downloaded as") }}: <strong>${format.toUpperCase()}</strong><br>
                                {{ __("Period") }}: <strong>${period}</strong><br>
                                {{ __("Generated") }}: <strong>${new Date().toLocaleString()}</strong>
                            </small>
                        </div>
                    </div>
                `,
                confirmButtonText: '{{ __("Great!") }}',
                confirmButtonColor: '#198754',
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    popup: 'animated bounceIn'
                }
            });
        }, 1500);
    })
    .catch(error => {
        console.error('Export error:', error);

        // Restore button state
        targetBtn.removeClass('loading').prop('disabled', false);
        targetBtn.html(originalContent);

        loadingToast.close();

        Swal.fire({
            icon: 'error',
            title: '{{ __("Export Failed") }}',
            html: `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p class="mb-2">{{ __("Failed to export SAW results") }}</p>
                    <div class="alert alert-danger">
                        <small>
                            <strong>{{ __("Possible causes") }}:</strong><br>
                            • {{ __("No data available for selected period") }}<br>
                            • {{ __("Server connection issue") }}<br>
                            • {{ __("File generation error") }}
                        </small>
                    </div>
                    <p class="small text-muted">{{ __("Please try again or contact administrator") }}</p>
                </div>
            `,
            confirmButtonText: '{{ __("Try Again") }}',
            confirmButtonColor: '#dc3545',
            showCancelButton: true,
            cancelButtonText: format === 'pdf' ? '{{ __("Try Simple Method") }}' : '{{ __("Close") }}',
            cancelButtonColor: format === 'pdf' ? '#ffc107' : '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                exportResults(format);
            } else if (result.dismiss === Swal.DismissReason.cancel && format === 'pdf') {
                exportResultsFallback(format);
            }
        });
    });
}

function showCalculationInfo() {
    $('#calculationModal').modal('show');
}

function exportResultsFallback(format) {
    const period = $('#periodFilter').val();

    if (!period) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Period Required") }}',
            text: '{{ __("Please select a period to export") }}',
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    // Show loading
    Swal.fire({
        title: '{{ __("Trying Fallback Method...") }}',
        html: `
            <div class="text-center">
                <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-2">{{ __("Using alternative PDF generation method...") }}</p>
                <small class="text-muted">{{ __("This may take a moment") }}</small>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });

    const fallbackUrl = "{{ route('results.export-pdf-simple') }}" + '?period=' + encodeURIComponent(period);

    // Direct download using window.location for simple method
    window.location.href = fallbackUrl;

    // Close loading after delay
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            icon: 'info',
            title: '{{ __("Download Started") }}',
            text: '{{ __("PDF download should start shortly using fallback method") }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }, 2000);
}

function debugPdfTemplate() {
    const period = $('#periodFilter').val();

    if (!period) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Period Required") }}',
            text: '{{ __("Please select a period to debug PDF template") }}',
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const debugUrl = "{{ route('results.debug-pdf') }}" + '?period=' + encodeURIComponent(period);

    Swal.fire({
        title: '{{ __("Debug PDF Template") }}',
        html: `
            <div class="text-start">
                <p class="mb-3">{{ __("This will open the PDF template as HTML to check for issues.") }}</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>{{ __("Period") }}:</strong> ${period}<br>
                    <strong>{{ __("Action") }}:</strong> {{ __("View HTML template") }}
                </div>
                <p class="small text-muted">{{ __("This helps identify any rendering issues before PDF conversion.") }}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-external-link-alt me-1"></i>{{ __("Open Debug View") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Open debug template in new tab
            window.open(debugUrl, '_blank');

            // Show success message
            Swal.fire({
                icon: 'success',
                title: '{{ __("Debug View Opened") }}',
                text: '{{ __("Check the new tab to review the HTML template") }}',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush
