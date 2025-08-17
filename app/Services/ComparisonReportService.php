<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ComparisonReportService
{
    /**
     * Compare performance across multiple periods
     */
    public function comparePeriods(array $periods): array
    {
        $startTime = microtime(true);
        
        try {
            // Validate periods
            $validPeriods = $this->validatePeriods($periods);
            
            if (count($validPeriods) < 2) {
                throw new \InvalidArgumentException("At least 2 periods are required for comparison");
            }
            
            // Get data for all periods
            $periodData = [];
            foreach ($validPeriods as $period) {
                $periodData[$period] = $this->getPeriodData($period);
            }
            
            // Perform comprehensive comparison
            $comparison = [
                'periods' => $validPeriods,
                'period_data' => $periodData,
                'period_comparison' => $this->analyzePeriodComparison($periodData),
                'employee_progression' => $this->analyzeEmployeeProgression($periodData),
                'criteria_trends' => $this->analyzeCriteriaTrends($periodData),
                'ranking_stability' => $this->analyzeRankingStability($periodData),
                'performance_metrics' => $this->calculatePerformanceMetrics($periodData),
                'statistical_analysis' => $this->performStatisticalAnalysis($periodData),
                'insights' => $this->generateInsights($periodData),
                'execution_time' => microtime(true) - $startTime
            ];

            Log::info("Period comparison completed", [
                'periods' => $validPeriods,
                'execution_time' => $comparison['execution_time']
            ]);

            return $comparison;

        } catch (\Exception $e) {
            Log::error("Period comparison failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Compare specific employees across periods
     */
    public function compareEmployees(array $employeeIds, array $periods = []): array
    {
        try {
            // Get available periods if not specified
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $employees = Employee::whereIn('id', $employeeIds)->get();
            
            if ($employees->isEmpty()) {
                throw new \InvalidArgumentException("No valid employees found");
            }
            
            $comparison = [];
            
            foreach ($employees as $employee) {
                $employeeData = $this->getEmployeePerformanceAcrossPeriods($employee->id, $periods);
                
                $comparison[$employee->id] = [
                    'employee_info' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'employee_code' => $employee->employee_code,
                        'position' => $employee->position,
                        'department' => $employee->department
                    ],
                    'performance_data' => $employeeData,
                    'trend_analysis' => $this->analyzeEmployeeTrend($employeeData),
                    'consistency_score' => $this->calculateConsistencyScore($employeeData),
                    'improvement_areas' => $this->identifyImprovementAreas($employee->id, $periods),
                    'strengths' => $this->identifyStrengths($employee->id, $periods)
                ];
            }
            
            return [
                'employees' => $comparison,
                'cross_employee_analysis' => $this->performCrossEmployeeAnalysis($comparison),
                'relative_performance' => $this->analyzeRelativePerformance($comparison),
                'recommendations' => $this->generateEmployeeRecommendations($comparison)
            ];

        } catch (\Exception $e) {
            Log::error("Employee comparison failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Compare criteria impact across periods
     */
    public function compareCriteriaImpact(array $periods = []): array
    {
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $criterias = Criteria::all();
            $comparison = [];
            
            foreach ($criterias as $criteria) {
                $criteriaData = $this->getCriteriaImpactAcrossPeriods($criteria->id, $periods);
                
                $comparison[$criteria->id] = [
                    'criteria_info' => [
                        'id' => $criteria->id,
                        'name' => $criteria->name,
                        'weight' => $criteria->weight,
                        'type' => $criteria->type
                    ],
                    'impact_data' => $criteriaData,
                    'impact_trend' => $this->analyzeCriteriaImpactTrend($criteriaData),
                    'discrimination_power' => $this->calculateDiscriminationPower($criteria->id, $periods),
                    'effectiveness_score' => $this->calculateCriteriaEffectiveness($criteria->id, $periods)
                ];
            }
            
            return [
                'criteria_comparison' => $comparison,
                'overall_criteria_analysis' => $this->analyzeOverallCriteriaEffectiveness($comparison),
                'weight_optimization_suggestions' => $this->suggestWeightOptimizations($comparison),
                'criteria_recommendations' => $this->generateCriteriaRecommendations($comparison)
            ];

        } catch (\Exception $e) {
            Log::error("Criteria comparison failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate departmental comparison report
     */
    public function compareDepartments(array $periods = []): array
    {
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $departments = Employee::distinct('department')->pluck('department')->toArray();
            $comparison = [];
            
            foreach ($departments as $department) {
                $departmentData = $this->getDepartmentPerformanceAcrossPeriods($department, $periods);
                
                $comparison[$department] = [
                    'department_name' => $department,
                    'employee_count' => Employee::where('department', $department)->count(),
                    'performance_data' => $departmentData,
                    'trend_analysis' => $this->analyzeDepartmentTrend($departmentData),
                    'ranking_distribution' => $this->analyzeDepartmentRankingDistribution($department, $periods),
                    'improvement_rate' => $this->calculateDepartmentImprovementRate($departmentData)
                ];
            }
            
            return [
                'departments' => $comparison,
                'inter_department_analysis' => $this->performInterDepartmentAnalysis($comparison),
                'department_rankings' => $this->rankDepartmentsByPerformance($comparison),
                'recommendations' => $this->generateDepartmentRecommendations($comparison)
            ];

        } catch (\Exception $e) {
            Log::error("Department comparison failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate comprehensive benchmark report
     */
    public function generateBenchmarkReport(string $targetPeriod, array $benchmarkPeriods = []): array
    {
        try {
            if (empty($benchmarkPeriods)) {
                $benchmarkPeriods = $this->getRecentPeriods($targetPeriod, 3);
            }
            
            $targetData = $this->getPeriodData($targetPeriod);
            $benchmarkData = [];
            
            foreach ($benchmarkPeriods as $period) {
                $benchmarkData[$period] = $this->getPeriodData($period);
            }
            
            return [
                'target_period' => $targetPeriod,
                'benchmark_periods' => $benchmarkPeriods,
                'target_data' => $targetData,
                'benchmark_data' => $benchmarkData,
                'benchmark_analysis' => $this->performBenchmarkAnalysis($targetData, $benchmarkData),
                'performance_gaps' => $this->identifyPerformanceGaps($targetData, $benchmarkData),
                'improvement_opportunities' => $this->identifyImprovementOpportunities($targetData, $benchmarkData),
                'best_practices' => $this->identifyBestPractices($targetData, $benchmarkData),
                'action_plan' => $this->generateBenchmarkActionPlan($targetData, $benchmarkData)
            ];

        } catch (\Exception $e) {
            Log::error("Benchmark report generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper methods
     */
    protected function validatePeriods(array $periods): array
    {
        $validPeriods = [];
        
        foreach ($periods as $period) {
            $exists = EvaluationResult::where('evaluation_period', $period)->exists();
            if ($exists) {
                $validPeriods[] = $period;
            }
        }
        
        return $validPeriods;
    }

    protected function getPeriodData(string $period): array
    {
        $results = EvaluationResult::with('employee')
            ->where('evaluation_period', $period)
            ->orderBy('ranking')
            ->get();
        
        $evaluations = Evaluation::where('evaluation_period', $period)
            ->with(['employee', 'criteria'])
            ->get();
        
        return [
            'period' => $period,
            'results' => $results->map(function($result) {
                return [
                    'employee_id' => $result->employee_id,
                    'employee_name' => $result->employee->name,
                    'employee_code' => $result->employee->employee_code,
                    'department' => $result->employee->department,
                    'total_score' => (float) $result->total_score,
                    'ranking' => $result->ranking
                ];
            })->toArray(),
            'evaluations' => $evaluations->groupBy('employee_id')->map(function($employeeEvaluations) {
                return $employeeEvaluations->mapWithKeys(function($evaluation) {
                    return [$evaluation->criteria->name => $evaluation->score];
                })->toArray();
            })->toArray(),
            'statistics' => $this->calculatePeriodStatistics($results)
        ];
    }

    protected function calculatePeriodStatistics($results): array
    {
        $scores = $results->pluck('total_score')->toArray();
        
        if (empty($scores)) {
            return [
                'count' => 0,
                'mean' => 0,
                'median' => 0,
                'std_dev' => 0,
                'min' => 0,
                'max' => 0
            ];
        }
        
        return [
            'count' => count($scores),
            'mean' => array_sum($scores) / count($scores),
            'median' => $this->calculateMedian($scores),
            'std_dev' => $this->calculateStandardDeviation($scores),
            'min' => min($scores),
            'max' => max($scores),
            'range' => max($scores) - min($scores),
            'coefficient_variation' => $this->calculateCoefficientOfVariation($scores)
        ];
    }

    protected function analyzePeriodComparison(array $periodData): array
    {
        $periods = array_keys($periodData);
        $comparison = [];
        
        // Compare each period with the previous one
        for ($i = 1; $i < count($periods); $i++) {
            $currentPeriod = $periods[$i];
            $previousPeriod = $periods[$i - 1];
            
            $currentData = $periodData[$currentPeriod];
            $previousData = $periodData[$previousPeriod];
            
            $comparison[$currentPeriod] = [
                'compared_to' => $previousPeriod,
                'score_changes' => $this->compareScores($previousData['statistics'], $currentData['statistics']),
                'ranking_changes' => $this->compareRankings($previousData['results'], $currentData['results']),
                'performance_shift' => $this->analyzePerformanceShift($previousData['results'], $currentData['results'])
            ];
        }
        
        return $comparison;
    }

    protected function analyzeEmployeeProgression(array $periodData): array
    {
        $periods = array_keys($periodData);
        $employees = [];
        
        // Collect all unique employees
        foreach ($periodData as $period => $data) {
            foreach ($data['results'] as $result) {
                $employees[$result['employee_id']] = $result['employee_name'];
            }
        }
        
        $progression = [];
        
        foreach ($employees as $employeeId => $employeeName) {
            $employeeData = [];
            
            foreach ($periods as $period) {
                $result = collect($periodData[$period]['results'])
                    ->where('employee_id', $employeeId)
                    ->first();
                
                if ($result) {
                    $employeeData[$period] = [
                        'total_score' => $result['total_score'],
                        'ranking' => $result['ranking']
                    ];
                }
            }
            
            if (count($employeeData) >= 2) {
                $progression[$employeeId] = [
                    'employee_name' => $employeeName,
                    'data' => $employeeData,
                    'trend' => $this->calculateTrend(array_column($employeeData, 'total_score')),
                    'ranking_trend' => $this->calculateTrend(array_map(function($x) { return -$x; }, array_column($employeeData, 'ranking'))),
                    'volatility' => $this->calculateVolatility(array_column($employeeData, 'total_score')),
                    'improvement_rate' => $this->calculateImprovementRate($employeeData)
                ];
            }
        }
        
        return $progression;
    }

    protected function analyzeCriteriaTrends(array $periodData): array
    {
        $criterias = Criteria::all();
        $trends = [];
        
        foreach ($criterias as $criteria) {
            $criteriaScores = [];
            
            foreach ($periodData as $period => $data) {
                $scores = [];
                
                foreach ($data['evaluations'] as $employeeId => $evaluations) {
                    if (isset($evaluations[$criteria->name])) {
                        $scores[] = $evaluations[$criteria->name];
                    }
                }
                
                if (!empty($scores)) {
                    $criteriaScores[$period] = [
                        'mean' => array_sum($scores) / count($scores),
                        'std_dev' => $this->calculateStandardDeviation($scores),
                        'min' => min($scores),
                        'max' => max($scores)
                    ];
                }
            }
            
            if (count($criteriaScores) >= 2) {
                $means = array_column($criteriaScores, 'mean');
                
                $trends[$criteria->id] = [
                    'criteria_name' => $criteria->name,
                    'data' => $criteriaScores,
                    'trend' => $this->calculateTrend($means),
                    'volatility' => $this->calculateVolatility($means),
                    'improvement_consistency' => $this->calculateConsistency($means)
                ];
            }
        }
        
        return $trends;
    }

    protected function analyzeRankingStability(array $periodData): array
    {
        $periods = array_keys($periodData);
        $stability = [];
        
        for ($i = 1; $i < count($periods); $i++) {
            $currentPeriod = $periods[$i];
            $previousPeriod = $periods[$i - 1];
            
            $currentRankings = collect($periodData[$currentPeriod]['results'])
                ->pluck('ranking', 'employee_id')
                ->toArray();
            
            $previousRankings = collect($periodData[$previousPeriod]['results'])
                ->pluck('ranking', 'employee_id')
                ->toArray();
            
            $changes = [];
            $totalChanges = 0;
            
            foreach ($currentRankings as $employeeId => $currentRanking) {
                if (isset($previousRankings[$employeeId])) {
                    $change = abs($currentRanking - $previousRankings[$employeeId]);
                    $changes[$employeeId] = $change;
                    $totalChanges += $change;
                }
            }
            
            $stability[$currentPeriod] = [
                'compared_to' => $previousPeriod,
                'total_employees' => count($changes),
                'total_ranking_changes' => $totalChanges,
                'average_change' => count($changes) > 0 ? $totalChanges / count($changes) : 0,
                'stability_score' => $this->calculateStabilityScore($changes),
                'most_volatile_employees' => $this->getMostVolatileEmployees($changes),
                'most_stable_employees' => $this->getMostStableEmployees($changes)
            ];
        }
        
        return $stability;
    }

    protected function calculatePerformanceMetrics(array $periodData): array
    {
        $metrics = [];
        
        foreach ($periodData as $period => $data) {
            $scores = array_column($data['results'], 'total_score');
            
            $metrics[$period] = [
                'performance_index' => array_sum($scores) / count($scores),
                'excellence_rate' => count(array_filter($scores, fn($s) => $s >= 0.8)) / count($scores),
                'improvement_needed_rate' => count(array_filter($scores, fn($s) => $s < 0.6)) / count($scores),
                'performance_spread' => max($scores) - min($scores),
                'gini_coefficient' => $this->calculateGiniCoefficient($scores)
            ];
        }
        
        return $metrics;
    }

    protected function performStatisticalAnalysis(array $periodData): array
    {
        $periods = array_keys($periodData);
        $analysis = [];
        
        // Correlation analysis between periods
        if (count($periods) >= 2) {
            $correlations = [];
            
            for ($i = 0; $i < count($periods) - 1; $i++) {
                for ($j = $i + 1; $j < count($periods); $j++) {
                    $period1 = $periods[$i];
                    $period2 = $periods[$j];
                    
                    $correlation = $this->calculatePeriodCorrelation(
                        $periodData[$period1]['results'],
                        $periodData[$period2]['results']
                    );
                    
                    $correlations[] = [
                        'period1' => $period1,
                        'period2' => $period2,
                        'correlation' => $correlation
                    ];
                }
            }
            
            $analysis['correlations'] = $correlations;
        }
        
        // Variance analysis
        $periodScores = [];
        foreach ($periodData as $period => $data) {
            $periodScores[$period] = array_column($data['results'], 'total_score');
        }
        
        $analysis['variance_analysis'] = $this->performVarianceAnalysis($periodScores);
        
        return $analysis;
    }

    protected function generateInsights(array $periodData): array
    {
        $insights = [];
        $periods = array_keys($periodData);
        
        // Overall trend insight
        $overallScores = [];
        foreach ($periodData as $period => $data) {
            $overallScores[$period] = $data['statistics']['mean'];
        }
        
        $overallTrend = $this->calculateTrend(array_values($overallScores));
        
        if ($overallTrend > 0.1) {
            $insights[] = [
                'type' => 'positive_trend',
                'title' => 'Overall Performance Improvement',
                'description' => 'The organization shows consistent performance improvement across evaluation periods.',
                'confidence' => 'high'
            ];
        } elseif ($overallTrend < -0.1) {
            $insights[] = [
                'type' => 'negative_trend',
                'title' => 'Performance Decline Detected',
                'description' => 'There is a concerning downward trend in overall performance that requires attention.',
                'confidence' => 'high'
            ];
        }
        
        // Variability insight
        $latestPeriod = end($periods);
        $latestVariability = $periodData[$latestPeriod]['statistics']['coefficient_variation'];
        
        if ($latestVariability > 0.3) {
            $insights[] = [
                'type' => 'high_variability',
                'title' => 'High Performance Variability',
                'description' => 'There is significant variability in employee performance, indicating potential inconsistencies in evaluation or training.',
                'confidence' => 'medium'
            ];
        }
        
        return $insights;
    }

    protected function getEmployeePerformanceAcrossPeriods(int $employeeId, array $periods): array
    {
        $data = [];
        
        foreach ($periods as $period) {
            $result = EvaluationResult::where('employee_id', $employeeId)
                ->where('evaluation_period', $period)
                ->first();
            
            if ($result) {
                $evaluations = Evaluation::where('employee_id', $employeeId)
                    ->where('evaluation_period', $period)
                    ->with('criteria')
                    ->get();
                
                $criteriaScores = [];
                foreach ($evaluations as $evaluation) {
                    $criteriaScores[$evaluation->criteria->name] = $evaluation->score;
                }
                
                $data[$period] = [
                    'total_score' => (float) $result->total_score,
                    'ranking' => $result->ranking,
                    'criteria_scores' => $criteriaScores
                ];
            }
        }
        
        return $data;
    }

    protected function analyzeEmployeeTrend(array $employeeData): array
    {
        $scores = array_column($employeeData, 'total_score');
        $rankings = array_column($employeeData, 'ranking');
        
        return [
            'score_trend' => $this->calculateTrend($scores),
            'ranking_trend' => $this->calculateTrend(array_map(function($x) { return -$x; }, $rankings)),
            'consistency' => $this->calculateConsistency($scores),
            'volatility' => $this->calculateVolatility($scores),
            'periods_evaluated' => count($employeeData)
        ];
    }

    protected function calculateConsistencyScore(array $employeeData): float
    {
        $scores = array_column($employeeData, 'total_score');
        
        if (count($scores) < 2) return 1.0;
        
        $mean = array_sum($scores) / count($scores);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $scores)) / count($scores);
        
        // Consistency score: higher variance = lower consistency
        return max(0, 1 - ($variance * 4)); // Scale to 0-1
    }

    protected function identifyImprovementAreas(int $employeeId, array $periods): array
    {
        $areas = [];
        $criterias = Criteria::all();
        
        foreach ($criterias as $criteria) {
            $scores = [];
            
            foreach ($periods as $period) {
                $evaluation = Evaluation::where('employee_id', $employeeId)
                    ->where('criteria_id', $criteria->id)
                    ->where('evaluation_period', $period)
                    ->first();
                
                if ($evaluation) {
                    $scores[] = $evaluation->score;
                }
            }
            
            if (count($scores) >= 2) {
                $avgScore = array_sum($scores) / count($scores);
                $trend = $this->calculateTrend($scores);
                
                if ($avgScore < 70 || $trend < -0.1) {
                    $areas[] = [
                        'criteria_name' => $criteria->name,
                        'average_score' => $avgScore,
                        'trend' => $trend,
                        'priority' => $avgScore < 60 ? 'high' : 'medium'
                    ];
                }
            }
        }
        
        // Sort by priority and average score
        usort($areas, function($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] === 'high' ? -1 : 1;
            }
            return $a['average_score'] <=> $b['average_score'];
        });
        
        return $areas;
    }

    protected function identifyStrengths(int $employeeId, array $periods): array
    {
        $strengths = [];
        $criterias = Criteria::all();
        
        foreach ($criterias as $criteria) {
            $scores = [];
            
            foreach ($periods as $period) {
                $evaluation = Evaluation::where('employee_id', $employeeId)
                    ->where('criteria_id', $criteria->id)
                    ->where('evaluation_period', $period)
                    ->first();
                
                if ($evaluation) {
                    $scores[] = $evaluation->score;
                }
            }
            
            if (count($scores) >= 2) {
                $avgScore = array_sum($scores) / count($scores);
                $trend = $this->calculateTrend($scores);
                
                if ($avgScore >= 80 && $trend >= 0) {
                    $strengths[] = [
                        'criteria_name' => $criteria->name,
                        'average_score' => $avgScore,
                        'trend' => $trend,
                        'consistency' => $this->calculateConsistency($scores)
                    ];
                }
            }
        }
        
        // Sort by average score descending
        usort($strengths, fn($a, $b) => $b['average_score'] <=> $a['average_score']);
        
        return $strengths;
    }

    // Statistical helper methods
    protected function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        
        if ($count % 2 == 0) {
            return ($values[$count/2 - 1] + $values[$count/2]) / 2;
        } else {
            return $values[floor($count/2)];
        }
    }

    protected function calculateStandardDeviation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(fn($value) => pow($value - $mean, 2), $values);
        
        $variance = array_sum($squaredDifferences) / count($values);
        return sqrt($variance);
    }

    protected function calculateCoefficientOfVariation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $stdDev = $this->calculateStandardDeviation($values);
        
        return $mean != 0 ? ($stdDev / $mean) : 0;
    }

    protected function calculateTrend(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        
        $x = range(1, $n);
        $y = $values;
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($i) => $x[$i] * $y[$i], range(0, $n-1)));
        $sumXX = array_sum(array_map(fn($val) => $val * $val, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        
        return $slope;
    }

    protected function calculateVolatility(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $changes = [];
        for ($i = 1; $i < count($values); $i++) {
            $changes[] = abs($values[$i] - $values[$i-1]);
        }
        
        return array_sum($changes) / count($changes);
    }

    protected function calculateConsistency(array $values): float
    {
        if (count($values) < 2) return 1.0;
        
        $volatility = $this->calculateVolatility($values);
        $mean = array_sum($values) / count($values);
        
        return $mean > 0 ? max(0, 1 - ($volatility / $mean)) : 0;
    }

    protected function calculateImprovementRate(array $employeeData): float
    {
        $periods = array_keys($employeeData);
        if (count($periods) < 2) return 0;
        
        $firstPeriod = reset($periods);
        $lastPeriod = end($periods);
        
        $firstScore = $employeeData[$firstPeriod]['total_score'];
        $lastScore = $employeeData[$lastPeriod]['total_score'];
        
        return $firstScore > 0 ? ($lastScore - $firstScore) / $firstScore : 0;
    }

    protected function compareScores(array $previousStats, array $currentStats): array
    {
        return [
            'mean_change' => $currentStats['mean'] - $previousStats['mean'],
            'median_change' => $currentStats['median'] - $previousStats['median'],
            'std_dev_change' => $currentStats['std_dev'] - $previousStats['std_dev'],
            'range_change' => $currentStats['range'] - $previousStats['range']
        ];
    }

    protected function compareRankings(array $previousResults, array $currentResults): array
    {
        $changes = [];
        
        foreach ($currentResults as $current) {
            $previous = collect($previousResults)
                ->where('employee_id', $current['employee_id'])
                ->first();
            
            if ($previous) {
                $changes[] = [
                    'employee_id' => $current['employee_id'],
                    'employee_name' => $current['employee_name'],
                    'previous_ranking' => $previous['ranking'],
                    'current_ranking' => $current['ranking'],
                    'ranking_change' => $current['ranking'] - $previous['ranking']
                ];
            }
        }
        
        return [
            'individual_changes' => $changes,
            'total_changes' => array_sum(array_map(fn($c) => abs($c['ranking_change']), $changes)),
            'improved' => count(array_filter($changes, fn($c) => $c['ranking_change'] < 0)),
            'declined' => count(array_filter($changes, fn($c) => $c['ranking_change'] > 0)),
            'unchanged' => count(array_filter($changes, fn($c) => $c['ranking_change'] == 0))
        ];
    }

    protected function analyzePerformanceShift(array $previousResults, array $currentResults): array
    {
        $previousScores = array_column($previousResults, 'total_score');
        $currentScores = array_column($currentResults, 'total_score');
        
        return [
            'overall_shift' => array_sum($currentScores) - array_sum($previousScores),
            'average_shift' => (array_sum($currentScores) / count($currentScores)) - (array_sum($previousScores) / count($previousScores)),
            'variance_shift' => $this->calculateVariance($currentScores) - $this->calculateVariance($previousScores),
            'distribution_shift' => $this->analyzeDistributionShift($previousScores, $currentScores)
        ];
    }

    protected function calculateVariance(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(fn($value) => pow($value - $mean, 2), $values);
        
        return array_sum($squaredDifferences) / count($values);
    }

    protected function analyzeDistributionShift(array $previousScores, array $currentScores): array
    {
        // Simple distribution analysis using quartiles
        sort($previousScores);
        sort($currentScores);
        
        $prevQ1 = $this->calculatePercentile($previousScores, 25);
        $prevQ2 = $this->calculatePercentile($previousScores, 50);
        $prevQ3 = $this->calculatePercentile($previousScores, 75);
        
        $currQ1 = $this->calculatePercentile($currentScores, 25);
        $currQ2 = $this->calculatePercentile($currentScores, 50);
        $currQ3 = $this->calculatePercentile($currentScores, 75);
        
        return [
            'q1_shift' => $currQ1 - $prevQ1,
            'q2_shift' => $currQ2 - $prevQ2,
            'q3_shift' => $currQ3 - $prevQ3,
            'overall_direction' => ($currQ2 > $prevQ2) ? 'upward' : 'downward'
        ];
    }

    protected function calculatePercentile(array $sortedValues, float $percentile): float
    {
        $index = ($percentile / 100) * (count($sortedValues) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower == $upper) {
            return $sortedValues[$lower];
        }
        
        $weight = $index - $lower;
        return $sortedValues[$lower] * (1 - $weight) + $sortedValues[$upper] * $weight;
    }

    protected function calculateStabilityScore(array $changes): float
    {
        if (empty($changes)) return 1.0;
        
        $totalEmployees = count($changes);
        $totalChanges = array_sum($changes);
        $maxPossibleChanges = $totalEmployees * ($totalEmployees - 1) / 2;
        
        return max(0, 1 - ($totalChanges / $maxPossibleChanges));
    }

    protected function getMostVolatileEmployees(array $changes): array
    {
        arsort($changes);
        return array_slice($changes, 0, 5, true);
    }

    protected function getMostStableEmployees(array $changes): array
    {
        asort($changes);
        return array_slice($changes, 0, 5, true);
    }

    protected function calculateGiniCoefficient(array $values): float
    {
        sort($values);
        $n = count($values);
        $sum = array_sum($values);
        
        if ($sum == 0) return 0;
        
        $index = 0;
        foreach ($values as $i => $value) {
            $index += ($i + 1) * $value;
        }
        
        return (2 * $index) / ($n * $sum) - ($n + 1) / $n;
    }

    protected function calculatePeriodCorrelation(array $results1, array $results2): float
    {
        // Find common employees
        $common = [];
        
        foreach ($results1 as $result1) {
            foreach ($results2 as $result2) {
                if ($result1['employee_id'] == $result2['employee_id']) {
                    $common[] = [
                        'score1' => $result1['total_score'],
                        'score2' => $result2['total_score']
                    ];
                    break;
                }
            }
        }
        
        if (count($common) < 2) return 0;
        
        $scores1 = array_column($common, 'score1');
        $scores2 = array_column($common, 'score2');
        
        return $this->calculateCorrelation($scores1, $scores2);
    }

    protected function calculateCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n != count($y) || $n < 2) return 0;
        
        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;
        
        $numerator = 0;
        $denomX = 0;
        $denomY = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $diffX = $x[$i] - $meanX;
            $diffY = $y[$i] - $meanY;
            
            $numerator += $diffX * $diffY;
            $denomX += $diffX * $diffX;
            $denomY += $diffY * $diffY;
        }
        
        $denominator = sqrt($denomX * $denomY);
        
        return $denominator != 0 ? $numerator / $denominator : 0;
    }

    protected function performVarianceAnalysis(array $periodScores): array
    {
        $analysis = [];
        
        foreach ($periodScores as $period => $scores) {
            $analysis[$period] = [
                'variance' => $this->calculateVariance($scores),
                'std_dev' => $this->calculateStandardDeviation($scores),
                'coefficient_variation' => $this->calculateCoefficientOfVariation($scores)
            ];
        }
        
        return $analysis;
    }

    protected function getAvailablePeriods(): array
    {
        return EvaluationResult::distinct('evaluation_period')
            ->orderBy('evaluation_period', 'desc')
            ->pluck('evaluation_period')
            ->toArray();
    }

    protected function getRecentPeriods(string $targetPeriod, int $count): array
    {
        $allPeriods = $this->getAvailablePeriods();
        $targetIndex = array_search($targetPeriod, $allPeriods);
        
        if ($targetIndex === false) return [];
        
        return array_slice($allPeriods, $targetIndex + 1, $count);
    }

    protected function performCrossEmployeeAnalysis(array $employeeComparisons): array
    {
        // Analyze patterns across employees
        $trends = [];
        $consistencies = [];
        
        foreach ($employeeComparisons as $employeeId => $comparison) {
            $trends[] = $comparison['trend_analysis']['score_trend'];
            $consistencies[] = $comparison['consistency_score'];
        }
        
        return [
            'average_trend' => array_sum($trends) / count($trends),
            'average_consistency' => array_sum($consistencies) / count($consistencies),
            'trend_distribution' => [
                'improving' => count(array_filter($trends, fn($t) => $t > 0.1)),
                'stable' => count(array_filter($trends, fn($t) => abs($t) <= 0.1)),
                'declining' => count(array_filter($trends, fn($t) => $t < -0.1))
            ]
        ];
    }

    protected function analyzeRelativePerformance(array $employeeComparisons): array
    {
        // Compare employees relative to each other
        $latestScores = [];
        
        foreach ($employeeComparisons as $employeeId => $comparison) {
            $performanceData = $comparison['performance_data'];
            if (!empty($performanceData)) {
                $latestPeriod = array_key_last($performanceData);
                $latestScores[$employeeId] = [
                    'employee_name' => $comparison['employee_info']['name'],
                    'score' => $performanceData[$latestPeriod]['total_score'],
                    'ranking' => $performanceData[$latestPeriod]['ranking']
                ];
            }
        }
        
        // Sort by score
        uasort($latestScores, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return [
            'rankings' => $latestScores,
            'performance_gaps' => $this->calculatePerformanceGaps($latestScores),
            'tier_analysis' => $this->analyzeTiers($latestScores)
        ];
    }

    protected function calculatePerformanceGaps(array $rankings): array
    {
        $scores = array_column($rankings, 'score');
        $gaps = [];
        
        for ($i = 0; $i < count($scores) - 1; $i++) {
            $gaps[] = $scores[$i] - $scores[$i + 1];
        }
        
        return [
            'largest_gap' => max($gaps),
            'smallest_gap' => min($gaps),
            'average_gap' => array_sum($gaps) / count($gaps)
        ];
    }

    protected function analyzeTiers(array $rankings): array
    {
        $count = count($rankings);
        $scores = array_values(array_column($rankings, 'score'));
        
        return [
            'top_tier' => array_slice($scores, 0, ceil($count * 0.2)),
            'middle_tier' => array_slice($scores, ceil($count * 0.2), ceil($count * 0.6)),
            'bottom_tier' => array_slice($scores, ceil($count * 0.8))
        ];
    }

    protected function generateEmployeeRecommendations(array $employeeComparisons): array
    {
        $recommendations = [];
        
        foreach ($employeeComparisons as $employeeId => $comparison) {
            $trend = $comparison['trend_analysis']['score_trend'];
            $consistency = $comparison['consistency_score'];
            $improvementAreas = $comparison['improvement_areas'];
            
            if ($trend < -0.1) {
                $recommendations[] = [
                    'employee_name' => $comparison['employee_info']['name'],
                    'type' => 'performance_decline',
                    'priority' => 'high',
                    'recommendation' => 'Immediate intervention required due to declining performance trend'
                ];
            } elseif ($consistency < 0.6) {
                $recommendations[] = [
                    'employee_name' => $comparison['employee_info']['name'],
                    'type' => 'inconsistent_performance',
                    'priority' => 'medium',
                    'recommendation' => 'Focus on performance consistency through regular coaching'
                ];
            } elseif (!empty($improvementAreas)) {
                $topArea = $improvementAreas[0]['criteria_name'];
                $recommendations[] = [
                    'employee_name' => $comparison['employee_info']['name'],
                    'type' => 'skill_development',
                    'priority' => 'medium',
                    'recommendation' => "Provide targeted training in {$topArea}"
                ];
            }
        }
        
        return $recommendations;
    }

    // Additional helper methods for other comparison types would go here...
    // (Department comparison, criteria impact analysis, etc.)
    // These follow similar patterns to the employee comparison methods
}