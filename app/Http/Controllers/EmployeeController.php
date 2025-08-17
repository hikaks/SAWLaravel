<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
// Excel export temporarily disabled - not compatible with Laravel 12
// use App\Exports\EmployeesExport;
// use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle filter data requests
        if ($request->has('get_departments')) {
            $departments = Employee::distinct('department')
                ->whereNotNull('department')
                ->pluck('department')
                ->sort()
                ->values();
            return response()->json(['departments' => $departments]);
        }

        if ($request->has('get_positions')) {
            $positions = Employee::distinct('position')
                ->whereNotNull('position')
                ->pluck('position')
                ->sort()
                ->values();
            return response()->json(['positions' => $positions]);
        }

        if ($request->has('get_stats')) {
            $stats = [
                'total' => Employee::count(),
                'evaluated' => Employee::whereHas('evaluationResults')->count(),
                'departments' => Employee::distinct('department')->count(),
                'active' => Employee::count(), // Assuming all employees are active
            ];
            return response()->json(['stats' => $stats]);
        }

        // Handle deleted employees request
        if ($request->has('get_deleted')) {
            $deletedEmployees = Employee::onlyTrashed()
                ->select('id', 'employee_code', 'name', 'position', 'department', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'name' => $employee->name,
                        'position' => $employee->position,
                        'department' => $employee->department,
                        'deleted_at' => $employee->deleted_at->format('d M Y H:i')
                    ];
                });

            return response()->json(['deleted_employees' => $deletedEmployees]);
        }

        if ($request->ajax()) {
            $query = Employee::with(['evaluations', 'evaluationResults']);

            // Apply filters
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            if ($request->filled('position')) {
                $query->where('position', $request->position);
            }

            if ($request->filled('evaluation_status')) {
                if ($request->evaluation_status === 'evaluated') {
                    $query->whereHas('evaluationResults');
                } elseif ($request->evaluation_status === 'not_evaluated') {
                    $query->whereDoesntHave('evaluationResults');
                }
            }

            $employees = $query->get();

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('latest_evaluation', function($employee) {
                    $latestResult = $employee->latestResult();
                    return $latestResult ? $latestResult->evaluation_period : 'Belum ada';
                })
                ->addColumn('latest_ranking', function($employee) {
                    $latestResult = $employee->latestResult();
                    return $latestResult ? $latestResult->ranking : '-';
                })
                ->addColumn('action', function($employee) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="'.route('employees.show', $employee->id).'" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="'.route('employees.edit', $employee->id).'" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteEmployee('.$employee->id.')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('employees.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        try {
            $employee = Employee::create($request->validated());

            // Invalidate related caches
            $this->cacheService->invalidateEmployeeData('all');
            $this->cacheService->invalidateDashboard();

            return redirect()->route('employees.index')
                ->with('success', "Karyawan {$employee->name} ({$employee->employee_code}) berhasil ditambahkan!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan karyawan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['evaluations.criteria', 'evaluationResults']);

        // Group evaluations by period
        $evaluationsByPeriod = $employee->evaluations->groupBy('evaluation_period');

        return view('employees.show', compact('employee', 'evaluationsByPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $employee->update($request->validated());

            // Invalidate related caches
            $this->cacheService->invalidateEmployeeData('all');
            $this->cacheService->invalidateDashboard();

            return redirect()->route('employees.index')
                ->with('success', "Data karyawan {$employee->name} ({$employee->employee_code}) berhasil diperbarui!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data karyawan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            DB::transaction(function () use ($employee) {
                // Soft delete related evaluations and results
                $employee->evaluations()->delete();
                $employee->evaluationResults()->delete();
                $employee->delete(); // This will now be soft delete
            });

            return response()->json([
                'success' => true,
                'message' => 'Employee successfully deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted employees.
     */
    public function restore(Request $request)
    {
        try {
            $employeeIds = $request->input('employee_ids', []);

            if (empty($employeeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees selected for restoration.'
                ], 400);
            }

            $restoredCount = 0;
            $failedIds = [];

            foreach ($employeeIds as $id) {
                try {
                    $employee = Employee::onlyTrashed()->find($id);
                    if ($employee) {
                        $employee->restore();
                        $restoredCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted employees list
            $deletedEmployees = Employee::onlyTrashed()
                ->select('id', 'employee_code', 'name', 'position', 'department', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'name' => $employee->name,
                        'position' => $employee->position,
                        'department' => $employee->department,
                        'deleted_at' => $employee->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully restored {$restoredCount} employee(s).";
            if (!empty($failedIds)) {
                $message .= " Failed to restore " . count($failedIds) . " employee(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'restored_count' => $restoredCount,
                'failed_count' => count($failedIds),
                'deleted_employees' => $deletedEmployees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete employees.
     */
    public function forceDelete(Request $request)
    {
        try {
            $employeeIds = $request->input('employee_ids', []);

            if (empty($employeeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees selected for permanent deletion.'
                ], 400);
            }

            $deletedCount = 0;
            $failedIds = [];

            foreach ($employeeIds as $id) {
                try {
                    $employee = Employee::onlyTrashed()->find($id);
                    if ($employee) {
                        // Permanently delete related data first
                        $employee->evaluations()->forceDelete();
                        $employee->evaluationResults()->forceDelete();
                        $employee->forceDelete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted employees list
            $deletedEmployees = Employee::onlyTrashed()
                ->select('id', 'employee_code', 'name', 'position', 'department', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'name' => $employee->name,
                        'position' => $employee->position,
                        'department' => $employee->department,
                        'deleted_at' => $employee->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully permanently deleted {$deletedCount} employee(s).";
            if (!empty($failedIds)) {
                $message .= " Failed to delete " . count($failedIds) . " employee(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'failed_count' => count($failedIds),
                'deleted_employees' => $deletedEmployees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees by department for filtering
     */
    public function getByDepartment(Request $request)
    {
        $department = $request->get('department');

        $employees = Employee::when($department, function($query) use ($department) {
            return $query->where('department', $department);
        })->get(['id', 'name', 'employee_code', 'position']);

        return response()->json($employees);
    }

    /**
     * Get unique departments
     */
    public function getDepartments()
    {
        $departments = Employee::distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();

        return response()->json($departments);
    }

    /**
     * Export employees - general export method
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');

        switch ($format) {
            case 'excel':
                return $this->exportExcel($request);
            case 'pdf':
            default:
                return $this->exportPdf($request);
        }
    }

    /**
     * Export employees to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Employee::query();

            // Apply filters
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            if ($request->filled('position')) {
                $query->where('position', $request->position);
            }

            $employees = $query->orderBy('employee_code')->get();

            $filters = [];
            if ($request->filled('department')) {
                $filters[] = 'Department: ' . $request->department;
            }
            if ($request->filled('position')) {
                $filters[] = 'Posisi: ' . $request->position;
            }

            $filtersText = empty($filters) ? 'Semua Data' : implode(', ', $filters);

            $pdf = Pdf::loadView('exports.pdf.employees', [
                'employees' => $employees,
                'filters' => $filtersText
            ]);

            $filename = 'daftar-karyawan-' . date('Y-m-d-His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengexport PDF: ' . $e->getMessage());
        }
    }

        /**
     * Export employees to Excel
     */
    public function exportExcel(Request $request)
    {
        // Excel export temporarily disabled - package compatibility issue with Laravel 12
        return redirect()->back()
            ->with('info', 'Excel export sedang dalam development. Silakan gunakan export PDF.');
    }
}
