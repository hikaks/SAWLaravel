<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Services\SAWCalculationService;
use App\Services\CacheService;
use App\Jobs\ProcessSAWCalculationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EvaluationController extends Controller
{
    protected $sawService;
    protected $cacheService;

    public function __construct(SAWCalculationService $sawService, CacheService $cacheService)
    {
        $this->sawService = $sawService;
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle deleted evaluations request
        if ($request->has('get_deleted')) {
            $deletedEvaluations = Evaluation::onlyTrashed()
                ->with(['employee', 'criteria'])
                ->select('id', 'employee_id', 'criteria_id', 'score', 'evaluation_period', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($evaluation) {
                    return [
                        'id' => $evaluation->id,
                        'employee_name' => $evaluation->employee->name ?? 'Unknown',
                        'employee_code' => $evaluation->employee->employee_code ?? '-',
                        'criteria_name' => $evaluation->criteria->name ?? 'Unknown',
                        'score' => $evaluation->score,
                        'evaluation_period' => $evaluation->evaluation_period,
                        'deleted_at' => $evaluation->deleted_at->format('d M Y H:i')
                    ];
                });

            return response()->json(['deleted_evaluations' => $deletedEvaluations]);
        }

        if ($request->ajax()) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $query = Evaluation::with([
                    'employee:id,name,employee_code,department', 
                    'criteria:id,name,weight,type'
                ])
                ->select('id', 'employee_id', 'criteria_id', 'score', 'evaluation_period', 'created_at')
                ->when($request->period, function($query) use ($request) {
                    return $query->where('evaluation_period', $request->period);
                })
                ->when($request->employee_id, function($query) use ($request) {
                    return $query->where('employee_id', $request->employee_id);
                })
                ->when($request->criteria_id, function($query) use ($request) {
                    return $query->where('criteria_id', $request->criteria_id);
                })
                ->when($request->score_range, function($query) use ($request) {
                    switch($request->score_range) {
                        case 'excellent':
                            return $query->where('score', '>=', 90);
                        case 'good':
                            return $query->whereBetween('score', [80, 89]);
                        case 'average':
                            return $query->whereBetween('score', [70, 79]);
                        case 'poor':
                            return $query->where('score', '<', 70);
                        default:
                            return $query;
                    }
                })
                ->latest();

            $evaluations = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data for pagination
            $formattedData = $evaluations->getCollection()->map(function($evaluation, $index) use ($evaluations, $page) {
                $scoreClass = $evaluation->score >= 80 ? 'success' :
                             ($evaluation->score >= 60 ? 'warning' : 'danger');

                return [
                    'DT_RowIndex' => (($page - 1) * $evaluations->perPage()) + $index + 1,
                    'evaluation_period' => $evaluation->evaluation_period,
                    'employee_name' => $evaluation->employee->name ?? '-',
                    'employee_code' => $evaluation->employee->employee_code ?? '-',
                    'criteria_name' => $evaluation->criteria->name ?? '-',
                    'criteria_weight' => ($evaluation->criteria->weight ?? 0) . '%',
                    'score_badge' => '<span class="badge bg-'.$scoreClass.'">'.$evaluation->score.'</span>',
                    'created_at' => $evaluation->created_at->format('d M Y H:i'),
                    'action' => '<div class="dropdown">' .
                        '<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">' .
                            '<i class="fas fa-ellipsis-v"></i>' .
                        '</button>' .
                        '<ul class="dropdown-menu dropdown-menu-end">' .
                            '<li>' .
                                '<a class="dropdown-item" href="' . route('evaluations.show', $evaluation->id) . '" data-bs-toggle="tooltip" title="View Full Details">' .
                                    '<i class="fas fa-eye text-info me-2"></i>' .
                                    'View Details' .
                                '</a>' .
                            '</li>' .
                            '<li>' .
                                '<a class="dropdown-item" href="' . route('evaluations.edit', $evaluation->id) . '" data-bs-toggle="tooltip" title="Edit Evaluation">' .
                                    '<i class="fas fa-edit text-warning me-2"></i>' .
                                    'Edit Evaluation' .
                                '</a>' .
                            '</li>' .
                            '<li><hr class="dropdown-divider"></li>' .
                            '<li>' .
                                '<a class="dropdown-item text-danger" href="#" onclick="deleteEvaluation(' . $evaluation->id . ')" data-bs-toggle="tooltip" title="Delete Evaluation">' .
                                    '<i class="fas fa-trash me-2"></i>' .
                                    'Delete Evaluation' .
                                '</a>' .
                            '</li>' .
                        '</ul>' .
                    '</div>'
                ];
            });

            return response()->json([
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $evaluations->currentPage(),
                    'last_page' => $evaluations->lastPage(),
                    'per_page' => $evaluations->perPage(),
                    'total' => $evaluations->total(),
                    'from' => $evaluations->firstItem(),
                    'to' => $evaluations->lastItem(),
                    'has_more_pages' => $evaluations->hasMorePages(),
                    'has_previous_page' => $evaluations->previousPageUrl() !== null,
                    'has_next_page' => $evaluations->nextPageUrl() !== null,
                    'previous_page_url' => $evaluations->previousPageUrl(),
                    'next_page_url' => $evaluations->nextPageUrl(),
                    'first_page_url' => $evaluations->url(1),
                    'last_page_url' => $evaluations->url($evaluations->lastPage())
                ]
            ]);
        }

        $periods = Evaluation::distinct('evaluation_period')
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_code']);
        $criterias = Criteria::orderBy('name')->get(['id', 'name', 'weight']);

        return view('evaluations.index', compact('periods', 'employees', 'criterias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $criterias = Criteria::orderBy('name')->get();

        // Get existing evaluation periods
        $existingPeriods = Evaluation::distinct('evaluation_period')
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        return view('evaluations.create', compact('employees', 'criterias', 'existingPeriods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'criteria_id' => 'required|exists:criterias,id',
            'score' => 'required|integer|min:1|max:100',
            'evaluation_period' => 'required|string|max:255',
        ]);

        try {
            // Check if evaluation already exists
            $existingEvaluation = Evaluation::where([
                'employee_id' => $request->employee_id,
                'criteria_id' => $request->criteria_id,
                'evaluation_period' => $request->evaluation_period,
            ])->first();

            if ($existingEvaluation) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Evaluation for this employee, criteria, and period already exists.');
            }

            Evaluation::create($request->all());

            // Invalidate related caches
            $this->cacheService->invalidateEvaluationData('all');
            $this->cacheService->invalidatePeriods();
            $this->cacheService->invalidateDashboard();
            $this->cacheService->invalidateSAWResults($request->evaluation_period);

            return redirect()->route('evaluations.index')
                ->with('success', 'Evaluation successfully added.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add evaluation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Evaluation $evaluation)
    {
        $evaluation->load(['employee', 'criteria']);

        // Get other evaluations for same employee in same period
        $relatedEvaluations = Evaluation::with('criteria')
            ->where('employee_id', $evaluation->employee_id)
            ->where('evaluation_period', $evaluation->evaluation_period)
            ->orderBy('criteria_id')
            ->get();

        return view('evaluations.show', compact('evaluation', 'relatedEvaluations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evaluation $evaluation)
    {
        $evaluation->load(['employee', 'criteria']);
        $employees = Employee::orderBy('name')->get();
        $criterias = Criteria::orderBy('name')->get();

        return view('evaluations.edit', compact('evaluation', 'employees', 'criterias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'criteria_id' => 'required|exists:criterias,id',
            'score' => 'required|integer|min:1|max:100',
            'evaluation_period' => 'required|string|max:255',
        ]);

        try {
            // Check if evaluation already exists (excluding current record)
            $existingEvaluation = Evaluation::where([
                'employee_id' => $request->employee_id,
                'criteria_id' => $request->criteria_id,
                'evaluation_period' => $request->evaluation_period,
            ])->where('id', '!=', $evaluation->id)->first();

            if ($existingEvaluation) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Evaluation for this employee, criteria, and period already exists.');
            }

            $evaluation->update($request->all());

            return redirect()->route('evaluations.index')
                ->with('success', 'Evaluation successfully updated.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update evaluation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evaluation $evaluation)
    {
        try {
            $evaluation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Evaluation successfully deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show batch evaluation form.
     */
    public function batchCreate()
    {
        $employees = Employee::orderBy('name')->get();
        $criterias = Criteria::orderBy('name')->get();

        return view('evaluations.batch-create', compact('employees', 'criterias'));
    }

    /**
     * Store batch evaluations.
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'evaluation_period' => 'required|string|max:255',
            'evaluations' => 'required|array|min:1',
            'evaluations.*.employee_id' => 'required|exists:employees,id',
            'evaluations.*.scores' => 'required|array',
            'evaluations.*.scores.*' => 'required|integer|min:1|max:100',
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $skipCount = 0;
            $errors = [];

            foreach ($request->evaluations as $evaluationData) {
                foreach ($evaluationData['scores'] as $criteriaId => $score) {
                    // Check if evaluation already exists
                    $existingEvaluation = Evaluation::where([
                        'employee_id' => $evaluationData['employee_id'],
                        'criteria_id' => $criteriaId,
                        'evaluation_period' => $request->evaluation_period,
                    ])->first();

                    if ($existingEvaluation) {
                        if ($request->overwrite_existing) {
                            $existingEvaluation->update(['score' => $score]);
                            $successCount++;
                        } else {
                            $skipCount++;
                        }
                    } else {
                        Evaluation::create([
                            'employee_id' => $evaluationData['employee_id'],
                            'criteria_id' => $criteriaId,
                            'score' => $score,
                            'evaluation_period' => $request->evaluation_period,
                        ]);
                        $successCount++;
                    }
                }
            }

            DB::commit();

            $message = "Successfully saved {$successCount} evaluations.";
            if ($skipCount > 0) {
                $message .= " {$skipCount} evaluations skipped because they already exist.";
            }

            return redirect()->route('evaluations.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save batch evaluations: ' . $e->getMessage());
        }
    }

    /**
     * Generate SAW calculation results.
     */
    public function generateResults(Request $request)
    {
        $request->validate([
            'evaluation_period' => 'required|string'
        ]);

        try {
            $period = $request->evaluation_period;

            // Validate period readiness
            $validation = $this->sawService->validateEvaluationPeriod($period);

            if (!$validation['is_ready']) {
                $errors = [];
                if (!$validation['is_weight_valid']) {
                    $errors[] = "Total criteria weight must be 100% (currently: {$validation['total_weight']}%)";
                }
                if ($validation['missing_evaluations'] > 0) {
                    $errors[] = "Missing {$validation['missing_evaluations']} evaluations from total {$validation['expected_evaluations']} required";
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Evaluation period is not ready for SAW calculation.',
                    'errors' => $errors,
                    'validation' => $validation
                ], 400);
            }

            // Dispatch SAW calculation as background job for better performance
            ProcessSAWCalculationJob::dispatch($period, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'SAW calculation has been queued and will be processed in background. Please check results page in a few moments.',
                'evaluation_period' => $period,
                'status' => 'queued',
                'redirect' => route('results.index', ['period' => $period])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform SAW calculation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluation periods and their completion status.
     */
    public function getPeriods()
    {
        $periods = $this->sawService->getAvailablePeriods();
        $periodStatus = [];

        foreach ($periods as $period) {
            $validation = $this->sawService->validateEvaluationPeriod($period);
            $periodStatus[] = [
                'period' => $period,
                'completion_percentage' => $validation['completion_percentage'],
                'is_ready' => $validation['is_ready'],
                'total_evaluations' => $validation['total_evaluations'],
                'expected_evaluations' => $validation['expected_evaluations'],
            ];
        }

        return response()->json($periodStatus);
    }

    /**
     * Get evaluation matrix for a specific period.
     */
    public function getEvaluationMatrix(Request $request)
    {
        $period = $request->get('period');

        if (!$period) {
            return response()->json(['error' => 'Period parameter required'], 400);
        }

        $employees = Employee::orderBy('name')->get();
        $criterias = Criteria::orderBy('name')->get();
        $evaluations = Evaluation::where('evaluation_period', $period)->get();

        $matrix = [];
        foreach ($employees as $employee) {
            $matrix[$employee->id] = [
                'employee' => $employee,
                'scores' => []
            ];

            foreach ($criterias as $criteria) {
                $evaluation = $evaluations->where('employee_id', $employee->id)
                    ->where('criteria_id', $criteria->id)
                    ->first();

                $matrix[$employee->id]['scores'][$criteria->id] = [
                    'criteria' => $criteria,
                    'score' => $evaluation ? $evaluation->score : null,
                    'evaluation_id' => $evaluation ? $evaluation->id : null
                ];
            }
        }

        return response()->json([
            'matrix' => array_values($matrix),
            'criterias' => $criterias,
            'period' => $period
        ]);
    }

    /**
     * Restore soft deleted evaluations.
     */
    public function restore(Request $request)
    {
        try {
            $evaluationIds = $request->input('evaluation_ids', []);

            if (empty($evaluationIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No evaluations selected for restoration.'
                ], 400);
            }

            $restoredCount = 0;
            $failedIds = [];

            foreach ($evaluationIds as $id) {
                try {
                    $evaluation = Evaluation::onlyTrashed()->find($id);
                    if ($evaluation) {
                        $evaluation->restore();
                        $restoredCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted evaluations list
            $deletedEvaluations = Evaluation::onlyTrashed()
                ->with(['employee', 'criteria'])
                ->select('id', 'employee_id', 'criteria_id', 'score', 'evaluation_period', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($evaluation) {
                    return [
                        'id' => $evaluation->id,
                        'employee_name' => $evaluation->employee->name ?? 'Unknown',
                        'employee_code' => $evaluation->employee->employee_code ?? '-',
                        'criteria_name' => $evaluation->criteria->name ?? 'Unknown',
                        'score' => $evaluation->score,
                        'evaluation_period' => $evaluation->evaluation_period,
                        'deleted_at' => $evaluation->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully restored {$restoredCount} evaluation(s).";
            if (!empty($failedIds)) {
                $message .= " Failed to restore " . count($failedIds) . " evaluation(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'restored_count' => $restoredCount,
                'failed_count' => count($failedIds),
                'deleted_evaluations' => $deletedEvaluations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore evaluations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete evaluations.
     */
    public function forceDelete(Request $request)
    {
        try {
            $evaluationIds = $request->input('evaluation_ids', []);

            if (empty($evaluationIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No evaluations selected for permanent deletion.'
                ], 400);
            }

            $deletedCount = 0;
            $failedIds = [];

            foreach ($evaluationIds as $id) {
                try {
                    $evaluation = Evaluation::onlyTrashed()->find($id);
                    if ($evaluation) {
                        $evaluation->forceDelete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted evaluations list
            $deletedEvaluations = Evaluation::onlyTrashed()
                ->with(['employee', 'criteria'])
                ->select('id', 'employee_id', 'criteria_id', 'score', 'evaluation_period', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($evaluation) {
                    return [
                        'id' => $evaluation->id,
                        'employee_name' => $evaluation->employee->name ?? 'Unknown',
                        'employee_code' => $evaluation->employee->employee_code ?? '-',
                        'criteria_name' => $evaluation->criteria->name ?? 'Unknown',
                        'score' => $evaluation->score,
                        'evaluation_period' => $evaluation->evaluation_period,
                        'deleted_at' => $evaluation->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully permanently deleted {$deletedCount} evaluation(s).";
            if (!empty($failedIds)) {
                $message .= " Failed to delete " . count($failedIds) . " evaluation(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'failed_count' => count($failedIds),
                'deleted_evaluations' => $deletedEvaluations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete evaluations: ' . $e->getMessage()
            ], 500);
        }
    }
}
