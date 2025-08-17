<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SensitivityAnalysisService
{
    protected $sawService;

    public function __construct(SAWCalculationService $sawService)
    {
        $this->sawService = $sawService;
    }

    /**
     * Perform comprehensive sensitivity analysis for given period
     */
    public function performSensitivityAnalysis(string $evaluationPeriod, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Get base data
            $baseResults = $this->getBaseResults($evaluationPeriod);
            $criterias = Criteria::all();
            
            if ($criterias->isEmpty() || empty($baseResults)) {
                throw new \Exception("Insufficient data for sensitivity analysis");
            }

            // Perform different types of sensitivity analysis
            $analysis = [
                'base_results' => $baseResults,
                'weight_sensitivity' => $this->analyzeWeightSensitivity($evaluationPeriod, $criterias),
                'criteria_impact' => $this->analyzeCriteriaImpact($evaluationPeriod, $criterias),
                'ranking_stability' => $this->analyzeRankingStability($evaluationPeriod, $criterias),
                'threshold_analysis' => $this->analyzeThresholds($evaluationPeriod, $criterias),
                'statistical_summary' => $this->generateStatisticalSummary($baseResults),
                'execution_time' => microtime(true) - $startTime
            ];

            Log::info("Sensitivity analysis completed", [
                'period' => $evaluationPeriod,
                'execution_time' => $analysis['execution_time']
            ]);

            return $analysis;

        } catch (\Exception $e) {
            Log::error("Sensitivity analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze weight sensitivity by varying each criteria weight
     */
    protected function analyzeWeightSensitivity(string $evaluationPeriod, $criterias): array
    {
        $results = [];
        $baseWeights = $criterias->pluck('weight', 'id')->toArray();
        
        foreach ($criterias as $criteria) {
            $sensitivityData = [];
            
            // Test weight variations (-20% to +20% in 5% steps)
            for ($variation = -20; $variation <= 20; $variation += 5) {
                if ($variation == 0) continue; // Skip base case
                
                $newWeights = $this->adjustWeights($baseWeights, $criteria->id, $variation);
                
                if ($newWeights) {
                    $modifiedResults = $this->calculateWithModifiedWeights(
                        $evaluationPeriod, 
                        $newWeights
                    );
                    
                    $sensitivityData[] = [
                        'variation_percent' => $variation,
                        'new_weight' => $newWeights[$criteria->id],
                        'results' => $modifiedResults,
                        'ranking_changes' => $this->compareRankings(
                            $this->getBaseResults($evaluationPeriod),
                            $modifiedResults
                        )
                    ];
                }
            }
            
            $results[$criteria->id] = [
                'criteria_name' => $criteria->name,
                'base_weight' => $criteria->weight,
                'sensitivity_data' => $sensitivityData,
                'sensitivity_score' => $this->calculateSensitivityScore($sensitivityData)
            ];
        }
        
        return $results;
    }

    /**
     * Analyze individual criteria impact on overall rankings
     */
    protected function analyzeCriteriaImpact(string $evaluationPeriod, $criterias): array
    {
        $results = [];
        $baseResults = $this->getBaseResults($evaluationPeriod);
        
        foreach ($criterias as $criteria) {
            // Calculate results without this criteria
            $modifiedWeights = $criterias->where('id', '!=', $criteria->id)
                ->pluck('weight', 'id')
                ->toArray();
            
            // Redistribute weights proportionally
            $totalRemainingWeight = array_sum($modifiedWeights);
            if ($totalRemainingWeight > 0) {
                foreach ($modifiedWeights as $id => $weight) {
                    $modifiedWeights[$id] = round(($weight / $totalRemainingWeight) * 100);
                }
                
                $resultsWithoutCriteria = $this->calculateWithModifiedWeights(
                    $evaluationPeriod,
                    $modifiedWeights,
                    [$criteria->id] // Exclude this criteria
                );
                
                $results[$criteria->id] = [
                    'criteria_name' => $criteria->name,
                    'weight' => $criteria->weight,
                    'impact_score' => $this->calculateImpactScore($baseResults, $resultsWithoutCriteria),
                    'ranking_changes' => $this->compareRankings($baseResults, $resultsWithoutCriteria),
                    'affected_employees' => $this->getAffectedEmployees($baseResults, $resultsWithoutCriteria)
                ];
            }
        }
        
        // Sort by impact score (highest impact first)
        uasort($results, function($a, $b) {
            return $b['impact_score'] <=> $a['impact_score'];
        });
        
        return $results;
    }

    /**
     * Analyze ranking stability across different weight scenarios
     */
    protected function analyzeRankingStability(string $evaluationPeriod, $criterias): array
    {
        $baseResults = $this->getBaseResults($evaluationPeriod);
        $stabilityData = [];
        
        // Generate multiple random weight scenarios
        $scenarios = $this->generateWeightScenarios($criterias, 50);
        
        foreach ($scenarios as $index => $weights) {
            $scenarioResults = $this->calculateWithModifiedWeights($evaluationPeriod, $weights);
            $rankingChanges = $this->compareRankings($baseResults, $scenarioResults);
            
            $stabilityData[] = [
                'scenario' => $index + 1,
                'weights' => $weights,
                'ranking_changes' => $rankingChanges,
                'stability_score' => $this->calculateStabilityScore($rankingChanges)
            ];
        }
        
        // Calculate overall stability metrics
        $stabilityScores = array_column($stabilityData, 'stability_score');
        
        return [
            'scenarios' => $stabilityData,
            'overall_stability' => [
                'average_stability' => array_sum($stabilityScores) / count($stabilityScores),
                'min_stability' => min($stabilityScores),
                'max_stability' => max($stabilityScores),
                'stability_variance' => $this->calculateVariance($stabilityScores)
            ],
            'most_stable_employees' => $this->identifyMostStableEmployees($stabilityData, $baseResults),
            'least_stable_employees' => $this->identifyLeastStableEmployees($stabilityData, $baseResults)
        ];
    }

    /**
     * Analyze threshold values for decision making
     */
    protected function analyzeThresholds(string $evaluationPeriod, $criterias): array
    {
        $baseResults = $this->getBaseResults($evaluationPeriod);
        
        if (count($baseResults) < 2) {
            return ['error' => 'Insufficient data for threshold analysis'];
        }
        
        // Sort by score
        usort($baseResults, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        
        $scores = array_column($baseResults, 'total_score');
        
        return [
            'score_distribution' => [
                'min' => min($scores),
                'max' => max($scores),
                'mean' => array_sum($scores) / count($scores),
                'median' => $this->calculateMedian($scores),
                'std_deviation' => $this->calculateStandardDeviation($scores)
            ],
            'performance_tiers' => $this->definePerformanceTiers($baseResults),
            'decision_thresholds' => $this->calculateDecisionThresholds($scores),
            'gap_analysis' => $this->analyzePerformanceGaps($baseResults)
        ];
    }

    /**
     * Generate statistical summary of base results
     */
    protected function generateStatisticalSummary(array $baseResults): array
    {
        if (empty($baseResults)) {
            return ['error' => 'No data available'];
        }
        
        $scores = array_column($baseResults, 'total_score');
        $rankings = array_column($baseResults, 'ranking');
        
        return [
            'total_employees' => count($baseResults),
            'score_statistics' => [
                'min' => min($scores),
                'max' => max($scores),
                'mean' => array_sum($scores) / count($scores),
                'median' => $this->calculateMedian($scores),
                'std_deviation' => $this->calculateStandardDeviation($scores),
                'coefficient_variation' => $this->calculateCoefficientOfVariation($scores)
            ],
            'performance_distribution' => $this->analyzePerformanceDistribution($baseResults),
            'top_performers' => array_slice($baseResults, 0, min(5, count($baseResults))),
            'bottom_performers' => array_slice(array_reverse($baseResults), 0, min(5, count($baseResults)))
        ];
    }

    /**
     * Helper methods
     */
    protected function getBaseResults(string $evaluationPeriod): array
    {
        return EvaluationResult::with('employee')
            ->where('evaluation_period', $evaluationPeriod)
            ->orderBy('ranking')
            ->get()
            ->map(function($result) {
                return [
                    'employee_id' => $result->employee_id,
                    'employee_name' => $result->employee->name,
                    'total_score' => (float) $result->total_score,
                    'ranking' => $result->ranking
                ];
            })
            ->toArray();
    }

    protected function adjustWeights(array $baseWeights, int $criteriaId, float $variationPercent): ?array
    {
        $newWeights = $baseWeights;
        $originalWeight = $baseWeights[$criteriaId];
        $adjustment = ($originalWeight * $variationPercent) / 100;
        
        $newWeight = $originalWeight + $adjustment;
        
        // Ensure weight stays within valid range
        if ($newWeight <= 0 || $newWeight >= 100) {
            return null;
        }
        
        $newWeights[$criteriaId] = round($newWeight);
        
        // Redistribute the difference among other criteria
        $difference = $newWeights[$criteriaId] - $originalWeight;
        $otherCriteriaCount = count($newWeights) - 1;
        
        if ($otherCriteriaCount > 0) {
            $redistributionPerCriteria = -$difference / $otherCriteriaCount;
            
            foreach ($newWeights as $id => $weight) {
                if ($id != $criteriaId) {
                    $newWeights[$id] = max(1, round($weight + $redistributionPerCriteria));
                }
            }
        }
        
        // Ensure total equals 100
        $total = array_sum($newWeights);
        if ($total != 100) {
            $adjustment = 100 - $total;
            $newWeights[$criteriaId] += $adjustment;
        }
        
        return $newWeights;
    }

    protected function calculateWithModifiedWeights(string $evaluationPeriod, array $weights, array $excludeCriteria = []): array
    {
        // This would use a modified version of SAW calculation with custom weights
        // For now, we'll simulate the calculation
        $employees = Employee::all();
        $criterias = Criteria::whereNotIn('id', $excludeCriteria)->get();
        $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)
            ->whereNotIn('criteria_id', $excludeCriteria)
            ->get();
        
        $results = [];
        
        foreach ($employees as $employee) {
            $totalScore = 0;
            
            foreach ($criterias as $criteria) {
                $evaluation = $evaluations->where('employee_id', $employee->id)
                    ->where('criteria_id', $criteria->id)
                    ->first();
                
                if ($evaluation) {
                    $weight = $weights[$criteria->id] ?? $criteria->weight;
                    $normalizedScore = $evaluation->score / 100; // Simplified normalization
                    $totalScore += $normalizedScore * ($weight / 100);
                }
            }
            
            $results[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'total_score' => $totalScore,
                'ranking' => 0 // Will be set after sorting
            ];
        }
        
        // Sort and assign rankings
        usort($results, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        
        foreach ($results as $index => &$result) {
            $result['ranking'] = $index + 1;
        }
        
        return $results;
    }

    protected function compareRankings(array $baseResults, array $modifiedResults): array
    {
        $changes = [];
        
        foreach ($baseResults as $baseResult) {
            $modifiedResult = collect($modifiedResults)
                ->where('employee_id', $baseResult['employee_id'])
                ->first();
            
            if ($modifiedResult) {
                $rankingChange = $modifiedResult['ranking'] - $baseResult['ranking'];
                $scoreChange = $modifiedResult['total_score'] - $baseResult['total_score'];
                
                $changes[] = [
                    'employee_id' => $baseResult['employee_id'],
                    'employee_name' => $baseResult['employee_name'],
                    'base_ranking' => $baseResult['ranking'],
                    'new_ranking' => $modifiedResult['ranking'],
                    'ranking_change' => $rankingChange,
                    'base_score' => $baseResult['total_score'],
                    'new_score' => $modifiedResult['total_score'],
                    'score_change' => $scoreChange,
                    'change_magnitude' => abs($rankingChange)
                ];
            }
        }
        
        return $changes;
    }

    protected function calculateSensitivityScore(array $sensitivityData): float
    {
        if (empty($sensitivityData)) return 0;
        
        $totalRankingChanges = 0;
        $totalVariations = count($sensitivityData);
        
        foreach ($sensitivityData as $data) {
            $rankingChanges = array_column($data['ranking_changes'], 'change_magnitude');
            $totalRankingChanges += array_sum($rankingChanges);
        }
        
        return $totalVariations > 0 ? $totalRankingChanges / $totalVariations : 0;
    }

    protected function calculateImpactScore(array $baseResults, array $modifiedResults): float
    {
        $rankingChanges = $this->compareRankings($baseResults, $modifiedResults);
        $totalChanges = array_sum(array_column($rankingChanges, 'change_magnitude'));
        
        return $totalChanges / count($baseResults);
    }

    protected function calculateStabilityScore(array $rankingChanges): float
    {
        $changes = array_column($rankingChanges, 'change_magnitude');
        $maxPossibleChange = count($changes) - 1;
        $actualChanges = array_sum($changes);
        
        return $maxPossibleChange > 0 ? 1 - ($actualChanges / ($maxPossibleChange * count($changes))) : 1;
    }

    protected function generateWeightScenarios($criterias, int $count): array
    {
        $scenarios = [];
        
        for ($i = 0; $i < $count; $i++) {
            $weights = [];
            $remainingWeight = 100;
            $criteriaCount = $criterias->count();
            
            foreach ($criterias as $index => $criteria) {
                if ($index == $criteriaCount - 1) {
                    // Last criteria gets remaining weight
                    $weights[$criteria->id] = $remainingWeight;
                } else {
                    // Random weight between 5 and 40
                    $maxWeight = min(40, $remainingWeight - (($criteriaCount - $index - 1) * 5));
                    $weight = rand(5, $maxWeight);
                    $weights[$criteria->id] = $weight;
                    $remainingWeight -= $weight;
                }
            }
            
            $scenarios[] = $weights;
        }
        
        return $scenarios;
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
        $squaredDifferences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        $variance = array_sum($squaredDifferences) / count($values);
        return sqrt($variance);
    }

    protected function calculateVariance(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        return array_sum($squaredDifferences) / count($values);
    }

    protected function calculateCoefficientOfVariation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $stdDev = $this->calculateStandardDeviation($values);
        
        return $mean != 0 ? ($stdDev / $mean) * 100 : 0;
    }

    protected function definePerformanceTiers(array $results): array
    {
        $count = count($results);
        
        return [
            'excellent' => array_slice($results, 0, ceil($count * 0.2)),
            'good' => array_slice($results, ceil($count * 0.2), ceil($count * 0.3)),
            'average' => array_slice($results, ceil($count * 0.5), ceil($count * 0.3)),
            'needs_improvement' => array_slice($results, ceil($count * 0.8))
        ];
    }

    protected function calculateDecisionThresholds(array $scores): array
    {
        sort($scores, SORT_NUMERIC | SORT_DESC);
        $count = count($scores);
        
        return [
            'top_10_percent' => $scores[ceil($count * 0.1) - 1] ?? $scores[0],
            'top_25_percent' => $scores[ceil($count * 0.25) - 1] ?? $scores[0],
            'median' => $this->calculateMedian($scores),
            'bottom_25_percent' => $scores[ceil($count * 0.75) - 1] ?? $scores[$count - 1],
            'bottom_10_percent' => $scores[ceil($count * 0.9) - 1] ?? $scores[$count - 1]
        ];
    }

    protected function analyzePerformanceGaps(array $results): array
    {
        if (count($results) < 2) return [];
        
        $gaps = [];
        for ($i = 0; $i < count($results) - 1; $i++) {
            $gaps[] = $results[$i]['total_score'] - $results[$i + 1]['total_score'];
        }
        
        return [
            'largest_gap' => max($gaps),
            'smallest_gap' => min($gaps),
            'average_gap' => array_sum($gaps) / count($gaps),
            'gap_positions' => array_map(function($gap, $index) use ($results) {
                return [
                    'position' => $index + 1,
                    'gap' => $gap,
                    'between' => $results[$index]['employee_name'] . ' and ' . $results[$index + 1]['employee_name']
                ];
            }, $gaps, array_keys($gaps))
        ];
    }

    protected function analyzePerformanceDistribution(array $results): array
    {
        $scores = array_column($results, 'total_score');
        $min = min($scores);
        $max = max($scores);
        $range = $max - $min;
        
        $bins = 5;
        $binSize = $range / $bins;
        $distribution = array_fill(0, $bins, 0);
        
        foreach ($scores as $score) {
            $binIndex = min($bins - 1, floor(($score - $min) / $binSize));
            $distribution[$binIndex]++;
        }
        
        return [
            'bins' => $bins,
            'bin_size' => $binSize,
            'distribution' => $distribution,
            'range' => $range
        ];
    }

    protected function identifyMostStableEmployees(array $stabilityData, array $baseResults): array
    {
        $employeeStability = [];
        
        foreach ($baseResults as $employee) {
            $totalRankingChanges = 0;
            $scenarioCount = 0;
            
            foreach ($stabilityData as $scenario) {
                $employeeChange = collect($scenario['ranking_changes'])
                    ->where('employee_id', $employee['employee_id'])
                    ->first();
                
                if ($employeeChange) {
                    $totalRankingChanges += $employeeChange['change_magnitude'];
                    $scenarioCount++;
                }
            }
            
            $averageChange = $scenarioCount > 0 ? $totalRankingChanges / $scenarioCount : 0;
            
            $employeeStability[] = [
                'employee_id' => $employee['employee_id'],
                'employee_name' => $employee['employee_name'],
                'base_ranking' => $employee['ranking'],
                'average_ranking_change' => $averageChange,
                'stability_score' => 1 / (1 + $averageChange) // Higher score = more stable
            ];
        }
        
        // Sort by stability score (most stable first)
        usort($employeeStability, function($a, $b) {
            return $b['stability_score'] <=> $a['stability_score'];
        });
        
        return array_slice($employeeStability, 0, 5);
    }

    protected function identifyLeastStableEmployees(array $stabilityData, array $baseResults): array
    {
        $mostStable = $this->identifyMostStableEmployees($stabilityData, $baseResults);
        
        // Return the least stable (reverse order, take last 5)
        return array_slice(array_reverse($mostStable), 0, 5);
    }

    protected function getAffectedEmployees(array $baseResults, array $modifiedResults): array
    {
        $changes = $this->compareRankings($baseResults, $modifiedResults);
        
        return array_filter($changes, function($change) {
            return $change['ranking_change'] != 0;
        });
    }
}