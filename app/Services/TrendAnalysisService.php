<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrendAnalysisService
{
    /**
     * Analyze overall organizational performance trends
     */
    public function analyzeOrganizationalTrends(array $periods = []): array
    {
        $startTime = microtime(true);
        
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $trendData = $this->getOrganizationalTrendData($periods);
            
            $analysis = [
                'periods' => $periods,
                'trend_data' => $trendData,
                'overall_trends' => $this->calculateOverallTrends($trendData),
                'performance_cycles' => $this->identifyPerformanceCycles($trendData),
                'seasonal_patterns' => $this->analyzeSeasonalPatterns($trendData),
                'forecasting' => $this->generatePerformanceForecast($trendData),
                'trend_indicators' => $this->calculateTrendIndicators($trendData),
                'anomaly_detection' => $this->detectAnomalies($trendData),
                'insights' => $this->generateTrendInsights($trendData),
                'execution_time' => microtime(true) - $startTime
            ];

            Log::info("Organizational trend analysis completed", [
                'periods_analyzed' => count($periods),
                'execution_time' => $analysis['execution_time']
            ]);

            return $analysis;

        } catch (\Exception $e) {
            Log::error("Organizational trend analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze individual employee performance trends
     */
    public function analyzeEmployeeTrends(int $employeeId, array $periods = []): array
    {
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $employee = Employee::findOrFail($employeeId);
            $trendData = $this->getEmployeeTrendData($employeeId, $periods);
            
            return [
                'employee_info' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_code,
                    'position' => $employee->position,
                    'department' => $employee->department
                ],
                'periods' => $periods,
                'trend_data' => $trendData,
                'performance_trends' => $this->calculateEmployeePerformanceTrends($trendData),
                'criteria_trends' => $this->analyzeEmployeeCriteriaTrends($employeeId, $periods),
                'ranking_trends' => $this->analyzeEmployeeRankingTrends($trendData),
                'trajectory_analysis' => $this->analyzePerformanceTrajectory($trendData),
                'forecasting' => $this->forecastEmployeePerformance($trendData),
                'milestone_analysis' => $this->analyzeMilestones($trendData),
                'recommendations' => $this->generateEmployeeTrendRecommendations($trendData)
            ];

        } catch (\Exception $e) {
            Log::error("Employee trend analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze criteria-specific trends across the organization
     */
    public function analyzeCriteriaTrends(array $periods = []): array
    {
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $criterias = Criteria::all();
            $analysis = [];
            
            foreach ($criterias as $criteria) {
                $trendData = $this->getCriteriaTrendData($criteria->id, $periods);
                
                $analysis[$criteria->id] = [
                    'criteria_info' => [
                        'id' => $criteria->id,
                        'name' => $criteria->name,
                        'weight' => $criteria->weight,
                        'type' => $criteria->type
                    ],
                    'trend_data' => $trendData,
                    'performance_trends' => $this->calculateCriteriaPerformanceTrends($trendData),
                    'distribution_trends' => $this->analyzeCriteriaDistributionTrends($trendData),
                    'effectiveness_trends' => $this->analyzeCriteriaEffectivenessTrends($criteria->id, $periods),
                    'forecasting' => $this->forecastCriteriaPerformance($trendData),
                    'insights' => $this->generateCriteriaTrendInsights($criteria, $trendData)
                ];
            }
            
            return [
                'criteria_analysis' => $analysis,
                'cross_criteria_trends' => $this->analyzeCrossCriteriaTrends($analysis),
                'criteria_correlation_trends' => $this->analyzeCriteriaCorrelationTrends($periods),
                'weight_impact_trends' => $this->analyzeWeightImpactTrends($periods)
            ];

        } catch (\Exception $e) {
            Log::error("Criteria trend analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze departmental performance trends
     */
    public function analyzeDepartmentalTrends(array $periods = []): array
    {
        try {
            if (empty($periods)) {
                $periods = $this->getAvailablePeriods();
            }
            
            $departments = Employee::distinct('department')->pluck('department')->toArray();
            $analysis = [];
            
            foreach ($departments as $department) {
                $trendData = $this->getDepartmentTrendData($department, $periods);
                
                $analysis[$department] = [
                    'department_name' => $department,
                    'employee_count' => Employee::where('department', $department)->count(),
                    'trend_data' => $trendData,
                    'performance_trends' => $this->calculateDepartmentPerformanceTrends($trendData),
                    'consistency_trends' => $this->analyzeDepartmentConsistencyTrends($trendData),
                    'talent_pipeline_trends' => $this->analyzeTalentPipelineTrends($department, $periods),
                    'forecasting' => $this->forecastDepartmentPerformance($trendData),
                    'benchmarking' => $this->benchmarkDepartmentTrends($department, $analysis)
                ];
            }
            
            return [
                'departments' => $analysis,
                'inter_department_trends' => $this->analyzeInterDepartmentTrends($analysis),
                'department_rankings_trends' => $this->analyzeDepartmentRankingsTrends($analysis),
                'resource_allocation_insights' => $this->generateResourceAllocationInsights($analysis)
            ];

        } catch (\Exception $e) {
            Log::error("Departmental trend analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate comprehensive trend dashboard data
     */
    public function generateTrendDashboard(array $options = []): array
    {
        try {
            $periods = $options['periods'] ?? $this->getRecentPeriods(6);
            
            return [
                'summary_metrics' => $this->calculateSummaryTrendMetrics($periods),
                'key_trends' => $this->identifyKeyTrends($periods),
                'performance_indicators' => $this->calculatePerformanceIndicators($periods),
                'alerts' => $this->generateTrendAlerts($periods),
                'quick_insights' => $this->generateQuickInsights($periods),
                'chart_data' => $this->generateChartData($periods),
                'recommendations' => $this->generateDashboardRecommendations($periods)
            ];

        } catch (\Exception $e) {
            Log::error("Trend dashboard generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper methods for data collection
     */
    protected function getOrganizationalTrendData(array $periods): array
    {
        $data = [];
        
        foreach ($periods as $period) {
            $results = EvaluationResult::where('evaluation_period', $period)
                ->with('employee')
                ->get();
            
            if ($results->isNotEmpty()) {
                $scores = $results->pluck('total_score')->toArray();
                
                $data[$period] = [
                    'period' => $period,
                    'employee_count' => $results->count(),
                    'average_score' => array_sum($scores) / count($scores),
                    'median_score' => $this->calculateMedian($scores),
                    'std_deviation' => $this->calculateStandardDeviation($scores),
                    'min_score' => min($scores),
                    'max_score' => max($scores),
                    'score_range' => max($scores) - min($scores),
                    'top_10_percent_avg' => $this->calculateTopPercentileAverage($scores, 10),
                    'bottom_10_percent_avg' => $this->calculateBottomPercentileAverage($scores, 10),
                    'excellence_rate' => count(array_filter($scores, fn($s) => $s >= 0.8)) / count($scores),
                    'improvement_needed_rate' => count(array_filter($scores, fn($s) => $s < 0.6)) / count($scores)
                ];
            }
        }
        
        return $data;
    }

    protected function getEmployeeTrendData(int $employeeId, array $periods): array
    {
        $data = [];
        
        foreach ($periods as $period) {
            $result = EvaluationResult::where('employee_id', $employeeId)
                ->where('evaluation_period', $period)
                ->first();
            
            if ($result) {
                $data[$period] = [
                    'period' => $period,
                    'total_score' => (float) $result->total_score,
                    'ranking' => $result->ranking,
                    'percentile' => $this->calculatePercentile($result->ranking, $period)
                ];
            }
        }
        
        return $data;
    }

    protected function getCriteriaTrendData(int $criteriaId, array $periods): array
    {
        $data = [];
        
        foreach ($periods as $period) {
            $evaluations = Evaluation::where('criteria_id', $criteriaId)
                ->where('evaluation_period', $period)
                ->get();
            
            if ($evaluations->isNotEmpty()) {
                $scores = $evaluations->pluck('score')->toArray();
                
                $data[$period] = [
                    'period' => $period,
                    'evaluation_count' => count($scores),
                    'average_score' => array_sum($scores) / count($scores),
                    'median_score' => $this->calculateMedian($scores),
                    'std_deviation' => $this->calculateStandardDeviation($scores),
                    'min_score' => min($scores),
                    'max_score' => max($scores),
                    'distribution' => $this->calculateScoreDistribution($scores)
                ];
            }
        }
        
        return $data;
    }

    protected function getDepartmentTrendData(string $department, array $periods): array
    {
        $data = [];
        
        foreach ($periods as $period) {
            $results = EvaluationResult::whereHas('employee', function($query) use ($department) {
                $query->where('department', $department);
            })->where('evaluation_period', $period)->get();
            
            if ($results->isNotEmpty()) {
                $scores = $results->pluck('total_score')->toArray();
                $rankings = $results->pluck('ranking')->toArray();
                
                $data[$period] = [
                    'period' => $period,
                    'employee_count' => count($scores),
                    'average_score' => array_sum($scores) / count($scores),
                    'median_score' => $this->calculateMedian($scores),
                    'average_ranking' => array_sum($rankings) / count($rankings),
                    'top_performers' => count(array_filter($rankings, fn($r) => $r <= 5)),
                    'department_score_vs_org' => $this->calculateDepartmentVsOrgScore($department, $period)
                ];
            }
        }
        
        return $data;
    }

    /**
     * Trend calculation methods
     */
    protected function calculateOverallTrends(array $trendData): array
    {
        $periods = array_keys($trendData);
        
        return [
            'average_score_trend' => $this->calculateTrend(array_column($trendData, 'average_score')),
            'median_score_trend' => $this->calculateTrend(array_column($trendData, 'median_score')),
            'variability_trend' => $this->calculateTrend(array_column($trendData, 'std_deviation')),
            'excellence_rate_trend' => $this->calculateTrend(array_column($trendData, 'excellence_rate')),
            'improvement_needed_trend' => $this->calculateTrend(array_column($trendData, 'improvement_needed_rate')),
            'score_range_trend' => $this->calculateTrend(array_column($trendData, 'score_range')),
            'trend_strength' => $this->calculateTrendStrength($trendData),
            'trend_direction' => $this->determineTrendDirection($trendData)
        ];
    }

    protected function calculateEmployeePerformanceTrends(array $trendData): array
    {
        $scores = array_column($trendData, 'total_score');
        $rankings = array_column($trendData, 'ranking');
        
        return [
            'score_trend' => $this->calculateTrend($scores),
            'ranking_trend' => $this->calculateTrend(array_map(fn($x) => -$x, $rankings)), // Invert for positive trend
            'volatility' => $this->calculateVolatility($scores),
            'consistency' => $this->calculateConsistency($scores),
            'momentum' => $this->calculateMomentum($scores),
            'acceleration' => $this->calculateAcceleration($scores),
            'trend_classification' => $this->classifyTrend($scores)
        ];
    }

    protected function calculateCriteriaPerformanceTrends(array $trendData): array
    {
        $averages = array_column($trendData, 'average_score');
        $variability = array_column($trendData, 'std_deviation');
        
        return [
            'average_trend' => $this->calculateTrend($averages),
            'variability_trend' => $this->calculateTrend($variability),
            'improvement_rate' => $this->calculateImprovementRate($averages),
            'stability_score' => $this->calculateStabilityScore($averages),
            'performance_consistency' => $this->calculatePerformanceConsistency($trendData)
        ];
    }

    /**
     * Advanced analysis methods
     */
    protected function identifyPerformanceCycles(array $trendData): array
    {
        $scores = array_column($trendData, 'average_score');
        
        return [
            'cycle_detected' => $this->detectCycles($scores),
            'cycle_length' => $this->calculateCycleLength($scores),
            'peak_periods' => $this->identifyPeaks($trendData),
            'trough_periods' => $this->identifyTroughs($trendData),
            'cycle_amplitude' => $this->calculateCycleAmplitude($scores)
        ];
    }

    protected function analyzeSeasonalPatterns(array $trendData): array
    {
        $periods = array_keys($trendData);
        $scores = array_column($trendData, 'average_score');
        
        $seasonalData = [];
        foreach ($periods as $index => $period) {
            $month = $this->extractMonth($period);
            $quarter = $this->extractQuarter($period);
            
            if (!isset($seasonalData[$quarter])) {
                $seasonalData[$quarter] = [];
            }
            $seasonalData[$quarter][] = $scores[$index];
        }
        
        $seasonalAverages = [];
        foreach ($seasonalData as $quarter => $quarterScores) {
            $seasonalAverages[$quarter] = array_sum($quarterScores) / count($quarterScores);
        }
        
        return [
            'seasonal_averages' => $seasonalAverages,
            'seasonal_variance' => $this->calculateSeasonalVariance($seasonalData),
            'strongest_quarter' => array_keys($seasonalAverages, max($seasonalAverages))[0],
            'weakest_quarter' => array_keys($seasonalAverages, min($seasonalAverages))[0],
            'seasonal_effect_strength' => $this->calculateSeasonalEffectStrength($seasonalAverages)
        ];
    }

    protected function generatePerformanceForecast(array $trendData, int $periodsAhead = 3): array
    {
        $scores = array_column($trendData, 'average_score');
        
        if (count($scores) < 3) {
            return ['error' => 'Insufficient data for forecasting'];
        }
        
        // Simple linear regression forecast
        $forecast = [];
        $trend = $this->calculateTrend($scores);
        $lastScore = end($scores);
        
        for ($i = 1; $i <= $periodsAhead; $i++) {
            $forecastScore = $lastScore + ($trend * $i);
            $forecast[] = [
                'period_ahead' => $i,
                'forecasted_score' => max(0, min(1, $forecastScore)), // Clamp to valid range
                'confidence_interval' => $this->calculateConfidenceInterval($scores, $i)
            ];
        }
        
        return [
            'forecasts' => $forecast,
            'forecast_method' => 'linear_regression',
            'trend_strength' => abs($trend),
            'forecast_reliability' => $this->calculateForecastReliability($scores)
        ];
    }

    protected function detectAnomalies(array $trendData): array
    {
        $scores = array_column($trendData, 'average_score');
        $mean = array_sum($scores) / count($scores);
        $stdDev = $this->calculateStandardDeviation($scores);
        
        $anomalies = [];
        $threshold = 2 * $stdDev; // 2 standard deviations
        
        foreach ($trendData as $period => $data) {
            $deviation = abs($data['average_score'] - $mean);
            if ($deviation > $threshold) {
                $anomalies[] = [
                    'period' => $period,
                    'score' => $data['average_score'],
                    'deviation' => $deviation,
                    'severity' => $deviation > (3 * $stdDev) ? 'high' : 'medium',
                    'type' => $data['average_score'] > $mean ? 'positive' : 'negative'
                ];
            }
        }
        
        return $anomalies;
    }

    protected function calculateTrendIndicators(array $trendData): array
    {
        $scores = array_column($trendData, 'average_score');
        $periods = array_keys($trendData);
        
        return [
            'trend_strength' => $this->calculateTrendStrength($trendData),
            'momentum_index' => $this->calculateMomentumIndex($scores),
            'volatility_index' => $this->calculateVolatilityIndex($scores),
            'performance_velocity' => $this->calculatePerformanceVelocity($scores),
            'trend_persistence' => $this->calculateTrendPersistence($scores),
            'directional_consistency' => $this->calculateDirectionalConsistency($scores)
        ];
    }

    /**
     * Statistical helper methods
     */
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

    protected function calculateMomentum(array $values): float
    {
        if (count($values) < 3) return 0;
        
        $recentTrend = $this->calculateTrend(array_slice($values, -3));
        $overallTrend = $this->calculateTrend($values);
        
        return $recentTrend - $overallTrend;
    }

    protected function calculateAcceleration(array $values): float
    {
        if (count($values) < 3) return 0;
        
        $velocities = [];
        for ($i = 1; $i < count($values); $i++) {
            $velocities[] = $values[$i] - $values[$i-1];
        }
        
        return $this->calculateTrend($velocities);
    }

    protected function classifyTrend(array $values): string
    {
        $trend = $this->calculateTrend($values);
        $volatility = $this->calculateVolatility($values);
        
        if (abs($trend) < 0.01) {
            return $volatility < 0.05 ? 'stable' : 'volatile';
        } elseif ($trend > 0.05) {
            return $volatility < 0.1 ? 'strong_upward' : 'volatile_upward';
        } elseif ($trend < -0.05) {
            return $volatility < 0.1 ? 'strong_downward' : 'volatile_downward';
        } else {
            return $trend > 0 ? 'weak_upward' : 'weak_downward';
        }
    }

    protected function calculateTrendStrength(array $trendData): float
    {
        $scores = array_column($trendData, 'average_score');
        $trend = $this->calculateTrend($scores);
        $rSquared = $this->calculateRSquared($scores);
        
        return abs($trend) * $rSquared;
    }

    protected function calculateRSquared(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        
        $x = range(1, $n);
        $y = $values;
        $yMean = array_sum($y) / $n;
        
        // Calculate predicted values using linear regression
        $slope = $this->calculateTrend($values);
        $intercept = $yMean - $slope * (array_sum($x) / $n);
        
        $ssTotal = 0;
        $ssRes = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $ssTotal += pow($y[$i] - $yMean, 2);
            $ssRes += pow($y[$i] - $predicted, 2);
        }
        
        return $ssTotal > 0 ? 1 - ($ssRes / $ssTotal) : 0;
    }

    protected function determineTrendDirection(array $trendData): string
    {
        $trend = $this->calculateTrend(array_column($trendData, 'average_score'));
        
        if ($trend > 0.02) return 'upward';
        elseif ($trend < -0.02) return 'downward';
        else return 'stable';
    }

    protected function detectCycles(array $values): bool
    {
        // Simple cycle detection using autocorrelation
        if (count($values) < 6) return false;
        
        $autocorrelations = [];
        $maxLag = min(floor(count($values) / 2), 12);
        
        for ($lag = 1; $lag <= $maxLag; $lag++) {
            $autocorrelations[$lag] = $this->calculateAutocorrelation($values, $lag);
        }
        
        return max($autocorrelations) > 0.5;
    }

    protected function calculateAutocorrelation(array $values, int $lag): float
    {
        $n = count($values);
        if ($lag >= $n) return 0;
        
        $mean = array_sum($values) / $n;
        $numerator = 0;
        $denominator = 0;
        
        for ($i = 0; $i < $n - $lag; $i++) {
            $numerator += ($values[$i] - $mean) * ($values[$i + $lag] - $mean);
        }
        
        for ($i = 0; $i < $n; $i++) {
            $denominator += pow($values[$i] - $mean, 2);
        }
        
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    protected function calculateCycleLength(array $values): ?int
    {
        if (!$this->detectCycles($values)) return null;
        
        $maxLag = min(floor(count($values) / 2), 12);
        $maxCorrelation = 0;
        $cycleLength = null;
        
        for ($lag = 2; $lag <= $maxLag; $lag++) {
            $correlation = $this->calculateAutocorrelation($values, $lag);
            if ($correlation > $maxCorrelation) {
                $maxCorrelation = $correlation;
                $cycleLength = $lag;
            }
        }
        
        return $cycleLength;
    }

    protected function identifyPeaks(array $trendData): array
    {
        $periods = array_keys($trendData);
        $scores = array_column($trendData, 'average_score');
        $peaks = [];
        
        for ($i = 1; $i < count($scores) - 1; $i++) {
            if ($scores[$i] > $scores[$i-1] && $scores[$i] > $scores[$i+1]) {
                $peaks[] = [
                    'period' => $periods[$i],
                    'score' => $scores[$i],
                    'prominence' => min($scores[$i] - $scores[$i-1], $scores[$i] - $scores[$i+1])
                ];
            }
        }
        
        return $peaks;
    }

    protected function identifyTroughs(array $trendData): array
    {
        $periods = array_keys($trendData);
        $scores = array_column($trendData, 'average_score');
        $troughs = [];
        
        for ($i = 1; $i < count($scores) - 1; $i++) {
            if ($scores[$i] < $scores[$i-1] && $scores[$i] < $scores[$i+1]) {
                $troughs[] = [
                    'period' => $periods[$i],
                    'score' => $scores[$i],
                    'depth' => min($scores[$i-1] - $scores[$i], $scores[$i+1] - $scores[$i])
                ];
            }
        }
        
        return $troughs;
    }

    protected function calculateTopPercentileAverage(array $scores, int $percentile): float
    {
        sort($scores, SORT_NUMERIC | SORT_DESC);
        $topCount = ceil(count($scores) * ($percentile / 100));
        $topScores = array_slice($scores, 0, $topCount);
        
        return array_sum($topScores) / count($topScores);
    }

    protected function calculateBottomPercentileAverage(array $scores, int $percentile): float
    {
        sort($scores, SORT_NUMERIC);
        $bottomCount = ceil(count($scores) * ($percentile / 100));
        $bottomScores = array_slice($scores, 0, $bottomCount);
        
        return array_sum($bottomScores) / count($bottomScores);
    }

    protected function getAvailablePeriods(): array
    {
        return EvaluationResult::distinct('evaluation_period')
            ->orderBy('evaluation_period', 'desc')
            ->pluck('evaluation_period')
            ->toArray();
    }

    protected function getRecentPeriods(int $count): array
    {
        return EvaluationResult::distinct('evaluation_period')
            ->orderBy('evaluation_period', 'desc')
            ->limit($count)
            ->pluck('evaluation_period')
            ->toArray();
    }

    protected function extractMonth(string $period): int
    {
        // Assuming period format is YYYY-MM
        return (int) substr($period, -2);
    }

    protected function extractQuarter(string $period): int
    {
        $month = $this->extractMonth($period);
        return ceil($month / 3);
    }

    protected function calculateSeasonalVariance(array $seasonalData): float
    {
        $allAverages = [];
        foreach ($seasonalData as $quarterScores) {
            $allAverages[] = array_sum($quarterScores) / count($quarterScores);
        }
        
        return $this->calculateStandardDeviation($allAverages);
    }

    protected function calculateSeasonalEffectStrength(array $seasonalAverages): float
    {
        $max = max($seasonalAverages);
        $min = min($seasonalAverages);
        $mean = array_sum($seasonalAverages) / count($seasonalAverages);
        
        return $mean > 0 ? ($max - $min) / $mean : 0;
    }

    protected function calculateConfidenceInterval(array $scores, int $periodsAhead): array
    {
        $stdDev = $this->calculateStandardDeviation($scores);
        $errorMargin = $stdDev * sqrt($periodsAhead) * 1.96; // 95% confidence
        
        return [
            'lower_bound' => -$errorMargin,
            'upper_bound' => $errorMargin,
            'confidence_level' => 0.95
        ];
    }

    protected function calculateForecastReliability(array $scores): float
    {
        $rSquared = $this->calculateRSquared($scores);
        $consistency = $this->calculateConsistency($scores);
        $dataPoints = count($scores);
        
        // Reliability based on R-squared, consistency, and data availability
        $dataReliability = min(1.0, $dataPoints / 12); // 12 periods for full reliability
        
        return ($rSquared * 0.5 + $consistency * 0.3 + $dataReliability * 0.2);
    }

    protected function generateTrendInsights(array $trendData): array
    {
        $insights = [];
        $trends = $this->calculateOverallTrends($trendData);
        
        // Performance trend insight
        if ($trends['average_score_trend'] > 0.02) {
            $insights[] = [
                'type' => 'positive_trend',
                'title' => 'Consistent Performance Improvement',
                'description' => 'Organization shows steady improvement in overall performance scores.',
                'confidence' => $trends['trend_strength'] > 0.5 ? 'high' : 'medium'
            ];
        } elseif ($trends['average_score_trend'] < -0.02) {
            $insights[] = [
                'type' => 'negative_trend',
                'title' => 'Performance Decline Alert',
                'description' => 'Declining trend in organizational performance requires attention.',
                'confidence' => $trends['trend_strength'] > 0.5 ? 'high' : 'medium'
            ];
        }
        
        // Variability insight
        if ($trends['variability_trend'] > 0.01) {
            $insights[] = [
                'type' => 'increasing_variability',
                'title' => 'Growing Performance Inconsistency',
                'description' => 'Performance variability is increasing, indicating potential management challenges.',
                'confidence' => 'medium'
            ];
        }
        
        return $insights;
    }

    // Additional helper methods would go here for the remaining functionality
    // (Employee-specific trends, criteria trends, departmental analysis, etc.)
    
    protected function calculatePercentile(int $ranking, string $period): float
    {
        $totalEmployees = EvaluationResult::where('evaluation_period', $period)->count();
        
        return $totalEmployees > 0 ? (($totalEmployees - $ranking + 1) / $totalEmployees) * 100 : 0;
    }

    protected function calculateScoreDistribution(array $scores): array
    {
        $ranges = [
            '90-100' => 0, '80-89' => 0, '70-79' => 0, 
            '60-69' => 0, '50-59' => 0, 'below-50' => 0
        ];
        
        foreach ($scores as $score) {
            if ($score >= 90) $ranges['90-100']++;
            elseif ($score >= 80) $ranges['80-89']++;
            elseif ($score >= 70) $ranges['70-79']++;
            elseif ($score >= 60) $ranges['60-69']++;
            elseif ($score >= 50) $ranges['50-59']++;
            else $ranges['below-50']++;
        }
        
        return $ranges;
    }

    protected function calculateDepartmentVsOrgScore(string $department, string $period): float
    {
        $deptAvg = EvaluationResult::whereHas('employee', function($query) use ($department) {
            $query->where('department', $department);
        })->where('evaluation_period', $period)->avg('total_score');
        
        $orgAvg = EvaluationResult::where('evaluation_period', $period)->avg('total_score');
        
        return $orgAvg > 0 ? ($deptAvg - $orgAvg) / $orgAvg : 0;
    }

    protected function calculateImprovementRate(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $first = reset($values);
        $last = end($values);
        
        return $first > 0 ? ($last - $first) / $first : 0;
    }

    protected function calculateStabilityScore(array $values): float
    {
        $volatility = $this->calculateVolatility($values);
        $mean = array_sum($values) / count($values);
        
        return $mean > 0 ? max(0, 1 - ($volatility / $mean)) : 0;
    }

    protected function calculatePerformanceConsistency(array $trendData): float
    {
        $scores = array_column($trendData, 'average_score');
        return $this->calculateConsistency($scores);
    }

    protected function calculateMomentumIndex(array $values): float
    {
        if (count($values) < 4) return 0;
        
        $recentPeriods = array_slice($values, -3);
        $earlierPeriods = array_slice($values, 0, -3);
        
        $recentAvg = array_sum($recentPeriods) / count($recentPeriods);
        $earlierAvg = array_sum($earlierPeriods) / count($earlierPeriods);
        
        return $earlierAvg > 0 ? ($recentAvg - $earlierAvg) / $earlierAvg : 0;
    }

    protected function calculateVolatilityIndex(array $values): float
    {
        $volatility = $this->calculateVolatility($values);
        $mean = array_sum($values) / count($values);
        
        return $mean > 0 ? $volatility / $mean : 0;
    }

    protected function calculatePerformanceVelocity(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $changes = [];
        for ($i = 1; $i < count($values); $i++) {
            $changes[] = $values[$i] - $values[$i-1];
        }
        
        return array_sum($changes) / count($changes);
    }

    protected function calculateTrendPersistence(array $values): float
    {
        if (count($values) < 3) return 0;
        
        $directions = [];
        for ($i = 1; $i < count($values); $i++) {
            $directions[] = $values[$i] > $values[$i-1] ? 1 : -1;
        }
        
        $consistentDirections = 0;
        for ($i = 1; $i < count($directions); $i++) {
            if ($directions[$i] == $directions[$i-1]) {
                $consistentDirections++;
            }
        }
        
        return count($directions) > 1 ? $consistentDirections / (count($directions) - 1) : 0;
    }

    protected function calculateDirectionalConsistency(array $values): float
    {
        if (count($values) < 2) return 1.0;
        
        $overallTrend = $this->calculateTrend($values);
        $consistentChanges = 0;
        $totalChanges = 0;
        
        for ($i = 1; $i < count($values); $i++) {
            $change = $values[$i] - $values[$i-1];
            $totalChanges++;
            
            if (($overallTrend > 0 && $change > 0) || ($overallTrend < 0 && $change < 0)) {
                $consistentChanges++;
            }
        }
        
        return $totalChanges > 0 ? $consistentChanges / $totalChanges : 1.0;
    }
}