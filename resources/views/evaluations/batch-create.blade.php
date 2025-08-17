@extends('layouts.main')

@section('title', 'Batch Evaluation Input - SAW Employee Evaluation')
@section('page-title', 'Batch Employee Evaluation Input')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users-cog text-primary me-2"></i>
                        Batch Evaluation Input Form
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllEmployees">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllEmployees">
                            <i class="fas fa-square me-1"></i>Clear All
                        </button>
                        <a href="{{ route('evaluations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('evaluations.batch-store') }}" method="POST" id="batchEvaluationForm">
                    @csrf
                    
                    <!-- Evaluation Period -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="evaluation_period" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Evaluation Period <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('evaluation_period') is-invalid @enderror" 
                                   id="evaluation_period" 
                                   name="evaluation_period" 
                                   value="{{ old('evaluation_period', date('Y-m')) }}" 
                                   placeholder="YYYY-MM (e.g., 2024-01)"
                                   required>
                            <div class="form-text">Format: YYYY-MM (contoh: 2024-01)</div>
                            @error('evaluation_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-cog me-1"></i>Options
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overwrite_existing" name="overwrite_existing" value="1">
                                <label class="form-check-label" for="overwrite_existing">
                                    Overwrite existing evaluations
                                </label>
                            </div>
                            <div class="form-text">Check this to update existing evaluations for the same period</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Quick Fill
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="quickFillScore" min="1" max="100" placeholder="Score">
                                <button type="button" class="btn btn-outline-primary" id="applyQuickFill">
                                    <i class="fas fa-fill me-1"></i>Apply to Selected
                                </button>
                            </div>
                            <div class="form-text">Fill all selected cells with the same score</div>
                        </div>
                    </div>

                    <!-- Criteria Headers -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="batchEvaluationTable">
                            <thead class="table-primary">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                    </th>
                                    <th width="200">Employee</th>
                                    <th width="120">Department</th>
                                    @foreach($criterias as $criteria)
                                        <th class="text-center" width="120">
                                            <div class="criteria-header">
                                                <strong>{{ $criteria->name }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Weight: {{ $criteria->weight }}% 
                                                    <span class="badge bg-{{ $criteria->type === 'benefit' ? 'success' : 'warning' }} ms-1">
                                                        {{ ucfirst($criteria->type) }}
                                                    </span>
                                                </small>
                                            </div>
                                        </th>
                                    @endforeach
                                    <th width="100" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $index => $employee)
                                    <tr class="employee-row" data-employee-id="{{ $employee->id }}">
                                        <td class="text-center">
                                            <input type="checkbox" 
                                                   class="form-check-input employee-checkbox" 
                                                   name="selected_employees[]" 
                                                   value="{{ $employee->id }}"
                                                   id="employee_{{ $employee->id }}">
                                        </td>
                                        <td>
                                            <div class="employee-info">
                                                <strong>{{ $employee->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $employee->employee_code }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $employee->department ?? 'N/A' }}</span>
                                        </td>
                                        @foreach($criterias as $criteria)
                                            <td class="text-center">
                                                <input type="number" 
                                                       class="form-control form-control-sm score-input" 
                                                       name="evaluations[{{ $index }}][scores][{{ $criteria->id }}]" 
                                                       min="1" 
                                                       max="100" 
                                                       placeholder="1-100"
                                                       data-employee-id="{{ $employee->id }}"
                                                       data-criteria-id="{{ $criteria->id }}"
                                                       style="width: 80px;">
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm fill-row-btn" data-employee-id="{{ $employee->id }}">
                                                <i class="fas fa-fill"></i>
                                            </button>
                                        </td>
                                        
                                        <!-- Hidden employee_id for form submission -->
                                        <input type="hidden" name="evaluations[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Information -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle text-info me-2"></i>Evaluation Summary
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Total Employees:</strong> <span id="totalEmployees">{{ $employees->count() }}</span></li>
                                        <li><strong>Total Criteria:</strong> <span id="totalCriteria">{{ $criterias->count() }}</span></li>
                                        <li><strong>Selected Employees:</strong> <span id="selectedCount">0</span></li>
                                        <li><strong>Total Evaluations:</strong> <span id="totalEvaluations">0</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>Instructions
                                    </h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li><i class="fas fa-check text-success me-1"></i>Select employees to evaluate</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Enter scores (1-100) for each criteria</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Use Quick Fill for bulk scoring</li>
                                        <li><i class="fas fa-check text-success me-1"></i>Review before submitting</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-outline-secondary me-2" id="previewBtn">
                                <i class="fas fa-eye me-1"></i>Preview Selected
                            </button>
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Save Batch Evaluations
                            </button>
                            <button type="button" class="btn btn-outline-warning me-2" id="resetBtn">
                                <i class="fas fa-redo me-1"></i>Reset Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Evaluation Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#batchEvaluationForm').submit()">
                    <i class="fas fa-save me-1"></i>Confirm & Save
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .score-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .employee-row.selected {
        background-color: #f8f9ff;
    }
    
    .criteria-header {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    
    .table th {
        vertical-align: middle;
        text-align: center;
    }
    
    .employee-info strong {
        font-size: 0.9rem;
    }
    
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let totalEmployees = {{ $employees->count() }};
    let totalCriteria = {{ $criterias->count() }};
    
    // Update counters
    function updateCounters() {
        let selectedEmployees = $('.employee-checkbox:checked').length;
        let totalEvaluations = selectedEmployees * totalCriteria;
        
        $('#selectedCount').text(selectedEmployees);
        $('#totalEvaluations').text(totalEvaluations);
    }
    
    // Select/Deselect all employees
    $('#selectAllCheckbox').change(function() {
        $('.employee-checkbox').prop('checked', this.checked);
        $('.employee-row').toggleClass('selected', this.checked);
        updateCounters();
    });
    
    $('#selectAllEmployees').click(function() {
        $('.employee-checkbox').prop('checked', true);
        $('.employee-row').addClass('selected');
        $('#selectAllCheckbox').prop('checked', true);
        updateCounters();
    });
    
    $('#clearAllEmployees').click(function() {
        $('.employee-checkbox').prop('checked', false);
        $('.employee-row').removeClass('selected');
        $('#selectAllCheckbox').prop('checked', false);
        updateCounters();
    });
    
    // Individual employee selection
    $('.employee-checkbox').change(function() {
        $(this).closest('.employee-row').toggleClass('selected', this.checked);
        
        // Update select all checkbox
        let allChecked = $('.employee-checkbox:checked').length === $('.employee-checkbox').length;
        $('#selectAllCheckbox').prop('checked', allChecked);
        
        updateCounters();
    });
    
    // Quick fill functionality
    $('#applyQuickFill').click(function() {
        let score = $('#quickFillScore').val();
        if (score && score >= 1 && score <= 100) {
            $('.employee-row.selected .score-input').val(score);
        } else {
            alert('Please enter a valid score (1-100)');
        }
    });
    
    // Fill row functionality
    $('.fill-row-btn').click(function() {
        let employeeId = $(this).data('employee-id');
        let score = prompt('Enter score to fill for this employee (1-100):');
        
        if (score && score >= 1 && score <= 100) {
            $(`.score-input[data-employee-id="${employeeId}"]`).val(score);
        }
    });
    
    // Preview functionality
    $('#previewBtn').click(function() {
        let selectedEmployees = [];
        let hasData = false;
        
        $('.employee-row.selected').each(function() {
            let employeeId = $(this).data('employee-id');
            let employeeName = $(this).find('.employee-info strong').text();
            let scores = {};
            let hasScores = false;
            
            $(this).find('.score-input').each(function() {
                let criteriaId = $(this).data('criteria-id');
                let score = $(this).val();
                if (score) {
                    scores[criteriaId] = score;
                    hasScores = true;
                    hasData = true;
                }
            });
            
            if (hasScores) {
                selectedEmployees.push({
                    id: employeeId,
                    name: employeeName,
                    scores: scores
                });
            }
        });
        
        if (!hasData) {
            alert('Please select employees and enter scores before previewing.');
            return;
        }
        
        // Generate preview content
        let previewHtml = `
            <div class="alert alert-info">
                <strong>Evaluation Period:</strong> ${$('#evaluation_period').val()}<br>
                <strong>Selected Employees:</strong> ${selectedEmployees.length}<br>
                <strong>Overwrite Existing:</strong> ${$('#overwrite_existing').is(':checked') ? 'Yes' : 'No'}
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Employee</th>
                            @foreach($criterias as $criteria)
                            <th class="text-center">{{ $criteria->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        selectedEmployees.forEach(employee => {
            previewHtml += `<tr><td>${employee.name}</td>`;
            @foreach($criterias as $criteria)
            previewHtml += `<td class="text-center">${employee.scores[{{ $criteria->id }}] || '-'}</td>`;
            @endforeach
            previewHtml += `</tr>`;
        });
        
        previewHtml += `</tbody></table></div>`;
        
        $('#previewContent').html(previewHtml);
        $('#previewModal').modal('show');
    });
    
    // Form validation
    $('#batchEvaluationForm').submit(function(e) {
        let hasSelectedEmployees = $('.employee-checkbox:checked').length > 0;
        let hasScores = false;
        
        $('.employee-row.selected .score-input').each(function() {
            if ($(this).val()) {
                hasScores = true;
                return false;
            }
        });
        
        if (!hasSelectedEmployees) {
            e.preventDefault();
            alert('Please select at least one employee.');
            return false;
        }
        
        if (!hasScores) {
            e.preventDefault();
            alert('Please enter scores for selected employees.');
            return false;
        }
        
        if (!$('#evaluation_period').val()) {
            e.preventDefault();
            alert('Please enter evaluation period.');
            return false;
        }
        
        // Show loading
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...').prop('disabled', true);
    });
    
    // Reset form
    $('#resetBtn').click(function() {
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            $('#batchEvaluationForm')[0].reset();
            $('.employee-checkbox').prop('checked', false);
            $('.employee-row').removeClass('selected');
            $('#selectAllCheckbox').prop('checked', false);
            updateCounters();
        }
    });
    
    // Score input validation
    $('.score-input').on('input', function() {
        let value = parseInt($(this).val());
        if (value < 1 || value > 100) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Initialize counters
    updateCounters();
});
</script>
@endpush