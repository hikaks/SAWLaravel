<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
                if ($request->ajax()) {
            // Check if request is for chart info data
            if ($request->has('get_chart_info')) {
                $totalCriteria = Criteria::count();
                $totalWeight = Criteria::sum('weight');
                $remainingWeight = 100 - $totalWeight;
                $benefitCriteria = Criteria::where('type', 'benefit')->count();
                $costCriteria = Criteria::where('type', 'cost')->count();

                // Calculate completion percentage
                $completionPercentage = min(100, $totalWeight);
                $status = $totalWeight == 100 ? 'Complete' : ($totalWeight > 100 ? 'Overweight' : 'Incomplete');

                return response()->json([
                    'total_criteria' => $totalCriteria,
                    'total_weight' => $totalWeight,
                    'remaining_weight' => $remainingWeight,
                    'benefit_criteria' => $benefitCriteria,
                    'cost_criteria' => $costCriteria,
                    'completion_percentage' => $completionPercentage,
                    'status' => $status
                ]);
            }

            // Handle deleted criteria request
            if ($request->has('get_deleted')) {
                $deletedCriteria = Criteria::onlyTrashed()
                    ->select('id', 'name', 'weight', 'type', 'deleted_at')
                    ->orderBy('deleted_at', 'desc')
                    ->get()
                    ->map(function($criteria) {
                        return [
                            'id' => $criteria->id,
                            'name' => $criteria->name,
                            'weight' => $criteria->weight,
                            'type' => $criteria->type,
                            'deleted_at' => $criteria->deleted_at->format('d M Y H:i')
                        ];
                    });

                return response()->json(['deleted_criteria' => $deletedCriteria]);
            }

            $criterias = Criteria::withCount('evaluations')->get();

            return DataTables::of($criterias)
                ->addIndexColumn()
                ->addColumn('weight_percentage', function($criteria) {
                    return $criteria->weight . '%';
                })
                ->addColumn('type_badge', function($criteria) {
                    $badgeClass = $criteria->type === 'benefit' ? 'success' : 'warning';
                    return '<span class="badge bg-'.$badgeClass.'">'.ucfirst($criteria->type).'</span>';
                })
                ->addColumn('evaluations_count', function($criteria) {
                    return $criteria->evaluations_count . ' evaluasi';
                })
                ->addColumn('action', function($criteria) {
                    return '
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="'.route('criterias.show', $criteria->id).'" data-bs-toggle="tooltip" title="View Full Details">
                                        <i class="fas fa-eye text-info me-2"></i>
                                        View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="'.route('criterias.edit', $criteria->id).'" data-bs-toggle="tooltip" title="Edit Criteria">
                                        <i class="fas fa-edit text-warning me-2"></i>
                                        Edit Criteria
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteCriteria('.$criteria->id.')" data-bs-toggle="tooltip" title="Delete Criteria">
                                        <i class="fas fa-trash me-2"></i>
                                        Delete Criteria
                                    </a>
                                </li>
                            </ul>
                        </div>
                    ';
                })
                ->rawColumns(['type_badge', 'action'])
                ->make(true);
        }

        $totalWeight = Criteria::sum('weight');
        return view('criterias.index', compact('totalWeight'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $totalWeight = Criteria::sum('weight');
        $remainingWeight = 100 - $totalWeight;

        return view('criterias.create', compact('totalWeight', 'remainingWeight'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:criterias,name',
            'weight' => 'required|integer|min:1|max:100',
            'type' => 'required|in:benefit,cost',
        ]);

        $currentTotal = Criteria::sum('weight');
        $newTotal = $currentTotal + $request->weight;

        if ($newTotal > 100) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total bobot tidak boleh melebihi 100. Sisa bobot yang tersedia: ' . (100 - $currentTotal));
        }

        try {
            Criteria::create($request->all());

            $message = 'Criteria successfully added.';
            if ($newTotal === 100) {
                $message .= ' Total weight has reached 100%.';
            }

            return redirect()->route('criterias.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add criteria: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Criteria $criteria)
    {
        $criteria->load(['evaluations.employee']);

        // Get evaluation statistics
        $evaluationStats = [
            'total_evaluations' => $criteria->evaluations->count(),
            'avg_score' => $criteria->evaluations->avg('score'),
            'min_score' => $criteria->evaluations->min('score'),
            'max_score' => $criteria->evaluations->max('score'),
        ];

        // Group evaluations by period
        $evaluationsByPeriod = $criteria->evaluations->groupBy('evaluation_period');

        return view('criterias.show', compact('criteria', 'evaluationStats', 'evaluationsByPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Criteria $criteria)
    {
        $totalWeight = Criteria::where('id', '!=', $criteria->id)->sum('weight');
        $remainingWeight = 100 - $totalWeight;

        return view('criterias.edit', compact('criteria', 'totalWeight', 'remainingWeight'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Criteria $criteria)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:criterias,name,'.$criteria->id,
            'weight' => 'required|integer|min:1|max:100',
            'type' => 'required|in:benefit,cost',
        ]);

        $currentTotal = Criteria::where('id', '!=', $criteria->id)->sum('weight');
        $newTotal = $currentTotal + $request->weight;

        if ($newTotal > 100) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total bobot tidak boleh melebihi 100. Sisa bobot yang tersedia: ' . (100 - $currentTotal));
        }

        try {
            $criteria->update($request->all());

            $message = 'Criteria successfully updated.';
            if ($newTotal === 100) {
                $message .= ' Total weight has reached 100%.';
            }

            return redirect()->route('criterias.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update criteria: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Criteria $criteria)
    {
        try {
            // Check if criteria is used in evaluations
            if ($criteria->evaluations()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Criteria cannot be deleted because it is already used in evaluations.'
                ], 400);
            }

            $criteria->delete(); // This will now be soft delete

            return response()->json([
                'success' => true,
                'message' => 'Criteria successfully deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete criteria: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted criteria.
     */
    public function restore(Request $request)
    {
        try {
            $criteriaIds = $request->input('criteria_ids', []);

            if (empty($criteriaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No criteria selected for restoration.'
                ], 400);
            }

            $restoredCount = 0;
            $failedIds = [];

            foreach ($criteriaIds as $id) {
                try {
                    $criteria = Criteria::onlyTrashed()->find($id);
                    if ($criteria) {
                        $criteria->restore();
                        $restoredCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted criteria list
            $deletedCriteria = Criteria::onlyTrashed()
                ->select('id', 'name', 'weight', 'type', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($criteria) {
                    return [
                        'id' => $criteria->id,
                        'name' => $criteria->name,
                        'weight' => $criteria->weight,
                        'type' => $criteria->type,
                        'deleted_at' => $criteria->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully restored {$restoredCount} criteria.";
            if (!empty($failedIds)) {
                $message .= " Failed to restore " . count($failedIds) . " criteria.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'restored_count' => $restoredCount,
                'failed_count' => count($failedIds),
                'deleted_criteria' => $deletedCriteria
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore criteria: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete criteria.
     */
    public function forceDelete(Request $request)
    {
        try {
            $criteriaIds = $request->input('criteria_ids', []);

            if (empty($criteriaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No criteria selected for permanent deletion.'
                ], 400);
            }

            $deletedCount = 0;
            $failedIds = [];

            foreach ($criteriaIds as $id) {
                try {
                    $criteria = Criteria::onlyTrashed()->find($id);
                    if ($criteria) {
                        // Check if criteria is being used in evaluations
                        if ($criteria->evaluations()->exists()) {
                            $failedIds[] = $id;
                            continue;
                        }

                        $criteria->forceDelete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $id;
                }
            }

            // Get updated deleted criteria list
            $deletedCriteria = Criteria::onlyTrashed()
                ->select('id', 'name', 'weight', 'type', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function($criteria) {
                    return [
                        'id' => $criteria->id,
                        'name' => $criteria->name,
                        'weight' => $criteria->weight,
                        'type' => $criteria->type,
                        'deleted_at' => $criteria->deleted_at->format('d M Y H:i')
                    ];
                });

            $message = "Successfully permanently deleted {$deletedCount} criteria.";
            if (!empty($failedIds)) {
                $message .= " Failed to delete " . count($failedIds) . " criteria (may be in use).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'failed_count' => count($failedIds),
                'deleted_criteria' => $deletedCriteria
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete criteria: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current total weight
     */
    public function getTotalWeight()
    {
        $totalWeight = Criteria::sum('weight');

        return response()->json([
            'total_weight' => $totalWeight,
            'remaining_weight' => 100 - $totalWeight,
            'is_complete' => $totalWeight === 100
        ]);
    }

    /**
     * Validate weight before save
     */
    public function validateWeight(Request $request)
    {
        $weight = $request->get('weight', 0);
        $excludeId = $request->get('exclude_id');

        $currentTotal = Criteria::when($excludeId, function($query) use ($excludeId) {
            return $query->where('id', '!=', $excludeId);
        })->sum('weight');

        $newTotal = $currentTotal + $weight;
        $isValid = $newTotal <= 100;

        return response()->json([
            'is_valid' => $isValid,
            'current_total' => $currentTotal,
            'new_total' => $newTotal,
            'remaining_weight' => 100 - $currentTotal,
            'message' => $isValid ? 'Weight valid' : 'Total weight exceeds 100'
        ]);
    }
}
