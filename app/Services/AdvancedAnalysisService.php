<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AdvancedAnalysisService
{
    protected $sawService;
    protected $cacheService;

    public function __construct(SAWCalculationService $sawService, CacheService $cacheService)
    {
        $this->sawService = $sawService;
        $this->cacheService = $cacheService;
    }

    /**
     * Perform sensitivity analysis on criteria weights
     */
    public function sensitivityAnalysis(string $evaluationPeriod, array $weightChanges = []): array
    {
        try {
            // Get original criteria weights
            $originalCriteria = Criteria::all();
            $originalWeights = $originalCriteria->pluck('weight', 'id')->toArray();

            // Get original results for comparison
            $originalResults = EvaluationResult::with('employee')
                ->where('evaluation_period', $evaluationPeriod)
                ->orderBy('ranking')
                ->get();

            $sensitivityData = [];

            // If no specific changes provided, generate standard sensitivity scenarios
            if (empty($weightChanges)) {
                $weightChanges = $this->generateStandardSensitivityScenarios($originalWeights);
            }

            foreach ($weightChanges as $scenarioName => $newWeights) {
                // Calculate SAW with modified weights
                $modifiedResults = $this->calculateSAWWithModifiedWeights(
                    $evaluationPeriod,
                    $newWeights
                );

                // Compare rankings
                $rankingChanges = $this->compareRankings($originalResults, $modifiedResults);

                // Calculate sensitivity metrics
                $sensitivityMetrics = $this->calculateSensitivityMetrics(
                    $originalResults,
                    $modifiedResults
                );

                $sensitivityData[$scenarioName] = [
                    'weights' => $newWeights,
                    'results' => $modifiedResults,
                    'ranking_changes' => $rankingChanges,
                    'metrics' => $sensitivityMetrics
                ];
            }

            return [
                'original_results' => $originalResults,
                'original_weights' => $originalWeights,
                'sensitivity_scenarios' => $sensitivityData,
                'summary' => $this->generateSensitivitySummary($sensitivityData)
            ];

        } catch (\Exception $e) {
            Log::error("Sensitivity analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * What-if scenario analysis
     */
    public function whatIfAnalysis(string $evaluationPeriod, array $scenarios): array
    {
        try {
            // Validate input parameters
            if (empty($evaluationPeriod)) {
                throw new \InvalidArgumentException('Evaluation period cannot be empty');
            }

            if (empty($scenarios)) {
                throw new \InvalidArgumentException('Scenarios array cannot be empty');
            }

            // Check if evaluation period exists
            $periodExists = EvaluationResult::where('evaluation_period', $evaluationPeriod)->exists();
            if (!$periodExists) {
                throw new \Exception("No evaluation data found for period: {$evaluationPeriod}");
            }

            $results = [];
            $errors = [];

            foreach ($scenarios as $scenarioName => $scenario) {
                try {
                    $scenarioResults = [];

                    // Validate scenario structure
                    if (!is_array($scenario)) {
                        throw new \InvalidArgumentException("Scenario '{$scenarioName}' must be an array");
                    }

                    // Handle weight changes
                    if (isset($scenario['weight_changes'])) {
                        try {
                            $scenarioResults['weight_impact'] = $this->calculateSAWWithModifiedWeights(
                                $evaluationPeriod,
                                $scenario['weight_changes']
                            );
                        } catch (\Exception $e) {
                            \Log::error("Weight changes analysis failed for scenario '{$scenarioName}': " . $e->getMessage());
                            $errors[$scenarioName]['weight_changes'] = $e->getMessage();
                        }
                    }

                    // Handle score changes for specific employees
                    if (isset($scenario['score_changes'])) {
                        try {
                            // Convert score_changes format from frontend to backend expected format
                            $scoreChanges = $this->convertScoreChangesFormat(
                                $evaluationPeriod,
                                $scenario['score_changes']
                            );
                            
                            $scenarioResults['score_impact'] = $this->calculateSAWWithModifiedScores(
                                $evaluationPeriod,
                                $scoreChanges
                            );
                        } catch (\Exception $e) {
                            \Log::error("Score changes analysis failed for scenario '{$scenarioName}': " . $e->getMessage());
                            $errors[$scenarioName]['score_changes'] = $e->getMessage();
                        }
                    }

                    // Handle criteria additions/removals
                    if (isset($scenario['criteria_changes'])) {
                        try {
                            $scenarioResults['criteria_impact'] = $this->calculateSAWWithModifiedCriteria(
                                $evaluationPeriod,
                                $scenario['criteria_changes']
                            );
                        } catch (\Exception $e) {
                            \Log::error("Criteria changes analysis failed for scenario '{$scenarioName}': " . $e->getMessage());
                            $errors[$scenarioName]['criteria_changes'] = $e->getMessage();
                        }
                    }

                    // If no valid analysis was performed, log warning
                    if (empty($scenarioResults)) {
                        \Log::warning("No valid analysis performed for scenario '{$scenarioName}'");
                        $errors[$scenarioName]['general'] = 'No valid scenario changes specified';
                    }

                    $results[$scenarioName] = $scenarioResults;

                } catch (\Exception $e) {
                    \Log::error("Scenario '{$scenarioName}' analysis failed: " . $e->getMessage());
                    $errors[$scenarioName]['general'] = $e->getMessage();
                    $results[$scenarioName] = [];
                }
            }

            // Add error information to results if any errors occurred
            if (!empty($errors)) {
                $results['_errors'] = $errors;
                $results['_has_errors'] = true;
            } else {
                $results['_has_errors'] = false;
            }

            return $results;

        } catch (\Exception $e) {
            \Log::error('What-if analysis failed: ' . $e->getMessage());
            throw new \Exception('What-if analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * Advanced statistical analysis
     */
    public function advancedStatisticalAnalysis(array $periods): array
    {
        $statistics = [];

        // Growth rate analysis
        $statistics['growth_rates'] = $this->calculateGrowthRates($periods);

        // Performance variance analysis
        $statistics['variance_analysis'] = $this->calculatePerformanceVariance($periods);

        // Correlation analysis between criteria
        $statistics['criteria_correlation'] = $this->calculateCriteriaCorrelation($periods);

        // Department performance trends
        $statistics['department_trends'] = $this->calculateDepartmentTrends($periods);

        // Statistical distribution analysis
        $statistics['distribution_analysis'] = $this->calculateDistributionAnalysis($periods);

        return $statistics;
    }

    /**
     * Multi-period comparison data
     */
    public function multiPeriodComparison(array $periods, string $comparisonType = 'all', ?int $employeeId = null, ?string $departmentId = null): array
    {
        $query = EvaluationResult::with('employee')
            ->whereIn('evaluation_period', $periods);

        // Handle different comparison types
        switch ($comparisonType) {
            case 'specific':
                if (!$employeeId) {
                    throw new \Exception('Employee ID is required for specific employee comparison');
                }
                $query->where('employee_id', $employeeId);
                break;
            
            case 'department':
                // For department comparison, we'll group by department later
                break;
            
            case 'all':
            default:
                // No additional filtering needed for all employees
                break;
        }

        $results = $query->orderBy('evaluation_period')
            ->orderBy('ranking')
            ->get()
            ->groupBy('evaluation_period');

        // Handle department comparison grouping
        if ($comparisonType === 'department') {
            return $this->handleDepartmentComparison($periods, $results, $departmentId);
        }

        $comparison = [];

        foreach ($periods as $period) {
            $periodResults = $results->get($period, collect());

            $comparison[$period] = [
                'results' => $periodResults,
                'statistics' => [
                    'avg_score' => $periodResults->avg('total_score'),
                    'max_score' => $periodResults->max('total_score'),
                    'min_score' => $periodResults->min('total_score'),
                    'std_deviation' => $this->calculateStandardDeviation($periodResults->pluck('total_score')),
                    'total_employees' => $periodResults->count()
                ]
            ];
        }

        // Calculate period-to-period changes
        $comparison['period_changes'] = $this->calculatePeriodChanges($comparison);

        return $comparison;
    }

    /**
     * Handle department comparison
     */
    private function handleDepartmentComparison(array $periods, $results, ?string $departmentId = null): array
    {
        $comparison = [];

        foreach ($periods as $period) {
            $periodResults = $results->get($period, collect());
            
            // Filter by specific department if provided
            if ($departmentId) {
                $periodResults = $periodResults->filter(function($result) use ($departmentId) {
                    return $result->employee && $result->employee->department == $departmentId;
                });
            }
            
            // Group by department
            $departmentGroups = $periodResults->groupBy('employee.department');
            
            $comparison[$period] = [
                'departments' => [],
                'statistics' => [
                    'avg_score' => $periodResults->avg('total_score'),
                    'max_score' => $periodResults->max('total_score'),
                    'min_score' => $periodResults->min('total_score'),
                    'std_deviation' => $this->calculateStandardDeviation($periodResults->pluck('total_score')),
                    'total_employees' => $periodResults->count(),
                    'total_departments' => $departmentGroups->count()
                ]
            ];
            
            foreach ($departmentGroups as $department => $deptResults) {
                $comparison[$period]['departments'][$department] = [
                    'results' => $deptResults,
                    'statistics' => [
                        'avg_score' => $deptResults->avg('total_score'),
                        'max_score' => $deptResults->max('total_score'),
                        'min_score' => $deptResults->min('total_score'),
                        'std_deviation' => $this->calculateStandardDeviation($deptResults->pluck('total_score')),
                        'total_employees' => $deptResults->count()
                    ]
                ];
            }
        }

        // Calculate department-wise period changes
        $comparison['department_changes'] = $this->calculateDepartmentChanges($comparison);

        return $comparison;
    }

    /**
     * Calculate department changes across periods
     */
    private function calculateDepartmentChanges(array $comparison): array
    {
        $changes = [];
        $periods = array_keys($comparison);
        
        // Remove non-period keys
        $periods = array_filter($periods, function($key) {
            return $key !== 'department_changes';
        });
        
        $periods = array_values($periods);
        
        if (count($periods) < 2) {
            return $changes;
        }
        
        // Get all departments across all periods
        $allDepartments = [];
        foreach ($periods as $period) {
            if (isset($comparison[$period]['departments'])) {
                $allDepartments = array_merge($allDepartments, array_keys($comparison[$period]['departments']));
            }
        }
        $allDepartments = array_unique($allDepartments);
        
        foreach ($allDepartments as $department) {
            $changes[$department] = [];
            
            for ($i = 1; $i < count($periods); $i++) {
                $currentPeriod = $periods[$i];
                $previousPeriod = $periods[$i - 1];
                
                $currentStats = $comparison[$currentPeriod]['departments'][$department]['statistics'] ?? null;
                $previousStats = $comparison[$previousPeriod]['departments'][$department]['statistics'] ?? null;
                
                if ($currentStats && $previousStats) {
                    $scoreChange = $currentStats['avg_score'] - $previousStats['avg_score'];
                    $employeeChange = $currentStats['total_employees'] - $previousStats['total_employees'];
                    
                    $changes[$department]["{$previousPeriod}_to_{$currentPeriod}"] = [
                        'score_change' => $scoreChange,
                        'score_change_percentage' => $previousStats['avg_score'] > 0 ? ($scoreChange / $previousStats['avg_score']) * 100 : 0,
                        'employee_change' => $employeeChange,
                        'trend' => $scoreChange > 0 ? 'improving' : ($scoreChange < 0 ? 'declining' : 'stable')
                    ];
                }
            }
        }
        
        return $changes;
    }

    /**
     * Performance forecasting
     */
    public function performanceForecast(int $employeeId, int $periodsAhead = 3, array $methods = ['linear_trend', 'moving_average', 'weighted_average'], float $confidenceLevel = 0.95): array
    {
        // Get historical data for the employee
        $historicalResults = EvaluationResult::where('employee_id', $employeeId)
            ->orderBy('evaluation_period')
            ->get();

        if ($historicalResults->count() < 3) {
            throw new \Exception('Insufficient historical data for forecasting (minimum 3 periods required)');
        }

        $forecasts = [];
        
        // Calculate forecasts based on requested methods
        if (in_array('linear_trend', $methods)) {
            $forecasts['linear_trend'] = $this->calculateLinearTrendForecast($historicalResults, $periodsAhead);
        }
        
        if (in_array('moving_average', $methods)) {
            $forecasts['moving_average'] = $this->calculateMovingAverageForecast($historicalResults, $periodsAhead);
        }
        
        if (in_array('weighted_average', $methods)) {
            $forecasts['weighted_average'] = $this->calculateWeightedMovingAverageForecast($historicalResults, $periodsAhead);
        }

        return [
            'historical_data' => $historicalResults,
            'forecasts' => $forecasts,
            'confidence_intervals' => $this->calculateConfidenceIntervals($historicalResults, $confidenceLevel),
            'forecast_accuracy' => $this->calculateForecastAccuracy($historicalResults)
        ];
    }

    /**
     * Generate standard sensitivity scenarios
     */
    private function generateStandardSensitivityScenarios(array $originalWeights): array
    {
        $scenarios = [];

        // Scenario 1: Increase each criteria by 10%, decrease others proportionally
        foreach ($originalWeights as $criteriaId => $weight) {
            $newWeights = $originalWeights;
            $increase = min(10, 100 - $weight); // Can't exceed 100%
            $newWeights[$criteriaId] += $increase;

            // Decrease other weights proportionally
            $totalOthers = array_sum($originalWeights) - $weight;
            if ($totalOthers > 0) {
                foreach ($newWeights as $id => $w) {
                    if ($id != $criteriaId) {
                        $newWeights[$id] = max(1, $w - ($increase * $w / $totalOthers));
                    }
                }
            }

            // Normalize to 100%
            $total = array_sum($newWeights);
            foreach ($newWeights as $id => $w) {
                $newWeights[$id] = round(($w / $total) * 100);
            }

            $scenarios["increase_criteria_{$criteriaId}"] = $newWeights;
        }

        // Scenario 2: Equal weights for all criteria
        $equalWeight = round(100 / count($originalWeights));
        $scenarios['equal_weights'] = array_fill_keys(array_keys($originalWeights), $equalWeight);

        return $scenarios;
    }

    /**
     * Calculate SAW with modified weights
     */
    private function calculateSAWWithModifiedWeights(string $evaluationPeriod, array $newWeights): Collection
    {
        // Validate input
        if (empty($newWeights)) {
            throw new \InvalidArgumentException('New weights array cannot be empty');
        }

        // Validate that all weights are numeric and positive
        foreach ($newWeights as $criteriaId => $weight) {
            if (!is_numeric($weight) || $weight <= 0) {
                throw new \InvalidArgumentException("Invalid weight value for criteria {$criteriaId}: {$weight}");
            }
        }

        // Get original evaluation results with proper relationships
        $originalResults = EvaluationResult::where('evaluation_period', $evaluationPeriod)
            ->with(['employee'])
            ->orderBy('ranking')
            ->get();

        if ($originalResults->isEmpty()) {
            throw new \Exception("No evaluation results found for period: {$evaluationPeriod}");
        }

        // Store original rankings and scores
        $originalData = [];
        foreach ($originalResults as $result) {
            $originalData[$result->employee_id] = [
                'ranking' => $result->ranking,
                'total_score' => $result->total_score
            ];
        }

        // Get evaluations for the period
        $evaluations = Evaluation::with(['employee', 'criteria'])
            ->where('evaluation_period', $evaluationPeriod)
            ->get();

        if ($evaluations->isEmpty()) {
            throw new \Exception("No evaluation data found for period: {$evaluationPeriod}");
        }

        // Get all criteria and merge with new weights
        $allCriterias = Criteria::all();
        $originalWeights = $this->getCriteriaWeights($evaluationPeriod);
        
        // Merge new weights with original weights
        $finalWeights = array_merge($originalWeights, $newWeights);
        
        $employees = Employee::whereIn('id', $evaluations->pluck('employee_id')->unique())->get();
        $criterias = $allCriterias;

        if ($criterias->isEmpty()) {
            throw new \Exception("No criteria found");
        }

        // Group evaluations by criteria for normalization
        $evaluationsByCriteria = $evaluations->groupBy('criteria_id');
        $normalizedScores = [];

        // Normalize scores for each criteria
        foreach ($criterias as $criteria) {
            $criteriaEvaluations = $evaluationsByCriteria->get($criteria->id, collect());
            $scores = $criteriaEvaluations->pluck('score')->toArray();

            if (empty($scores)) continue;

            if ($criteria->isBenefit()) {
                $maxScore = max($scores);
                foreach ($criteriaEvaluations as $evaluation) {
                    $normalizedScores[$evaluation->employee_id][$criteria->id] =
                        $maxScore > 0 ? $evaluation->score / $maxScore : 0;
                }
            } else {
                $minScore = min($scores);
                foreach ($criteriaEvaluations as $evaluation) {
                    $normalizedScores[$evaluation->employee_id][$criteria->id] =
                        $evaluation->score > 0 ? $minScore / $evaluation->score : 0;
                }
            }
        }

        // Calculate weighted scores with new weights
        $results = [];
        foreach ($employees as $employee) {
            $totalScore = 0;

            foreach ($criterias as $criteria) {
                $normalizedScore = $normalizedScores[$employee->id][$criteria->id] ?? 0;
                $weight = $finalWeights[$criteria->id] ?? 0;
                $weightedScore = $normalizedScore * ($weight / 100);
                $totalScore += $weightedScore;
            }

            // Get original data for comparison
            $originalScore = $originalData[$employee->id]['total_score'] ?? 0;
            $originalRanking = $originalData[$employee->id]['ranking'] ?? null;

            $results[] = (object)[
                'employee_id' => $employee->id,
                'employee' => $employee,
                'total_score' => round($totalScore, 4),
                'original_ranking' => $originalRanking,
                'original_score' => $originalScore,
                'score_change' => round($totalScore - $originalScore, 4),
            ];
        }

        // Sort by score and assign rankings
        usort($results, function ($a, $b) {
            return $b->total_score <=> $a->total_score;
        });

        foreach ($results as $index => $result) {
            $result->ranking = $index + 1;
        }

        return collect($results);
    }

    /**
     * Convert score changes format from frontend to backend expected format
     */
    private function convertScoreChangesFormat(string $evaluationPeriod, array $scoreChangeData): array
    {
        $scoreChanges = [];
        
        // Frontend sends: {employee_id: X, score_change: Y}
        // Backend expects: scoreChanges[employee_id][criteria_id] = newScore
        
        if (isset($scoreChangeData['employee_id']) && isset($scoreChangeData['score_change'])) {
            $employeeId = $scoreChangeData['employee_id'];
            $scoreChangePercent = $scoreChangeData['score_change'];
            
            // Get all evaluations for this employee in the evaluation period
            $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)
                ->where('employee_id', $employeeId)
                ->with('criteria')
                ->get();
            
            foreach ($evaluations as $evaluation) {
                // Calculate new score by applying percentage change
                $originalScore = $evaluation->score;
                $newScore = $originalScore + ($originalScore * $scoreChangePercent / 100);
                
                // Ensure score stays within valid range (0-100)
                $newScore = max(0, min(100, $newScore));
                
                $scoreChanges[$employeeId][$evaluation->criteria_id] = $newScore;
            }
        }
        
        return $scoreChanges;
    }

    /**
     * Convert criteria changes format from frontend to backend expected format
     */
    private function convertCriteriaChangesFormat(array $criteriaChangeData): array
    {
        $convertedChanges = [];
        
        // Frontend sends: {action: 'remove', criteria_id: X} or {action: 'add', name: Y, weight: Z, type: W} etc.
        // Backend expects: {remove: [criteria_id], add: {criteria_id: weight}, modify: {criteria_id: weight}}
        
        if (isset($criteriaChangeData['action'])) {
            $action = $criteriaChangeData['action'];
            
            switch ($action) {
                case 'remove':
                    if (isset($criteriaChangeData['criteria_id'])) {
                        $convertedChanges['remove'] = [$criteriaChangeData['criteria_id']];
                    }
                    break;
                    
                case 'add':
                    if (isset($criteriaChangeData['name'], $criteriaChangeData['weight'], $criteriaChangeData['type'])) {
                        // For add operation, we need to create a temporary criteria ID
                        // We'll use a negative ID to distinguish from existing criteria
                        $tempId = -1;
                        $convertedChanges['add'] = [$tempId => $criteriaChangeData['weight']];
                        $convertedChanges['add_details'] = [
                            $tempId => [
                                'name' => $criteriaChangeData['name'],
                                'type' => $criteriaChangeData['type'],
                                'weight' => $criteriaChangeData['weight']
                            ]
                        ];
                    }
                    break;
                    
                case 'modify':
                    if (isset($criteriaChangeData['criteria_id'], $criteriaChangeData['weight'])) {
                        $convertedChanges['modify'] = [
                            $criteriaChangeData['criteria_id'] => $criteriaChangeData['weight']
                        ];
                    }
                    break;
            }
        }
        
        return $convertedChanges;
    }

    /**
     * Calculate SAW with modified scores
     */
    private function calculateSAWWithModifiedScores(string $evaluationPeriod, array $scoreChanges): Collection
    {
        try {
            // Get original evaluation results with proper relationships
            $originalResults = EvaluationResult::where('evaluation_period', $evaluationPeriod)
                ->with([
                    'employee',
                    'evaluationDetails' => function($query) use ($evaluationPeriod) {
                        $query->where('evaluation_period', $evaluationPeriod)->with('criteria');
                    }
                ])
                ->orderBy('ranking')
                ->get();

            if ($originalResults->isEmpty()) {
                throw new \Exception("No evaluation results found for period: {$evaluationPeriod}");
            }

            // Store original rankings and scores
            $originalData = [];
            foreach ($originalResults as $result) {
                $originalData[$result->employee_id] = [
                    'ranking' => $result->ranking,
                    'total_score' => $result->total_score
                ];
            }

            // Get all employees and criteria
            $employees = Employee::all();
            $criterias = Criteria::all();

            // Get all evaluations for this period
            $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)
                ->with(['employee', 'criteria'])
                ->get()
                ->groupBy('employee_id');

            // Apply score modifications and calculate normalized scores
            $normalizedScores = [];
            foreach ($criterias as $criteria) {
                $scores = [];
                
                // Collect scores for this criteria (with modifications applied)
                foreach ($employees as $employee) {
                    $evaluation = $evaluations->get($employee->id)?->firstWhere('criteria_id', $criteria->id);
                    if ($evaluation) {
                        $originalScore = $evaluation->score;
                        
                        // Apply score change if specified
                        if (isset($scoreChanges[$employee->id][$criteria->id])) {
                            $newScore = $scoreChanges[$employee->id][$criteria->id];
                            // Validate score range
                            if ($newScore >= 0 && $newScore <= 100) {
                                $scores[$employee->id] = $newScore;
                            } else {
                                $scores[$employee->id] = $originalScore;
                                \Log::warning("Invalid score {$newScore} for employee {$employee->id}, criteria {$criteria->id}. Using original score.");
                            }
                        } else {
                            $scores[$employee->id] = $originalScore;
                        }
                    }
                }

                // Normalize scores based on criteria type
                if ($criteria->type === 'benefit') {
                    $maxScore = max($scores);
                    foreach ($scores as $employeeId => $score) {
                        $normalizedScores[$employeeId][$criteria->id] = $maxScore > 0 ? $score / $maxScore : 0;
                    }
                } else {
                    $minScore = min($scores);
                    foreach ($scores as $employeeId => $score) {
                        $normalizedScores[$employeeId][$criteria->id] = $score > 0 ? $minScore / $score : 0;
                    }
                }
            }

            // Calculate weighted scores
            $results = [];
            foreach ($employees as $employee) {
                $totalScore = 0;

                foreach ($criterias as $criteria) {
                    $normalizedScore = $normalizedScores[$employee->id][$criteria->id] ?? 0;
                    $weight = $criteria->weight / 100;
                    $weightedScore = $normalizedScore * $weight;
                    $totalScore += $weightedScore;
                }

                // Get original data for comparison
                $originalScore = $originalData[$employee->id]['total_score'] ?? 0;
                $originalRanking = $originalData[$employee->id]['ranking'] ?? null;

                $results[] = (object)[
                    'employee_id' => $employee->id,
                    'employee' => $employee,
                    'total_score' => round($totalScore, 4),
                    'original_ranking' => $originalRanking,
                    'original_score' => $originalScore,
                    'score_change' => round($totalScore - $originalScore, 4),
                ];
            }

            // Sort by score and assign rankings
            usort($results, function ($a, $b) {
                return $b->total_score <=> $a->total_score;
            });

            foreach ($results as $index => $result) {
                $result->ranking = $index + 1;
            }

            return collect($results);

        } catch (\Exception $e) {
            \Log::error("Error in calculateSAWWithModifiedScores: " . $e->getMessage());
            throw new \Exception("Failed to calculate SAW with modified scores: " . $e->getMessage());
        }
    }

    /**
     * Calculate SAW with modified criteria
     */
    private function calculateSAWWithModifiedCriteria(string $evaluationPeriod, array $criteriaChanges): Collection
    {
        try {
            // Convert frontend format to backend expected format
            $convertedChanges = $this->convertCriteriaChangesFormat($criteriaChanges);
            
            // Get original evaluation results with proper relationships
            $originalResults = EvaluationResult::where('evaluation_period', $evaluationPeriod)
                ->with([
                    'employee',
                    'evaluationDetails' => function($query) use ($evaluationPeriod) {
                        $query->where('evaluation_period', $evaluationPeriod)->with('criteria');
                    }
                ])
                ->orderBy('ranking')
                ->get();

            if ($originalResults->isEmpty()) {
                throw new \Exception("No evaluation results found for period: {$evaluationPeriod}");
            }

        // Store original rankings and scores
        $originalData = [];
        foreach ($originalResults as $result) {
            $originalData[$result->employee_id] = [
                'ranking' => $result->ranking,
                'total_score' => $result->total_score
            ];
        }

        // Get original criteria weights
        $originalWeights = $this->getCriteriaWeights($evaluationPeriod);

        // Apply criteria changes (add/remove/modify weights)
        $modifiedWeights = $originalWeights;

        // Handle criteria additions
        if (isset($convertedChanges['add'])) {
            foreach ($convertedChanges['add'] as $criteriaId => $weight) {
                $modifiedWeights[$criteriaId] = $weight;
            }
        }

        // Handle criteria removals
        if (isset($convertedChanges['remove'])) {
            foreach ($convertedChanges['remove'] as $criteriaId) {
                unset($modifiedWeights[$criteriaId]);
            }
        }

        // Handle weight modifications
        if (isset($convertedChanges['modify'])) {
            foreach ($convertedChanges['modify'] as $criteriaId => $newWeight) {
                if (isset($modifiedWeights[$criteriaId])) {
                    $modifiedWeights[$criteriaId] = $newWeight;
                }
            }
        }

        // Normalize weights to 100%
        $totalWeight = array_sum($modifiedWeights);
        if ($totalWeight > 0) {
            foreach ($modifiedWeights as $criteriaId => $weight) {
                $modifiedWeights[$criteriaId] = ($weight / $totalWeight) * 100;
            }
        }

        // Get evaluations for the period
        $evaluations = Evaluation::with(['employee', 'criteria'])
            ->where('evaluation_period', $evaluationPeriod)
            ->get();

        if ($evaluations->isEmpty()) {
            throw new \Exception("No evaluation data found for period: {$evaluationPeriod}");
        }

        // Get all employees and create criteria objects for modified weights
        $employees = Employee::whereIn('id', $evaluations->pluck('employee_id')->unique())->get();
        $allCriterias = Criteria::all();
        
        // Create modified criteria collection
        $modifiedCriterias = collect();
        foreach ($modifiedWeights as $criteriaId => $weight) {
            if ($criteriaId < 0) {
                // Handle new criteria (negative ID)
                if (isset($convertedChanges['add_details'][$criteriaId])) {
                    $newCriteriaData = $convertedChanges['add_details'][$criteriaId];
                    $modifiedCriteria = new \stdClass();
                    $modifiedCriteria->id = $criteriaId;
                    $modifiedCriteria->name = $newCriteriaData['name'];
                    $modifiedCriteria->type = $newCriteriaData['type'];
                    $modifiedCriteria->weight = $weight;
                    $modifiedCriterias->push($modifiedCriteria);
                }
            } else {
                // Handle existing criteria
                $criteria = $allCriterias->firstWhere('id', $criteriaId);
                if ($criteria) {
                    $modifiedCriteria = $criteria->replicate();
                    $modifiedCriteria->weight = $weight;
                    $modifiedCriterias->push($modifiedCriteria);
                }
            }
        }

        if ($modifiedCriterias->isEmpty()) {
            throw new \Exception("No valid criteria found after modifications");
        }

        // Group evaluations by criteria for normalization
        $evaluationsByCriteria = $evaluations->groupBy('criteria_id');
        $normalizedScores = [];

        // Normalize scores for each modified criteria
        foreach ($modifiedCriterias as $criteria) {
            if ($criteria->id < 0) {
                // For new criteria, assign neutral score (0.5) to all employees
                foreach ($employees as $employee) {
                    $normalizedScores[$employee->id][$criteria->id] = 0.5;
                }
                continue;
            }
            
            $criteriaEvaluations = $evaluationsByCriteria->get($criteria->id, collect());
            $scores = $criteriaEvaluations->pluck('score')->toArray();

            if (empty($scores)) continue;

            // Check if criteria has isBenefit method or use type property
            $isBenefit = method_exists($criteria, 'isBenefit') ? $criteria->isBenefit() : ($criteria->type === 'benefit');
            
            if ($isBenefit) {
                // Benefit: Rij = Xij / Max(Xij)
                $maxScore = max($scores);
                foreach ($criteriaEvaluations as $evaluation) {
                    $normalizedScores[$evaluation->employee_id][$criteria->id] =
                        $maxScore > 0 ? $evaluation->score / $maxScore : 0;
                }
            } else {
                // Cost: Rij = Min(Xij) / Xij
                $minScore = min($scores);
                foreach ($criteriaEvaluations as $evaluation) {
                    $normalizedScores[$evaluation->employee_id][$criteria->id] =
                        $evaluation->score > 0 ? $minScore / $evaluation->score : 0;
                }
            }
        }

        // Calculate weighted scores with modified criteria and weights
        $results = [];
        foreach ($employees as $employee) {
            $totalScore = 0;

            foreach ($modifiedCriterias as $criteria) {
                $normalizedScore = $normalizedScores[$employee->id][$criteria->id] ?? 0;
                $weightedScore = $normalizedScore * ($criteria->weight / 100);
                $totalScore += $weightedScore;
            }

            // Get original data for comparison
            $originalScore = $originalData[$employee->id]['total_score'] ?? 0;
            $originalRanking = $originalData[$employee->id]['ranking'] ?? null;

            $results[] = (object)[
                'employee_id' => $employee->id,
                'employee' => $employee,
                'total_score' => round($totalScore, 4),
                'original_ranking' => $originalRanking,
                'original_score' => $originalScore,
                'score_change' => round($totalScore - $originalScore, 4),
            ];
        }

        // Sort by score and assign rankings
        usort($results, function ($a, $b) {
            return $b->total_score <=> $a->total_score;
        });

        foreach ($results as $index => $result) {
            $result->ranking = $index + 1;
        }

        $modifiedResults = collect($results);

        return $modifiedResults;

        } catch (\Exception $e) {
            \Log::error("Error in calculateSAWWithModifiedCriteria: " . $e->getMessage());
            throw new \Exception("Failed to calculate SAW with modified criteria: " . $e->getMessage());
        }
    }

    /**
     * Compare rankings between two result sets
     */
    private function compareRankings(Collection $original, Collection $modified): array
    {
        $changes = [];

        foreach ($original as $originalResult) {
            $modifiedResult = $modified->firstWhere('employee_id', $originalResult->employee_id);

            if ($modifiedResult) {
                $rankingChange = $originalResult->ranking - $modifiedResult->ranking;
                $scoreChange = $modifiedResult->total_score - $originalResult->total_score;

                $changes[] = [
                    'employee_id' => $originalResult->employee_id,
                    'employee_name' => $originalResult->employee->name,
                    'original_ranking' => $originalResult->ranking,
                    'new_ranking' => $modifiedResult->ranking,
                    'ranking_change' => $rankingChange,
                    'original_score' => $originalResult->total_score,
                    'new_score' => $modifiedResult->total_score,
                    'score_change' => $scoreChange,
                    'change_type' => $rankingChange > 0 ? 'improved' : ($rankingChange < 0 ? 'declined' : 'unchanged')
                ];
            }
        }

        return $changes;
    }

    /**
     * Calculate sensitivity metrics
     */
    private function calculateSensitivityMetrics(Collection $original, Collection $modified): array
    {
        $rankingChanges = [];
        $scoreChanges = [];

        foreach ($original as $originalResult) {
            $modifiedResult = $modified->firstWhere('employee_id', $originalResult->employee_id);
            if ($modifiedResult) {
                $rankingChanges[] = abs($originalResult->ranking - $modifiedResult->ranking);
                $scoreChanges[] = abs($originalResult->total_score - $modifiedResult->total_score);
            }
        }

        return [
            'avg_ranking_change' => count($rankingChanges) > 0 ? array_sum($rankingChanges) / count($rankingChanges) : 0,
            'max_ranking_change' => count($rankingChanges) > 0 ? max($rankingChanges) : 0,
            'avg_score_change' => count($scoreChanges) > 0 ? array_sum($scoreChanges) / count($scoreChanges) : 0,
            'max_score_change' => count($scoreChanges) > 0 ? max($scoreChanges) : 0,
            'stability_index' => $this->calculateStabilityIndex($rankingChanges)
        ];
    }

    /**
     * Get criteria weights for a specific evaluation period
     */
    private function getCriteriaWeights(string $evaluationPeriod): array
    {
        // Get criteria weights from the database
        $criteria = \App\Models\Criteria::all();

        $weights = [];
        foreach ($criteria as $criterion) {
            $weights[$criterion->id] = $criterion->weight;
        }

        return $weights;
    }

    /**
     * Generate sensitivity summary
     */
    private function generateSensitivitySummary(array $sensitivityData): array
    {
        $summary = [
            'most_sensitive_scenario' => null,
            'least_sensitive_scenario' => null,
            'avg_stability_index' => 0,
            'recommendations' => []
        ];

        $stabilityIndices = [];
        foreach ($sensitivityData as $scenario => $data) {
            $stabilityIndices[$scenario] = $data['metrics']['stability_index'];
        }

        if (!empty($stabilityIndices)) {
            $summary['most_sensitive_scenario'] = array_search(min($stabilityIndices), $stabilityIndices);
            $summary['least_sensitive_scenario'] = array_search(max($stabilityIndices), $stabilityIndices);
            $summary['avg_stability_index'] = array_sum($stabilityIndices) / count($stabilityIndices);
        }

        return $summary;
    }

    /**
     * Calculate growth rates
     */
    private function calculateGrowthRates(array $periods): array
    {
        // Implementation for growth rate calculation
        return [];
    }

    /**
     * Calculate performance variance
     */
    private function calculatePerformanceVariance(array $periods): array
    {
        // Implementation for variance analysis
        return [];
    }

    /**
     * Calculate criteria correlation
     */
    private function calculateCriteriaCorrelation(array $periods): array
    {
        // Implementation for correlation analysis
        return [];
    }

    /**
     * Calculate department trends
     */
    private function calculateDepartmentTrends(array $periods): array
    {
        // Implementation for department trend analysis
        return [];
    }

    /**
     * Calculate distribution analysis
     */
    private function calculateDistributionAnalysis(array $periods): array
    {
        // Implementation for distribution analysis
        return [];
    }

    /**
     * Calculate period changes
     */
    private function calculatePeriodChanges(array $comparison): array
    {
        $periods = array_keys($comparison);
        $changes = [];

        for ($i = 1; $i < count($periods); $i++) {
            $currentPeriod = $periods[$i];
            $previousPeriod = $periods[$i - 1];

            if (!isset($comparison[$currentPeriod]) || !isset($comparison[$previousPeriod])) {
                continue;
            }

            $currentStats = $comparison[$currentPeriod]['statistics'];
            $previousStats = $comparison[$previousPeriod]['statistics'];

            $changes[$currentPeriod] = [
                'avg_score_change' => $currentStats['avg_score'] - $previousStats['avg_score'],
                'max_score_change' => $currentStats['max_score'] - $previousStats['max_score'],
                'min_score_change' => $currentStats['min_score'] - $previousStats['min_score'],
                'employee_count_change' => $currentStats['total_employees'] - $previousStats['total_employees']
            ];
        }

        return $changes;
    }

    /**
     * Calculate linear trend forecast
     */
    private function calculateLinearTrendForecast(Collection $historicalResults, int $periodsAhead): array
    {
        // Simple linear regression implementation
        $n = $historicalResults->count();
        $x = range(1, $n);
        $y = $historicalResults->pluck('total_score')->toArray();

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        for ($i = 1; $i <= $periodsAhead; $i++) {
            $forecast[] = [
                'period' => $n + $i,
                'predicted_score' => $intercept + $slope * ($n + $i)
            ];
        }

        return $forecast;
    }

    /**
     * Calculate moving average forecast
     */
    private function calculateMovingAverageForecast(Collection $historicalResults, int $periodsAhead): array
    {
        $windowSize = min(3, $historicalResults->count());
        $recentScores = $historicalResults->slice(-$windowSize)->pluck('total_score');
        $average = $recentScores->avg();

        $forecast = [];
        for ($i = 1; $i <= $periodsAhead; $i++) {
            $forecast[] = [
                'period' => $historicalResults->count() + $i,
                'predicted_score' => $average
            ];
        }

        return $forecast;
    }

    /**
     * Calculate weighted moving average forecast
     */
    private function calculateWeightedMovingAverageForecast(Collection $historicalResults, int $periodsAhead): array
    {
        $windowSize = min(3, $historicalResults->count());
        $recentScores = $historicalResults->slice(-$windowSize)->pluck('total_score')->toArray();

        // Assign weights (more recent = higher weight)
        $weights = [];
        for ($i = 0; $i < $windowSize; $i++) {
            $weights[] = ($i + 1) / array_sum(range(1, $windowSize));
        }

        $weightedSum = 0;
        for ($i = 0; $i < count($recentScores); $i++) {
            $weightedSum += $recentScores[$i] * $weights[$i];
        }

        $forecast = [];
        for ($i = 1; $i <= $periodsAhead; $i++) {
            $forecast[] = [
                'period' => $historicalResults->count() + $i,
                'predicted_score' => $weightedSum
            ];
        }

        return $forecast;
    }

    /**
     * Calculate confidence intervals
     */
    private function calculateConfidenceIntervals(Collection $historicalResults, float $confidenceLevel = 0.95): array
    {
        $scores = $historicalResults->pluck('total_score');
        $mean = $scores->avg();
        $stdDev = $this->calculateStandardDeviation($scores);
        
        // Z-scores for different confidence levels
        $zScores = [
            0.90 => 1.645,
            0.95 => 1.96,
            0.99 => 2.576
        ];
        
        $zScore = $zScores[$confidenceLevel] ?? 1.96; // Default to 95% if not found
        
        return [
            'confidence_level' => $confidenceLevel,
            'lower' => $mean - ($zScore * $stdDev),
            'upper' => $mean + ($zScore * $stdDev)
        ];
    }

    /**
     * Calculate forecast accuracy
     */
    private function calculateForecastAccuracy(Collection $historicalResults): array
    {
        if ($historicalResults->count() < 4) {
            return ['accuracy' => 'insufficient_data'];
        }

        // Use last 3 periods to test accuracy
        $testData = $historicalResults->slice(-3);
        $trainData = $historicalResults->take($historicalResults->count() - 3);

        // Calculate forecast for test period
        $forecast = $this->calculateLinearTrendForecast($trainData, 3);

        // Calculate accuracy metrics
        $errors = [];
        foreach ($testData as $index => $actual) {
            if (isset($forecast[$index])) {
                $errors[] = abs($actual->total_score - $forecast[$index]['predicted_score']);
            }
        }

        return [
            'mean_absolute_error' => count($errors) > 0 ? array_sum($errors) / count($errors) : 0,
            'accuracy_percentage' => count($errors) > 0 ? (1 - (array_sum($errors) / count($errors))) * 100 : 0
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation(Collection $values): float
    {
        $mean = $values->avg();
        $squaredDiffs = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        });

        return sqrt($squaredDiffs->avg());
    }

    /**
     * Calculate stability index
     */
    private function calculateStabilityIndex(array $rankingChanges): float
    {
        if (empty($rankingChanges)) {
            return 1.0;
        }

        $maxPossibleChange = count($rankingChanges);
        $avgChange = array_sum($rankingChanges) / count($rankingChanges);

        return 1 - ($avgChange / $maxPossibleChange);
    }
}