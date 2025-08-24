<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Models\Criteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdvancedStatisticsService
{
    /**
     * Get comprehensive statistical overview
     */
    public function getStatisticalOverview(string $evaluationPeriod = null): array
    {
        $cacheKey = "statistical_overview_{$evaluationPeriod}";

        return Cache::remember($cacheKey, 3600, function () use ($evaluationPeriod) {
            return [
                'employee_stats' => $this->getEmployeeStatistics($evaluationPeriod),
                'criteria_stats' => $this->getCriteriaStatistics($evaluationPeriod),
                'performance_distribution' => $this->getPerformanceDistribution($evaluationPeriod),
                'trend_analysis' => $this->getTrendAnalysis($evaluationPeriod),
                'correlation_analysis' => $this->getCorrelationAnalysis($evaluationPeriod),
                'outlier_detection' => $this->detectOutliers($evaluationPeriod),
                'confidence_intervals' => $this->calculateConfidenceIntervals($evaluationPeriod),
                'reliability_metrics' => $this->calculateReliabilityMetrics($evaluationPeriod)
            ];
        });
    }

    /**
     * Get employee performance statistics
     */
    public function getEmployeeStatistics(?string $evaluationPeriod = null): array
    {
        $query = EvaluationResult::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $results = $query->selectRaw('
            employee_id,
            AVG(total_score) as avg_score,
            STDDEV(total_score) as std_dev,
            MIN(total_score) as min_score,
            MAX(total_score) as max_score,
            COUNT(*) as evaluation_count
        ')
        ->groupBy('employee_id')
        ->get();

        $employeeStats = [];
        foreach ($results as $result) {
            $employee = Employee::find($result->employee_id);
            if ($employee) {
                $employeeStats[] = [
                    'employee_id' => $result->employee_id,
                    'employee_name' => $employee->name,
                    'avg_score' => round($result->avg_score, 4),
                    'std_dev' => round($result->std_dev ?? 0, 4),
                    'min_score' => round($result->min_score, 4),
                    'max_score' => round($result->max_score, 4),
                    'evaluation_count' => $result->evaluation_count,
                    'coefficient_variation' => $result->std_dev && $result->avg_score ?
                        round(($result->std_dev / $result->avg_score) * 100, 2) : 0
                ];
            }
        }

        // Sort by average score descending
        usort($employeeStats, fn($a, $b) => $b['avg_score'] <=> $a['avg_score']);

        return [
            'individual_stats' => $employeeStats,
            'summary' => [
                'total_employees' => count($employeeStats),
                'avg_score_all' => count($employeeStats) > 0 ?
                    round(array_sum(array_column($employeeStats, 'avg_score')) / count($employeeStats), 4) : 0,
                'highest_avg_score' => count($employeeStats) > 0 ? $employeeStats[0]['avg_score'] : 0,
                'lowest_avg_score' => count($employeeStats) > 0 ? end($employeeStats)['avg_score'] : 0
            ]
        ];
    }

    /**
     * Get criteria performance statistics
     */
    public function getCriteriaStatistics(?string $evaluationPeriod = null): array
    {
        $query = Evaluation::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $criteriaStats = Criteria::with(['evaluations' => function($q) use ($evaluationPeriod) {
            if ($evaluationPeriod) {
                $q->where('evaluation_period', $evaluationPeriod);
            }
        }])->get()->map(function($criteria) {
            $scores = $criteria->evaluations->pluck('score')->filter()->toArray();

            if (empty($scores)) {
                return [
                    'criteria_id' => $criteria->id,
                    'criteria_name' => $criteria->name,
                    'avg_score' => 0,
                    'std_dev' => 0,
                    'min_score' => 0,
                    'max_score' => 0,
                    'total_evaluations' => 0,
                    'score_distribution' => []
                ];
            }

            $avgScore = array_sum($scores) / count($scores);
            $variance = array_sum(array_map(fn($score) => pow($score - $avgScore, 2), $scores)) / count($scores);
            $stdDev = sqrt($variance);

            // Score distribution (1-5 scale)
            $distribution = array_fill(1, 5, 0);
            foreach ($scores as $score) {
                if ($score >= 1 && $score <= 5) {
                    $distribution[round($score)]++;
                }
            }

            return [
                'criteria_id' => $criteria->id,
                'criteria_name' => $criteria->name,
                'avg_score' => round($avgScore, 4),
                'std_dev' => round($stdDev, 4),
                'min_score' => min($scores),
                'max_score' => max($scores),
                'total_evaluations' => count($scores),
                'score_distribution' => $distribution,
                'coefficient_variation' => $avgScore > 0 ? round(($stdDev / $avgScore) * 100, 2) : 0
            ];
        });

        return [
            'criteria_stats' => $criteriaStats->toArray(),
            'summary' => [
                'total_criteria' => $criteriaStats->count(),
                'avg_score_all_criteria' => $criteriaStats->avg('avg_score'),
                'most_consistent_criteria' => $criteriaStats->sortBy('std_dev')->first(),
                'most_variable_criteria' => $criteriaStats->sortByDesc('std_dev')->first()
            ]
        ];
    }

    /**
     * Get performance distribution analysis
     */
    public function getPerformanceDistribution(?string $evaluationPeriod = null): array
    {
        $query = EvaluationResult::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $scores = $query->pluck('total_score')->toArray();

        if (empty($scores)) {
            return [
                'distribution' => [],
                'percentiles' => [],
                'summary' => []
            ];
        }

        sort($scores);
        $count = count($scores);

        // Calculate percentiles
        $percentiles = [
            'p10' => $this->calculatePercentile($scores, 10),
            'p25' => $this->calculatePercentile($scores, 25),
            'p50' => $this->calculatePercentile($scores, 50), // median
            'p75' => $this->calculatePercentile($scores, 75),
            'p90' => $this->calculatePercentile($scores, 90)
        ];

        // Score ranges distribution
        $ranges = [
            'excellent' => ['min' => 4.5, 'max' => 5.0, 'count' => 0, 'percentage' => 0],
            'very_good' => ['min' => 4.0, 'max' => 4.49, 'count' => 0, 'percentage' => 0],
            'good' => ['min' => 3.5, 'max' => 3.99, 'count' => 0, 'percentage' => 0],
            'fair' => ['min' => 3.0, 'max' => 3.49, 'count' => 0, 'percentage' => 0],
            'poor' => ['min' => 0, 'max' => 2.99, 'count' => 0, 'percentage' => 0]
        ];

        foreach ($scores as $score) {
            foreach ($ranges as $key => &$range) {
                if ($score >= $range['min'] && $score <= $range['max']) {
                    $range['count']++;
                    break;
                }
            }
        }

        // Calculate percentages
        foreach ($ranges as &$range) {
            $range['percentage'] = round(($range['count'] / $count) * 100, 2);
        }

        return [
            'distribution' => $ranges,
            'percentiles' => $percentiles,
            'summary' => [
                'total_scores' => $count,
                'mean' => round(array_sum($scores) / $count, 4),
                'median' => $percentiles['p50'],
                'mode' => $this->calculateMode($scores),
                'range' => round(max($scores) - min($scores), 4),
                'iqr' => round($percentiles['p75'] - $percentiles['p25'], 4) // Interquartile Range
            ]
        ];
    }

    /**
     * Get trend analysis across periods
     */
    public function getTrendAnalysis(?string $evaluationPeriod = null): array
    {
        $periods = EvaluationResult::distinct('evaluation_period')
            ->orderBy('evaluation_period')
            ->pluck('evaluation_period')
            ->toArray();

        if (count($periods) < 2) {
            return [
                'trends' => [],
                'summary' => 'Insufficient data for trend analysis'
            ];
        }

        $trends = [];
        foreach ($periods as $period) {
            $periodStats = $this->getEmployeeStatistics($period);
            $trends[] = [
                'period' => $period,
                'avg_score' => $periodStats['summary']['avg_score_all'],
                'total_employees' => $periodStats['summary']['total_employees']
            ];
        }

        // Calculate trend direction
        $trendDirection = 'stable';
        if (count($trends) >= 2) {
            $firstAvg = $trends[0]['avg_score'];
            $lastAvg = end($trends)['avg_score'];
            $change = $lastAvg - $firstAvg;

            if (abs($change) < 0.01) {
                $trendDirection = 'stable';
            } elseif ($change > 0) {
                $trendDirection = 'improving';
            } else {
                $trendDirection = 'declining';
            }
        }

        return [
            'trends' => $trends,
            'summary' => [
                'total_periods' => count($periods),
                'trend_direction' => $trendDirection,
                'overall_change' => count($trends) >= 2 ?
                    round(end($trends)['avg_score'] - $trends[0]['avg_score'], 4) : 0
            ]
        ];
    }

    /**
     * Get correlation analysis between criteria
     */
    public function getCorrelationAnalysis(?string $evaluationPeriod = null): array
    {
        $query = Evaluation::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $evaluations = $query->with(['criteria', 'employee'])
            ->get()
            ->groupBy('employee_id');

        $criteriaIds = Criteria::pluck('id')->toArray();
        $correlationMatrix = [];

        // Initialize correlation matrix
        foreach ($criteriaIds as $i) {
            foreach ($criteriaIds as $j) {
                $correlationMatrix[$i][$j] = 0;
            }
        }

        // Calculate correlations
        foreach ($criteriaIds as $criteria1) {
            foreach ($criteriaIds as $criteria2) {
                if ($criteria1 === $criteria2) {
                    $correlationMatrix[$criteria1][$criteria2] = 1.0;
                    continue;
                }

                $scores1 = [];
                $scores2 = [];

                foreach ($evaluations as $employeeEvaluations) {
                    $score1 = $employeeEvaluations->where('criteria_id', $criteria1)->first()?->score;
                    $score2 = $employeeEvaluations->where('criteria_id', $criteria2)->first()?->score;

                    if ($score1 !== null && $score2 !== null) {
                        $scores1[] = $score1;
                        $scores2[] = $score2;
                    }
                }

                if (count($scores1) > 1) {
                    $correlationMatrix[$criteria1][$criteria2] = round(
                        $this->calculatePearsonCorrelation($scores1, $scores2), 4
                    );
                }
            }
        }

        return [
            'correlation_matrix' => $correlationMatrix,
            'criteria_names' => Criteria::pluck('name', 'id')->toArray(),
            'summary' => [
                'strong_correlations' => $this->findStrongCorrelations($correlationMatrix, $criteriaIds),
                'weak_correlations' => $this->findWeakCorrelations($correlationMatrix, $criteriaIds)
            ]
        ];
    }

    /**
     * Detect statistical outliers
     */
    public function detectOutliers(?string $evaluationPeriod = null): array
    {
        $query = EvaluationResult::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $scores = $query->pluck('total_score')->toArray();

        if (count($scores) < 4) {
            return [
                'outliers' => [],
                'summary' => 'Insufficient data for outlier detection'
            ];
        }

        sort($scores);
        $q1 = $this->calculatePercentile($scores, 25);
        $q3 = $this->calculatePercentile($scores, 75);
        $iqr = $q3 - $q1;

        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        $outliers = [];
        foreach ($scores as $index => $score) {
            if ($score < $lowerBound || $score > $upperBound) {
                $outliers[] = [
                    'value' => $score,
                    'index' => $index,
                    'type' => $score < $lowerBound ? 'lower' : 'upper',
                    'deviation' => $score < $lowerBound ?
                        round(($lowerBound - $score) / $iqr, 2) :
                        round(($score - $upperBound) / $iqr, 2)
                ];
            }
        }

        return [
            'outliers' => $outliers,
            'bounds' => [
                'lower_bound' => round($lowerBound, 4),
                'upper_bound' => round($upperBound, 4),
                'q1' => round($q1, 4),
                'q3' => round($q3, 4),
                'iqr' => round($iqr, 4)
            ],
            'summary' => [
                'total_outliers' => count($outliers),
                'outlier_percentage' => round((count($outliers) / count($scores)) * 100, 2)
            ]
        ];
    }

    /**
     * Calculate confidence intervals
     */
    public function calculateConfidenceIntervals(?string $evaluationPeriod = null, float $confidenceLevel = 0.95): array
    {
        $query = EvaluationResult::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $scores = $query->pluck('total_score')->toArray();

        if (empty($scores)) {
            return [
                'confidence_intervals' => [],
                'summary' => 'No data available'
            ];
        }

        $n = count($scores);
        $mean = array_sum($scores) / $n;
        $variance = array_sum(array_map(fn($score) => pow($score - $mean, 2), $scores)) / ($n - 1);
        $stdError = sqrt($variance / $n);

        // Z-scores for common confidence levels
        $zScores = [
            0.90 => 1.645,
            0.95 => 1.96,
            0.99 => 2.576
        ];

        $intervals = [];
        foreach ($zScores as $level => $zScore) {
            $margin = $zScore * $stdError;
            $intervals[$level] = [
                'lower' => round($mean - $margin, 4),
                'upper' => round($mean + $margin, 4),
                'margin' => round($margin, 4)
            ];
        }

        return [
            'confidence_intervals' => $intervals,
            'sample_stats' => [
                'sample_size' => $n,
                'sample_mean' => round($mean, 4),
                'sample_std_error' => round($stdError, 4)
            ],
            'summary' => [
                'primary_interval' => $intervals[$confidenceLevel] ?? $intervals[0.95],
                'confidence_level' => $confidenceLevel
            ]
        ];
    }

    /**
     * Calculate reliability metrics
     */
    public function calculateReliabilityMetrics(?string $evaluationPeriod = null): array
    {
        $query = Evaluation::query();

        if ($evaluationPeriod) {
            $query->where('evaluation_period', $evaluationPeriod);
        }

        $evaluations = $query->with(['criteria', 'employee'])
            ->get()
            ->groupBy('employee_id');

        if ($evaluations->isEmpty()) {
            return [
                'reliability_metrics' => [],
                'summary' => 'No data available'
            ];
        }

        $criteriaIds = Criteria::pluck('id')->toArray();
        $reliabilityScores = [];

        foreach ($criteriaIds as $criteriaId) {
            $scores = [];
            foreach ($evaluations as $employeeEvaluations) {
                $score = $employeeEvaluations->where('criteria_id', $criteriaId)->first()?->score;
                if ($score !== null) {
                    $scores[] = $score;
                }
            }

            if (count($scores) > 1) {
                $reliabilityScores[$criteriaId] = [
                    'criteria_name' => Criteria::find($criteriaId)->name,
                    'sample_size' => count($scores),
                    'std_dev' => round($this->calculateStandardDeviation($scores), 4),
                    'coefficient_variation' => round(($this->calculateStandardDeviation($scores) / (array_sum($scores) / count($scores))) * 100, 2)
                ];
            }
        }

        return [
            'reliability_metrics' => $reliabilityScores,
            'summary' => [
                'total_criteria_analyzed' => count($reliabilityScores),
                'most_reliable_criteria' => collect($reliabilityScores)->sortBy('std_dev')->first(),
                'least_reliable_criteria' => collect($reliabilityScores)->sortByDesc('std_dev')->first()
            ]
        ];
    }

    /**
     * Helper methods
     */
    private function calculatePercentile(array $array, float $percentile): float
    {
        $index = ($percentile / 100) * (count($array) - 1);
        $floor = floor($index);
        $ceil = ceil($index);

        if ($floor == $ceil) {
            return $array[$index];
        }

        $d = $index - $floor;
        return $array[$floor] * (1 - $d) + $array[$ceil] * $d;
    }

    private function calculateMode(array $array): ?float
    {
        $values = array_count_values($array);
        $mode = array_search(max($values), $values);
        return $mode !== false ? (float)$mode : null;
    }

    private function calculatePearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n !== count($y) || $n === 0) {
            return 0;
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt((($n * $sumX2) - ($sumX * $sumX)) * (($n * $sumY2) - ($sumY * $sumY)));

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }

    private function calculateStandardDeviation(array $array): float
    {
        $n = count($array);
        if ($n === 0) return 0;

        $mean = array_sum($array) / $n;
        $variance = array_sum(array_map(fn($value) => pow($value - $mean, 2), $array)) / ($n - 1);
        return sqrt($variance);
    }

    private function findStrongCorrelations(array $matrix, array $criteriaIds): array
    {
        $strong = [];
        foreach ($criteriaIds as $i) {
            foreach ($criteriaIds as $j) {
                if ($i !== $j && abs($matrix[$i][$j]) >= 0.7) {
                    $strong[] = [
                        'criteria1' => $i,
                        'criteria2' => $j,
                        'correlation' => $matrix[$i][$j]
                    ];
                }
            }
        }
        return $strong;
    }

    private function findWeakCorrelations(array $matrix, array $criteriaIds): array
    {
        $weak = [];
        foreach ($criteriaIds as $i) {
            foreach ($criteriaIds as $j) {
                if ($i !== $j && abs($matrix[$i][$j]) <= 0.3) {
                    $weak[] = [
                        'criteria1' => $i,
                        'criteria2' => $j,
                        'correlation' => $matrix[$i][$j]
                    ];
                }
            }
        }
        return $weak;
    }
}
