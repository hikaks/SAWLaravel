@extends('layouts.main')

@section('title', __('Batch Evaluation') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Batch Evaluation'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Batch Evaluation') }}</h1>
        <p class="text-gray-600">{{ __('Evaluate multiple employees for the same period efficiently') }}</p>
    </div>
    <x-ui.button href="{{ route('evaluations.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="max-w-6xl mx-auto">
    <!-- Period Selection -->
    <div class="card mb-6">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-calendar text-primary-500"></i>{{ __('Evaluation Period') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label for="evaluation_period" class="form-label">{{ __('Period') }} <span class="text-danger-500">*</span></label>
                    <input type="text" class="form-control" id="evaluation_period" placeholder="{{ __('e.g., 2024-Q1, January 2024') }}" required>
                </div>
                <div class="form-group">
                    <label for="department_filter" class="form-label">{{ __('Filter by Department') }}</label>
                    <select class="form-select" id="department_filter">
                        <option value="">{{ __('All Departments') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Actions') }}</label>
                    <div class="flex gap-2">
                        <x-ui.button onclick="loadEmployees()" variant="primary" size="sm" class="flex-1">
                            {{ __('Load Employees') }}
                        </x-ui.button>
                        <x-ui.button onclick="selectAll()" variant="outline-secondary" size="sm">
                            {{ __('Select All') }}
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Selection -->
    <div class="card mb-6" id="employeeSelectionCard" style="display: none;">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-users text-primary-500"></i>{{ __('Select Employees') }}
                </h6>
                <span class="badge badge-primary" id="selectedCount">0 {{ __('selected') }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="employeeGrid">
                <!-- Employees will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Criteria Scoring -->
    <div class="card mb-6" id="criteriaCard" style="display: none;">
        <div class="card-header">
            <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                <i class="fas fa-sliders text-primary-500"></i>{{ __('Criteria Scoring') }}
            </h6>
        </div>
        <div class="card-body">
            <form id="batchEvaluationForm">
                <div class="space-y-6" id="criteriaContainer">
                    @foreach($criterias as $criteria)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h6 class="font-semibold text-gray-900">{{ $criteria->name }}</h6>
                                <p class="text-sm text-gray-600">{{ $criteria->description }}</p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="badge {{ $criteria->type === 'benefit' ? 'badge-success' : 'badge-warning' }}">
                                        <i class="fas fa-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ $criteria->type === 'benefit' ? __('Benefit') : __('Cost') }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ __('Weight') }}: <span class="font-semibold">{{ $criteria->weight }}%</span></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">{{ __('Score for all selected employees') }}</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="criteria_{{ $criteria->id }}_score" 
                                       min="0" 
                                       step="0.01" 
                                       placeholder="{{ __('Enter score') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Apply to') }}</label>
                                <div class="flex gap-2">
                                    <x-ui.button type="button" onclick="applyToAll({{ $criteria->id }})" variant="outline-primary" size="sm">
                                        {{ __('All Selected') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" onclick="applyByDepartment({{ $criteria->id }})" variant="outline-secondary" size="sm">
                                        {{ __('By Department') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Individual scores will be generated here -->
                        <div id="individual_scores_{{ $criteria->id }}" class="mt-4" style="display: none;">
                            <!-- Individual employee scores -->
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200 mt-8">
                    <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">
                        {{ __('Cancel') }}
                    </x-ui.button>
                    <x-ui.button type="button" onclick="previewResults()" variant="outline-info">
                        {{ __('Preview Results') }}
                    </x-ui.button>
                    <x-ui.button type="button" onclick="submitBatchEvaluation()" variant="primary">
                        <i class="fas fa-save mr-2"></i>{{ __('Save Batch Evaluation') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedEmployees = [];
let employees = [];

function loadEmployees() {
    const period = document.getElementById('evaluation_period').value;
    const department = document.getElementById('department_filter').value;
    
    if (!period.trim()) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Warning") }}',
            text: '{{ __("Please enter evaluation period first") }}'
        });
        return;
    }
    
    const params = new URLSearchParams({ period, department });
    
    fetch(`{{ route('employees.index') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            employees = data.data || [];
            renderEmployeeGrid();
            document.getElementById('employeeSelectionCard').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: '{{ __("Failed to load employees") }}'
            });
        });
}

function renderEmployeeGrid() {
    const grid = document.getElementById('employeeGrid');
    grid.innerHTML = '';
    
    employees.forEach(employee => {
        const div = document.createElement('div');
        div.className = 'p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors';
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       class="form-check-input employee-checkbox" 
                       value="${employee.id}" 
                       onchange="updateSelectedEmployees()">
                <div class="flex-1">
                    <div class="font-medium text-gray-900">${employee.name}</div>
                    <div class="text-sm text-gray-500">${employee.employee_code} • ${employee.department}</div>
                    <div class="text-sm text-gray-500">${employee.position}</div>
                </div>
            </div>
        `;
        grid.appendChild(div);
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
    
    updateSelectedEmployees();
}

function updateSelectedEmployees() {
    selectedEmployees = [];
    document.querySelectorAll('.employee-checkbox:checked').forEach(cb => {
        selectedEmployees.push(parseInt(cb.value));
    });
    
    document.getElementById('selectedCount').textContent = `${selectedEmployees.length} {{ __('selected') }}`;
    
    if (selectedEmployees.length > 0) {
        document.getElementById('criteriaCard').style.display = 'block';
        generateIndividualScoreInputs();
    } else {
        document.getElementById('criteriaCard').style.display = 'none';
    }
}

function generateIndividualScoreInputs() {
    @foreach($criterias as $criteria)
    const container{{ $criteria->id }} = document.getElementById('individual_scores_{{ $criteria->id }}');
    container{{ $criteria->id }}.innerHTML = '';
    container{{ $criteria->id }}.style.display = 'block';
    
    selectedEmployees.forEach(employeeId => {
        const employee = employees.find(e => e.id === employeeId);
        if (employee) {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded mb-2';
            div.innerHTML = `
                <div class="flex-1">
                    <span class="font-medium">${employee.name}</span>
                    <span class="text-sm text-gray-500 ml-2">(${employee.employee_code})</span>
                </div>
                <div class="w-32">
                    <input type="number" 
                           class="form-control form-control-sm" 
                           name="scores[${employeeId}][{{ $criteria->id }}]" 
                           min="0" 
                           step="0.01" 
                           placeholder="Score">
                </div>
            `;
            container{{ $criteria->id }}.appendChild(div);
        }
    });
    @endforeach
}

function applyToAll(criteriaId) {
    const score = document.getElementById(`criteria_${criteriaId}_score`).value;
    if (!score) {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Warning") }}',
            text: '{{ __("Please enter a score first") }}'
        });
        return;
    }
    
    document.querySelectorAll(`input[name*="[${criteriaId}]"]`).forEach(input => {
        input.value = score;
    });
}

function submitBatchEvaluation() {
    const formData = new FormData(document.getElementById('batchEvaluationForm'));
    formData.append('evaluation_period', document.getElementById('evaluation_period').value);
    formData.append('selected_employees', JSON.stringify(selectedEmployees));
    
    fetch('{{ route("evaluations.batch-store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '{{ __("Success") }}',
                text: data.message
            }).then(() => {
                window.location.href = '{{ route("evaluations.index") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: '{{ __("An error occurred while saving") }}'
        });
    });
}

function previewResults() {
    // Implementation for preview
    Swal.fire({
        icon: 'info',
        title: '{{ __("Preview") }}',
        text: '{{ __("Preview functionality will be implemented") }}'
    });
}
</script>
@endpush