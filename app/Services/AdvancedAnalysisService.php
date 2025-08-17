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
        $results = [];
        
        foreach ($scenarios as $scenarioName => $scenario) {
            $scenarioResults = [];
            
            // Handle weight changes
            if (isset($scenario['weight_changes'])) {
                $scenarioResults['weight_impact'] = $this->calculateSAWWithModifiedWeights(
                    $evaluationPeriod,
                    $scenario['weight_changes']
                );
            }
            
            // Handle score changes for specific employees
            if (isset($scenario['score_changes'])) {
                $scenarioResults['score_impact'] = $this->calculateSAWWithModifiedScores(
                    $evaluationPeriod,
                    $scenario['score_changes']
                );
            }
            
            // Handle criteria additions/removals
            if (isset($scenario['criteria_changes'])) {
                $scenarioResults['criteria_impact'] = $this->calculateSAWWithModifiedCriteria(
                    $evaluationPeriod,
                    $scenario['criteria_changes']
                );
            }
            
            $results[$scenarioName] = $scenarioResults;
        }
        
        return $results;
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
    public function multiPeriodComparison(array $periods, int $employeeId = null): array
    {
        $query = EvaluationResult::with('employee')
            ->whereIn('evaluation_period', $periods);
            
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        
        $results = $query->orderBy('evaluation_period')
            ->orderBy('ranking')
            ->get()
            ->groupBy('evaluation_period');
            
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
     * Performance forecasting
     */
    public function performanceForecast(int $employeeId, int $periodsAhead = 3): array
    {
        // Get historical data for the employee
        $historicalResults = EvaluationResult::where('employee_id', $employeeId)
            ->orderBy('evaluation_period')
            ->get();
            
        if ($historicalResults->count() < 3) {
            throw new \Exception('Insufficient historical data for forecasting (minimum 3 periods required)');
        }
        
        // Simple linear regression for trend
        $forecast = $this->calculateLinearTrendForecast($historicalResults, $periodsAhead);
        
        // Moving average forecast
        $movingAverageForecast = $this->calculateMovingAverageForecast($historicalResults, $periodsAhead);
        
        // Weighted moving average (recent periods have more weight)
        $weightedForecast = $this->calculateWeightedMovingAverageForecast($historicalResults, $periodsAhead);
        
        return [
            'historical_data' => $historicalResults,
            'forecasts' => [
                'linear_trend' => $forecast,
                'moving_average' => $movingAverageForecast,
                'weighted_average' => $weightedForecast
            ],
            'confidence_intervals' => $this->calculateConfidenceIntervals($historicalResults),
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
        // Get evaluations for the period
        $evaluations = Evaluation::with(['employee', 'criteria'])
            ->where('evaluation_period', $evaluationPeriod)
            ->get();
            
        $employees = Employee::whereIn('id', $evaluations->pluck('employee_id')->unique())->get();
        $criterias = Criteria::whereIn('id', array_keys($newWeights))->get();
        
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
                $weightedScore = $normalizedScore * ($newWeights[$criteria->id] / 100);
                $totalScore += $weightedScore;
            }
            
            $results[] = (object)[
                'employee_id' => $employee->id,
                'employee' => $employee,
                'total_score' => round($totalScore, 4),
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
     * Calculate SAW with modified scores
     */
    private function calculateSAWWithModifiedScores(string $evaluationPeriod, array $scoreChanges): Collection
    {
        // Implementation for score modification scenarios
        // This would modify specific employee scores and recalculate
        return collect();
    }

    /**
     * Calculate SAW with modified criteria
     */
    private function calculateSAWWithModifiedCriteria(string $evaluationPeriod, array $criteriaChanges): Collection
    {
        // Implementation for criteria modification scenarios
        // This would add/remove criteria and recalculate
        return collect();
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
        // Implementation for period-to-period change calculation
        return [];
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
        $recentScores = $historicalResults->takeLast($windowSize)->pluck('total_score');
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
        $recentScores = $historicalResults->takeLast($windowSize)->pluck('total_score')->toArray();
        
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
    private function calculateConfidenceIntervals(Collection $historicalResults): array
    {
        $scores = $historicalResults->pluck('total_score');
        $mean = $scores->avg();
        $stdDev = $this->calculateStandardDeviation($scores);
        
        return [
            'confidence_95' => [
                'lower' => $mean - (1.96 * $stdDev),
                'upper' => $mean + (1.96 * $stdDev)
            ],
            'confidence_90' => [
                'lower' => $mean - (1.645 * $stdDev),
                'upper' => $mean + (1.645 * $stdDev)
            ]
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
        $testData = $historicalResults->takeLast(3);
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