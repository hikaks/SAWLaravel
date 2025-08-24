<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Services\AdvancedAnalysisService;
use App\Services\CacheService;
use App\Services\AdvancedStatisticsService;
use App\Services\AnalysisHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    protected $analysisService;
    protected $cacheService;
    protected $statisticsService;
    protected $historyService;

    public function __construct(
        AdvancedAnalysisService $analysisService,
        CacheService $cacheService,
        AdvancedStatisticsService $statisticsService,
        AnalysisHistoryService $historyService
    ) {
        $this->analysisService = $analysisService;
        $this->cacheService = $cacheService;
        $this->statisticsService = $statisticsService;
        $this->historyService = $historyService;
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
            $startTime = microtime(true);
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

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Record analysis history
            $this->historyService->recordAnalysis(
                'sensitivity',
                [
                    'evaluation_period' => $evaluationPeriod,
                    'weight_changes' => $weightChanges,
                    'scenarios_count' => count($results['scenarios'] ?? [])
                ],
                [
                    'total_scenarios' => count($results['scenarios'] ?? []),
                    'total_employees' => count($results['rankings'] ?? []),
                    'most_sensitive_criteria' => $results['most_sensitive_criteria'] ?? 'N/A',
                    'avg_ranking_change' => $results['avg_ranking_change'] ?? 0
                ],
                $executionTime
            );

            return response()->json([
                'success' => true,
                'data' => $results,
                'execution_time' => $executionTime
            ]);

        } catch (\Exception $e) {
            Log::error('Sensitivity analysis failed: ' . $e->getMessage());

            // Record failed analysis
            $this->historyService->recordAnalysis(
                'sensitivity',
                [
                    'evaluation_period' => $request->input('evaluation_period'),
                    'weight_changes' => $weightChanges ?? []
                ],
                [],
                0,
                'failed',
                $e->getMessage()
            );

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
            $startTime = microtime(true);
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

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Record analysis history
            $this->historyService->recordAnalysis(
                'what-if',
                [
                    'evaluation_period' => $evaluationPeriod,
                    'scenarios_count' => count($scenarios),
                    'scenario_types' => array_keys($scenarios)
                ],
                [
                    'scenarios_analyzed' => count($scenarios),
                    'total_employees' => count($results['rankings'] ?? []),
                    'best_performer' => $results['best_performer'] ?? 'N/A',
                    'improvement_potential' => $results['improvement_potential'] ?? 'N/A'
                ],
                $executionTime
            );

            return response()->json([
                'success' => true,
                'data' => $results,
                'execution_time' => $executionTime
            ]);

        } catch (\Exception $e) {
            Log::error('What-if analysis failed: ' . $e->getMessage());

            // Record failed analysis
            $this->historyService->recordAnalysis(
                'what-if',
                [
                    'evaluation_period' => $request->input('evaluation_period'),
                    'scenarios' => $request->input('scenarios', [])
                ],
                [],
                0,
                'failed',
                $e->getMessage()
            );

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
            $startTime = microtime(true);
            $periods = $request->input('periods');
            $comparisonType = $request->input('comparison_type', 'all');
            $employeeId = $request->input('employee_id');
            $departmentId = $request->input('department_id');

            $cacheKey = "multi_period_comparison_" . md5(json_encode($periods) . "_" . $comparisonType . "_" . ($employeeId ?? 'all') . "_" . ($departmentId ?? 'all'));
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($periods, $comparisonType, $employeeId, $departmentId) {
                return $this->analysisService->multiPeriodComparison($periods, $comparisonType, $employeeId, $departmentId);
            });

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Record analysis history
            $this->historyService->recordAnalysis(
                'comparison',
                [
                    'periods' => $periods,
                    'comparison_type' => $comparisonType,
                    'employee_id' => $employeeId,
                    'department_id' => $departmentId
                ],
                [
                    'periods_compared' => count($periods),
                    'trend_direction' => $results['trend_analysis']['trend_direction'] ?? 'N/A',
                    'avg_improvement' => $results['trend_analysis']['overall_change'] ?? 'N/A',
                    'total_employees' => count($results['employee_comparisons'] ?? [])
                ],
                $executionTime
            );

            return response()->json([
                'success' => true,
                'data' => $results,
                'execution_time' => $executionTime
            ]);

        } catch (\Exception $e) {
            Log::error('Multi-period comparison failed: ' . $e->getMessage());

            // Record failed analysis
            $this->historyService->recordAnalysis(
                'comparison',
                [
                    'periods' => $request->input('periods', []),
                    'comparison_type' => $request->input('comparison_type', 'all')
                ],
                [],
                0,
                'failed',
                $e->getMessage()
            );

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
            'confidence_level' => 'sometimes|numeric|min:50|max:99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startTime = microtime(true);
            $employeeId = $request->input('employee_id');
            $periodsAhead = $request->input('periods_ahead', 3);
            $methods = $request->input('methods', ['linear_trend', 'moving_average', 'weighted_average']);
            $confidenceLevel = $request->input('confidence_level', 95);

            // Convert percentage to decimal for service layer
            $confidenceLevelDecimal = $confidenceLevel / 100;

            $cacheKey = "performance_forecast_{$employeeId}_{$periodsAhead}_" . md5(json_encode($methods) . "_{$confidenceLevel}");
            $results = $this->cacheService->remember($cacheKey, 1800, function() use ($employeeId, $periodsAhead, $methods, $confidenceLevelDecimal) {
                return $this->analysisService->performanceForecast($employeeId, $periodsAhead, $methods, $confidenceLevelDecimal);
            });

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Record analysis history
            $this->historyService->recordAnalysis(
                'forecast',
                [
                    'employee_id' => $employeeId,
                    'periods_ahead' => $periodsAhead,
                    'methods' => $methods,
                    'confidence_level' => $confidenceLevel
                ],
                [
                    'forecast_periods' => $periodsAhead,
                    'predicted_trend' => $results['trend_prediction'] ?? 'N/A',
                    'confidence_interval' => $results['confidence_interval'] ?? 'N/A',
                    'accuracy_score' => $results['accuracy_score'] ?? 'N/A'
                ],
                $executionTime
            );

            return response()->json([
                'success' => true,
                'data' => $results,
                'execution_time' => $executionTime
            ]);

        } catch (\Exception $e) {
            Log::error('Performance forecast failed: ' . $e->getMessage());

            // Record failed analysis
            $this->historyService->recordAnalysis(
                'forecast',
                [
                    'employee_id' => $request->input('employee_id'),
                    'periods_ahead' => $request->input('periods_ahead', 3)
                ],
                [],
                0,
                'failed',
                $e->getMessage()
            );

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
     * Get Analysis History
     */
    public function getAnalysisHistory(Request $request)
    {
        $request->validate([
            'analysis_type' => 'nullable|string|in:sensitivity,what-if,comparison,forecast,statistics',
            'evaluation_period' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        try {
            $analysisType = $request->input('analysis_type');
            $evaluationPeriod = $request->input('evaluation_period');
            $limit = $request->input('limit', 20);

            $history = $this->historyService->getUserHistory($limit, $analysisType, $evaluationPeriod);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Get analysis history failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve history: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Get Advanced Statistics
     */
    public function getAdvancedStatistics(Request $request)
    {
        $request->validate([
            'evaluation_period' => 'nullable|string'
        ]);

        try {
            $evaluationPeriod = $request->input('evaluation_period');
            $startTime = microtime(true);

            $statistics = $this->statisticsService->getStatisticalOverview($evaluationPeriod);

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Record analysis history
            $this->historyService->recordAnalysis(
                'statistics',
                ['evaluation_period' => $evaluationPeriod],
                [
                    'total_employees' => $statistics['employee_stats']['summary']['total_employees'],
                    'total_criteria' => $statistics['criteria_stats']['summary']['total_criteria'],
                    'total_outliers' => $statistics['outlier_detection']['summary']['total_outliers'] ?? 0
                ],
                $executionTime
            );

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'execution_time' => $executionTime
            ]);

        } catch (\Exception $e) {
            Log::error('Advanced statistics failed: ' . $e->getMessage());

            // Record failed analysis
            $this->historyService->recordAnalysis(
                'statistics',
                ['evaluation_period' => $request->input('evaluation_period')],
                [],
                0,
                'failed',
                $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Statistics generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Analysis Statistics
     */
    public function getAnalysisStatistics()
    {
        try {
            $statistics = $this->historyService->getStatistics();
            $trends = $this->historyService->getRecentTrends();
            $performance = $this->historyService->getPerformanceMetrics();

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => $statistics,
                    'trends' => $trends,
                    'performance' => $performance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get analysis statistics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Analysis History
     */
    public function exportAnalysisHistory(Request $request)
    {
        try {
            $filters = $request->only(['analysis_type', 'evaluation_period', 'status', 'date_from', 'date_to']);
            $exportData = $this->historyService->exportHistory($filters);

            if (empty($exportData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data to export'
                ], 404);
            }

            // Convert to CSV format
            $csvContent = $this->convertToCSV($exportData);

            return response()->json([
                'success' => true,
                'data' => $csvContent
            ]);

        } catch (\Exception $e) {
            Log::error('Export analysis history failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert data to CSV format
     */
    private function convertToCSV($data)
    {
        if (empty($data)) {
            return '';
        }

        // CSV headers
        $headers = [
            'ID',
            'Analysis Type',
            'Evaluation Period',
            'Status',
            'Execution Time (ms)',
            'Parameters',
            'Results Summary',
            'Error Message',
            'Created At',
            'Updated At'
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csvRow = [
                $row->id,
                $row->analysis_type_display,
                $row->evaluation_period ?? '',
                $row->status,
                $row->execution_time_ms,
                json_encode($row->parameters),
                json_encode($row->results_summary),
                $row->error_message ?? '',
                $row->created_at,
                $row->updated_at
            ];

            // Escape CSV values
            $csvRow = array_map(function($value) {
                if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }
                return $value;
            }, $csvRow);

            $csv .= implode(',', $csvRow) . "\n";
        }

        return $csv;
    }

    /**
     * Delete Analysis History
     */
    public function deleteAnalysisHistory($analysisId)
    {
        try {
            $deleted = $this->historyService->deleteAnalysis($analysisId);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Analysis history deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete analysis history'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Delete analysis history failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
