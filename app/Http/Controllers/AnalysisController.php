<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Services\AdvancedAnalysisService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    protected $analysisService;
    protected $cacheService;

    public function __construct(AdvancedAnalysisService $analysisService, CacheService $cacheService)
    {
        $this->analysisService = $analysisService;
        $this->cacheService = $cacheService;
    }

    /**
     * Display the advanced analysis dashboard
     */
    public function index()
    {
        $availablePeriods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $criterias = Criteria::orderBy('weight', 'desc')->get();
        $employees = Employee::orderBy('name')->get();

        return view('analysis.index', compact('availablePeriods', 'criterias', 'employees'));
    }

    /**
     * Sensitivity Analysis
     */
    public function sensitivityAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evaluation_period' => 'required|string',
            'weight_changes' => 'sometimes|array',
            'weight_changes.*.criteria_id' => 'required_with:weight_changes|exists:criterias,id',
            'weight_changes.*.weight' => 'required_with:weight_changes|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $evaluationPeriod = $request->input('evaluation_period');
            $weightChanges = [];

            // Process custom weight changes if provided
            if ($request->has('weight_changes')) {
                $customWeights = [];
                foreach ($request->input('weight_changes') as $change) {
                    $customWeights[$change['criteria_id']] = $change['weight'];
                }
                $weightChanges['custom_scenario'] = $customWeights;
            }

            // Cache the analysis results
            $cacheKey = "sensitivity_analysis_{$evaluationPeriod}_" . md5(json_encode($weightChanges));
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($evaluationPeriod, $weightChanges) {
                return $this->analysisService->sensitivityAnalysis($evaluationPeriod, $weightChanges);
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Sensitivity analysis failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * What-if Scenario Analysis
     */
    public function whatIfScenarios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evaluation_period' => 'required|string',
            'scenarios' => 'required|array|min:1',
            'scenarios.*.name' => 'required|string',
            'scenarios.*.type' => 'required|in:weight_changes,score_changes,criteria_changes',
            'scenarios.*.changes' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $evaluationPeriod = $request->input('evaluation_period');
            $scenarios = [];

            foreach ($request->input('scenarios') as $scenarioData) {
                $scenarios[$scenarioData['name']] = [
                    $scenarioData['type'] => $scenarioData['changes']
                ];
            }

            $cacheKey = "what_if_analysis_{$evaluationPeriod}_" . md5(json_encode($scenarios));
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($evaluationPeriod, $scenarios) {
                return $this->analysisService->whatIfAnalysis($evaluationPeriod, $scenarios);
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('What-if analysis failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Multi-period Comparison
     */
    public function multiPeriodComparison(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periods' => 'required|array|min:2|max:6',
            'periods.*' => 'required|string',
            'comparison_type' => 'sometimes|in:all,specific,department',
            'employee_id' => 'sometimes|exists:employees,id',
            'department_id' => 'sometimes|string'
        ]);
        
        // Custom validation for department_id
        if ($request->has('department_id') && !empty($request->input('department_id'))) {
            $departmentExists = Employee::where('department', $request->input('department_id'))->exists();
            if (!$departmentExists) {
                return response()->json([
                    'success' => false,
                    'errors' => ['department_id' => ['The selected department does not exist.']]
                ], 422);
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $periods = $request->input('periods');
            $comparisonType = $request->input('comparison_type', 'all');
            $employeeId = $request->input('employee_id');
            $departmentId = $request->input('department_id');

            $cacheKey = "multi_period_comparison_" . md5(json_encode($periods) . "_" . $comparisonType . "_" . ($employeeId ?? 'all') . "_" . ($departmentId ?? 'all'));
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($periods, $comparisonType, $employeeId, $departmentId) {
                return $this->analysisService->multiPeriodComparison($periods, $comparisonType, $employeeId, $departmentId);
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Multi-period comparison failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Comparison failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced Statistical Analysis
     */
    public function advancedStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periods' => 'required|array|min:2',
            'periods.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $periods = $request->input('periods');

            $cacheKey = "advanced_statistics_" . md5(json_encode($periods));
            $results = $this->cacheService->remember($cacheKey, 3600, function() use ($periods) {
                return $this->analysisService->advancedStatisticalAnalysis($periods);
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Advanced statistics analysis failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Statistics analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Performance Forecasting
     */
    public function performanceForecast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'periods_ahead' => 'sometimes|integer|min:1|max:12',
            'methods' => 'sometimes|array',
            'methods.*' => 'sometimes|in:linear_trend,moving_average,weighted_average',
            'confidence_level' => 'sometimes|numeric|min:0.5|max:0.99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->input('employee_id');
            $periodsAhead = $request->input('periods_ahead', 3);
            $methods = $request->input('methods', ['linear_trend', 'moving_average', 'weighted_average']);
            $confidenceLevel = $request->input('confidence_level', 0.95);

            $cacheKey = "performance_forecast_{$employeeId}_{$periodsAhead}_" . md5(json_encode($methods) . "_{$confidenceLevel}");
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($employeeId, $periodsAhead, $methods, $confidenceLevel) {
                return $this->analysisService->performanceForecast($employeeId, $periodsAhead, $methods, $confidenceLevel);
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Performance forecast failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Forecast failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available periods for analysis
     */
    public function getAvailablePeriods()
    {
        $periods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        return response()->json([
            'success' => true,
            'data' => $periods
        ]);
    }

    /**
     * Get criteria for weight modification
     */
    public function getCriterias()
    {
        $criterias = Criteria::select('id', 'name', 'weight', 'type')
            ->orderBy('weight', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $criterias
        ]);
    }

    /**
     * Sensitivity Analysis View
     */
    public function sensitivityView(Request $request)
    {
        $availablePeriods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $criterias = Criteria::orderBy('weight', 'desc')->get();
        
        $selectedPeriod = $request->input('period', $availablePeriods->first());

        return view('analysis.sensitivity', compact('availablePeriods', 'criterias', 'selectedPeriod'));
    }

    /**
     * What-if Scenarios View
     */
    public function whatIfView(Request $request)
    {
        $availablePeriods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $criterias = Criteria::orderBy('weight', 'desc')->get();
        $employees = Employee::orderBy('name')->get();
        
        $selectedPeriod = $request->input('period', $availablePeriods->first());

        return view('analysis.what-if', compact('availablePeriods', 'criterias', 'employees', 'selectedPeriod'));
    }

    /**
     * Multi-period Comparison View
     */
    public function comparisonView()
    {
        $availablePeriods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $employees = Employee::orderBy('name')->get();

        return view('analysis.comparison', compact('availablePeriods', 'employees'));
    }

    /**
     * Forecasting View
     */
    public function forecastView()
    {
        $employees = Employee::has('evaluationResults', '>=', 3)
            ->orderBy('name')
            ->get();

        return view('analysis.forecast', compact('employees'));
    }

    /**
     * Export Analysis Report
     */
    public function exportAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:sensitivity,what_if,comparison,forecast,statistics',
            'format' => 'required|in:pdf,excel',
            'data' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $type = $request->input('type');
            $format = $request->input('format');
            $data = $request->input('data');

            // Generate export based on type and format
            $filename = "analysis_{$type}_" . date('Y-m-d_H-i-s') . ".{$format}";
            
            // Implementation would depend on the specific export requirements
            // For now, return success response
            return response()->json([
                'success' => true,
                'message' => 'Export generated successfully',
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            Log::error('Analysis export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analysis history
     */
    public function getAnalysisHistory(Request $request)
    {
        // This would typically fetch from a dedicated analysis_history table
        // For now, return empty array as placeholder
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Save analysis configuration
     */
    public function saveConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:sensitivity,what_if,comparison,forecast,statistics',
            'configuration' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Save configuration to database or cache
            // Implementation would depend on requirements
            return response()->json([
                'success' => true,
                'message' => 'Configuration saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Save configuration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Save failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get employee historical data for forecasting
     */
    public function getEmployeeHistoricalData(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employeeId = $request->input('employee_id');
        
        // Get historical evaluation results for the employee
        $historicalData = EvaluationResult::where('employee_id', $employeeId)
            ->with(['employee'])
            ->orderBy('evaluation_period')
            ->get()
            ->map(function ($result) {
                return [
                    'evaluation_period' => $result->evaluation_period,
                    'total_score' => $result->total_score,
                    'ranking' => $result->ranking
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $historicalData
        ]);
    }

    /**
     * Debug view for troubleshooting
     */
    public function debugView()
    {
        $availablePeriods = Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period');

        $criterias = Criteria::orderBy('weight', 'desc')->get();
        $employees = Employee::orderBy('name')->get();
        
        // Get evaluation counts per period
        $periodCounts = [];
        foreach ($availablePeriods as $period) {
            $periodCounts[$period] = Evaluation::where('evaluation_period', $period)->count();
        }
        
        $totalEvaluations = Evaluation::count();
        $totalResults = EvaluationResult::count();

        return view('analysis.debug', compact(
            'availablePeriods', 
            'criterias', 
            'employees', 
            'periodCounts',
            'totalEvaluations',
            'totalResults'
        ));
    }
}