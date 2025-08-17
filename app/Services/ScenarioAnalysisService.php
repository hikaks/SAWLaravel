<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScenarioAnalysisService
{
    protected $sawService;

    public function __construct(SAWCalculationService $sawService)
    {
        $this->sawService = $sawService;
    }

    /**
     * Create and analyze a what-if scenario
     */
    public function analyzeScenario(array $scenarioData): array
    {
        $startTime = microtime(true);
        
        try {
            $scenario = $this->validateScenarioData($scenarioData);
            
            // Get base results for comparison
            $baseResults = $this->getBaseResults($scenario['evaluation_period']);
            
            // Calculate scenario results
            $scenarioResults = $this->calculateScenarioResults($scenario);
            
            // Perform comparison analysis
            $analysis = [
                'scenario_info' => $scenario,
                'base_results' => $baseResults,
                'scenario_results' => $scenarioResults,
                'comparison' => $this->compareScenarios($baseResults, $scenarioResults),
                'impact_analysis' => $this->analyzeImpact($baseResults, $scenarioResults),
                'recommendations' => $this->generateRecommendations($scenario, $baseResults, $scenarioResults),
                'execution_time' => microtime(true) - $startTime
            ];

            Log::info("What-if scenario analysis completed", [
                'scenario_type' => $scenario['type'],
                'period' => $scenario['evaluation_period'],
                'execution_time' => $analysis['execution_time']
            ]);

            return $analysis;

        } catch (\Exception $e) {
            Log::error("Scenario analysis failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate multiple scenarios for comparison
     */
    public function generateMultipleScenarios(string $evaluationPeriod, array $scenarioTypes = []): array
    {
        $scenarios = [];
        $baseResults = $this->getBaseResults($evaluationPeriod);
        
        if (empty($scenarioTypes)) {
            $scenarioTypes = ['weight_adjustment', 'performance_boost', 'criteria_focus', 'balanced_approach'];
        }
        
        foreach ($scenarioTypes as $type) {
            try {
                $scenarioData = $this->generateScenarioData($evaluationPeriod, $type);
                $scenarioResults = $this->calculateScenarioResults($scenarioData);
                
                $scenarios[] = [
                    'type' => $type,
                    'name' => $this->getScenarioName($type),
                    'description' => $this->getScenarioDescription($type),
                    'scenario_data' => $scenarioData,
                    'results' => $scenarioResults,
                    'comparison' => $this->compareScenarios($baseResults, $scenarioResults),
                    'feasibility_score' => $this->calculateFeasibilityScore($scenarioData)
                ];
            } catch (\Exception $e) {
                Log::warning("Failed to generate scenario: $type", ['error' => $e->getMessage()]);
            }
        }
        
        return [
            'base_results' => $baseResults,
            'scenarios' => $scenarios,
            'summary' => $this->generateScenarioSummary($scenarios),
            'recommendations' => $this->generateMultiScenarioRecommendations($scenarios)
        ];
    }

    /**
     * Simulate employee performance changes
     */
    public function simulatePerformanceChanges(string $evaluationPeriod, array $changes): array
    {
        $baseResults = $this->getBaseResults($evaluationPeriod);
        $simulatedResults = $this->applyPerformanceChanges($evaluationPeriod, $changes);
        
        return [
            'base_results' => $baseResults,
            'simulated_results' => $simulatedResults,
            'performance_changes' => $changes,
            'impact_analysis' => $this->analyzePerformanceImpact($baseResults, $simulatedResults, $changes),
            'affected_rankings' => $this->compareScenarios($baseResults, $simulatedResults),
            'feasibility_assessment' => $this->assessPerformanceChangeFeasibility($changes)
        ];
    }

    /**
     * Test criteria weight optimization scenarios
     */
    public function optimizeWeights(string $evaluationPeriod, array $constraints = []): array
    {
        $baseResults = $this->getBaseResults($evaluationPeriod);
        $currentWeights = Criteria::pluck('weight', 'id')->toArray();
        
        // Generate optimized weight scenarios
        $optimizationResults = [];
        $objectives = ['maximize_top_performer', 'minimize_variance', 'balanced_distribution'];
        
        foreach ($objectives as $objective) {
            $optimizedWeights = $this->calculateOptimalWeights($evaluationPeriod, $objective, $constraints);
            $optimizedResults = $this->calculateWithCustomWeights($evaluationPeriod, $optimizedWeights);
            
            $optimizationResults[] = [
                'objective' => $objective,
                'objective_name' => $this->getObjectiveName($objective),
                'optimized_weights' => $optimizedWeights,
                'weight_changes' => $this->calculateWeightChanges($currentWeights, $optimizedWeights),
                'results' => $optimizedResults,
                'improvement_score' => $this->calculateImprovementScore($baseResults, $optimizedResults, $objective),
                'comparison' => $this->compareScenarios($baseResults, $optimizedResults)
            ];
        }
        
        return [
            'base_results' => $baseResults,
            'current_weights' => $currentWeights,
            'optimization_results' => $optimizationResults,
            'best_optimization' => $this->selectBestOptimization($optimizationResults),
            'implementation_guide' => $this->generateImplementationGuide($optimizationResults)
        ];
    }

    /**
     * Validate scenario data
     */
    protected function validateScenarioData(array $scenarioData): array
    {
        $required = ['type', 'evaluation_period'];
        
        foreach ($required as $field) {
            if (!isset($scenarioData[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }
        
        // Validate evaluation period exists
        $evaluationExists = Evaluation::where('evaluation_period', $scenarioData['evaluation_period'])->exists();
        if (!$evaluationExists) {
            throw new \InvalidArgumentException("No evaluations found for period: " . $scenarioData['evaluation_period']);
        }
        
        return $scenarioData;
    }

    /**
     * Calculate scenario results based on scenario data
     */
    protected function calculateScenarioResults(array $scenario): array
    {
        switch ($scenario['type']) {
            case 'weight_adjustment':
                return $this->calculateWithCustomWeights($scenario['evaluation_period'], $scenario['weights']);
                
            case 'performance_boost':
                return $this->applyPerformanceChanges($scenario['evaluation_period'], $scenario['performance_changes']);
                
            case 'criteria_addition':
                return $this->simulateNewCriteria($scenario['evaluation_period'], $scenario['new_criteria']);
                
            case 'criteria_removal':
                return $this->simulateRemovedCriteria($scenario['evaluation_period'], $scenario['removed_criteria']);
                
            default:
                throw new \InvalidArgumentException("Unknown scenario type: " . $scenario['type']);
        }
    }

    /**
     * Calculate results with custom weights
     */
    protected function calculateWithCustomWeights(string $evaluationPeriod, array $customWeights): array
    {
        $employees = Employee::all();
        $criterias = Criteria::whereIn('id', array_keys($customWeights))->get();
        $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)->get();
        
        $results = [];
        
        // Get all scores by criteria for normalization
        $scoresByCriteria = [];
        foreach ($criterias as $criteria) {
            $scores = $evaluations->where('criteria_id', $criteria->id)->pluck('score')->toArray();
            $scoresByCriteria[$criteria->id] = $scores;
        }
        
        // Calculate SAW scores for each employee
        foreach ($employees as $employee) {
            $totalScore = 0;
            $criteriaCount = 0;
            
            foreach ($criterias as $criteria) {
                $evaluation = $evaluations->where('employee_id', $employee->id)
                    ->where('criteria_id', $criteria->id)
                    ->first();
                
                if ($evaluation && isset($customWeights[$criteria->id])) {
                    // Normalize score
                    $scores = $scoresByCriteria[$criteria->id];
                    if ($criteria->isBenefit()) {
                        $normalizedScore = max($scores) > 0 ? $evaluation->score / max($scores) : 0;
                    } else {
                        $normalizedScore = $evaluation->score > 0 ? min($scores) / $evaluation->score : 0;
                    }
                    
                    // Apply weight
                    $weight = $customWeights[$criteria->id] / 100;
                    $totalScore += $normalizedScore * $weight;
                    $criteriaCount++;
                }
            }
            
            if ($criteriaCount > 0) {
                $results[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'total_score' => $totalScore,
                    'ranking' => 0 // Will be set after sorting
                ];
            }
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

    /**
     * Apply performance changes to simulation
     */
    protected function applyPerformanceChanges(string $evaluationPeriod, array $changes): array
    {
        $baseResults = $this->getBaseResults($evaluationPeriod);
        $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)->get();
        
        // Apply changes to evaluation scores
        $modifiedEvaluations = $evaluations->map(function($evaluation) use ($changes) {
            $employeeChanges = $changes[$evaluation->employee_id] ?? [];
            $criteriaChange = $employeeChanges[$evaluation->criteria_id] ?? 0;
            
            $newScore = $evaluation->score + $criteriaChange;
            $newScore = max(0, min(100, $newScore)); // Clamp to valid range
            
            return [
                'employee_id' => $evaluation->employee_id,
                'criteria_id' => $evaluation->criteria_id,
                'score' => $newScore
            ];
        });
        
        // Recalculate SAW with modified scores
        return $this->calculateWithModifiedScores($evaluationPeriod, $modifiedEvaluations->toArray());
    }

    /**
     * Calculate results with modified scores
     */
    protected function calculateWithModifiedScores(string $evaluationPeriod, array $modifiedEvaluations): array
    {
        $employees = Employee::all();
        $criterias = Criteria::all();
        
        $results = [];
        
        // Group evaluations by criteria for normalization
        $evaluationsByCriteria = collect($modifiedEvaluations)->groupBy('criteria_id');
        
        foreach ($employees as $employee) {
            $totalScore = 0;
            
            foreach ($criterias as $criteria) {
                $criteriaEvaluations = $evaluationsByCriteria->get($criteria->id, collect());
                $employeeEvaluation = $criteriaEvaluations->where('employee_id', $employee->id)->first();
                
                if ($employeeEvaluation) {
                    $scores = $criteriaEvaluations->pluck('score')->toArray();
                    
                    // Normalize score
                    if ($criteria->isBenefit()) {
                        $normalizedScore = max($scores) > 0 ? $employeeEvaluation['score'] / max($scores) : 0;
                    } else {
                        $normalizedScore = $employeeEvaluation['score'] > 0 ? min($scores) / $employeeEvaluation['score'] : 0;
                    }
                    
                    // Apply weight
                    $totalScore += $normalizedScore * ($criteria->weight / 100);
                }
            }
            
            $results[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'total_score' => $totalScore,
                'ranking' => 0
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

    /**
     * Compare two scenarios
     */
    protected function compareScenarios(array $baseResults, array $scenarioResults): array
    {
        $changes = [];
        
        foreach ($baseResults as $baseResult) {
            $scenarioResult = collect($scenarioResults)
                ->where('employee_id', $baseResult['employee_id'])
                ->first();
            
            if ($scenarioResult) {
                $rankingChange = $scenarioResult['ranking'] - $baseResult['ranking'];
                $scoreChange = $scenarioResult['total_score'] - $baseResult['total_score'];
                
                $changes[] = [
                    'employee_id' => $baseResult['employee_id'],
                    'employee_name' => $baseResult['employee_name'],
                    'base_ranking' => $baseResult['ranking'],
                    'scenario_ranking' => $scenarioResult['ranking'],
                    'ranking_change' => $rankingChange,
                    'base_score' => $baseResult['total_score'],
                    'scenario_score' => $scenarioResult['total_score'],
                    'score_change' => $scoreChange,
                    'change_type' => $this->categorizeChange($rankingChange),
                    'change_magnitude' => abs($rankingChange)
                ];
            }
        }
        
        return [
            'individual_changes' => $changes,
            'summary' => $this->summarizeChanges($changes),
            'winners' => $this->getWinners($changes),
            'losers' => $this->getLosers($changes),
            'unchanged' => $this->getUnchanged($changes)
        ];
    }

    /**
     * Analyze impact of scenario
     */
    protected function analyzeImpact(array $baseResults, array $scenarioResults): array
    {
        $comparison = $this->compareScenarios($baseResults, $scenarioResults);
        
        return [
            'overall_impact_score' => $this->calculateOverallImpact($comparison['individual_changes']),
            'top_performers_impact' => $this->analyzeTopPerformersImpact($baseResults, $scenarioResults),
            'distribution_changes' => $this->analyzeDistributionChanges($baseResults, $scenarioResults),
            'volatility_analysis' => $this->analyzeVolatility($comparison['individual_changes']),
            'fairness_assessment' => $this->assessFairness($baseResults, $scenarioResults)
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    protected function generateRecommendations(array $scenario, array $baseResults, array $scenarioResults): array
    {
        $comparison = $this->compareScenarios($baseResults, $scenarioResults);
        $impact = $this->analyzeImpact($baseResults, $scenarioResults);
        
        $recommendations = [];
        
        // Analyze if scenario is beneficial
        if ($impact['overall_impact_score'] > 0.7) {
            $recommendations[] = [
                'type' => 'positive',
                'priority' => 'high',
                'title' => 'Highly Recommended Implementation',
                'description' => 'This scenario shows significant positive impact on overall performance distribution.',
                'action_items' => $this->generatePositiveActionItems($scenario, $comparison)
            ];
        } elseif ($impact['overall_impact_score'] > 0.4) {
            $recommendations[] = [
                'type' => 'neutral',
                'priority' => 'medium',
                'title' => 'Consider with Modifications',
                'description' => 'This scenario has moderate impact. Consider implementing with adjustments.',
                'action_items' => $this->generateModificationActionItems($scenario, $comparison)
            ];
        } else {
            $recommendations[] = [
                'type' => 'negative',
                'priority' => 'low',
                'title' => 'Not Recommended',
                'description' => 'This scenario may have negative or minimal impact on performance.',
                'action_items' => $this->generateAlternativeActionItems($scenario)
            ];
        }
        
        return $recommendations;
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

    protected function generateScenarioData(string $evaluationPeriod, string $type): array
    {
        switch ($type) {
            case 'weight_adjustment':
                return [
                    'type' => $type,
                    'evaluation_period' => $evaluationPeriod,
                    'weights' => $this->generateBalancedWeights()
                ];
                
            case 'performance_boost':
                return [
                    'type' => $type,
                    'evaluation_period' => $evaluationPeriod,
                    'performance_changes' => $this->generatePerformanceBoost()
                ];
                
            case 'criteria_focus':
                return [
                    'type' => $type,
                    'evaluation_period' => $evaluationPeriod,
                    'weights' => $this->generateFocusedWeights()
                ];
                
            default:
                return [
                    'type' => $type,
                    'evaluation_period' => $evaluationPeriod
                ];
        }
    }

    protected function generateBalancedWeights(): array
    {
        $criterias = Criteria::all();
        $weights = [];
        $equalWeight = round(100 / $criterias->count());
        
        foreach ($criterias as $criteria) {
            $weights[$criteria->id] = $equalWeight;
        }
        
        // Adjust to ensure total is 100
        $total = array_sum($weights);
        if ($total != 100) {
            $firstCriteria = $criterias->first();
            $weights[$firstCriteria->id] += (100 - $total);
        }
        
        return $weights;
    }

    protected function generatePerformanceBoost(): array
    {
        $employees = Employee::take(3)->get(); // Boost top 3 employees
        $changes = [];
        
        foreach ($employees as $employee) {
            $criterias = Criteria::all();
            $employeeChanges = [];
            
            foreach ($criterias as $criteria) {
                $employeeChanges[$criteria->id] = rand(5, 15); // 5-15 point boost
            }
            
            $changes[$employee->id] = $employeeChanges;
        }
        
        return $changes;
    }

    protected function generateFocusedWeights(): array
    {
        $criterias = Criteria::all();
        $weights = [];
        $focusCriteria = $criterias->first();
        
        foreach ($criterias as $criteria) {
            if ($criteria->id == $focusCriteria->id) {
                $weights[$criteria->id] = 50; // Focus 50% on one criteria
            } else {
                $weights[$criteria->id] = round(50 / ($criterias->count() - 1));
            }
        }
        
        return $weights;
    }

    protected function getScenarioName(string $type): string
    {
        return match($type) {
            'weight_adjustment' => 'Balanced Weight Distribution',
            'performance_boost' => 'Top Performer Enhancement',
            'criteria_focus' => 'Single Criteria Focus',
            'balanced_approach' => 'Balanced Evaluation Approach',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }

    protected function getScenarioDescription(string $type): string
    {
        return match($type) {
            'weight_adjustment' => 'Redistributes criteria weights equally across all criteria',
            'performance_boost' => 'Simulates performance improvements for top employees',
            'criteria_focus' => 'Focuses heavily on one primary criteria',
            'balanced_approach' => 'Maintains current balance with minor optimizations',
            default => "Analysis scenario for $type"
        };
    }

    protected function categorizeChange(int $rankingChange): string
    {
        if ($rankingChange < 0) return 'improvement';
        elseif ($rankingChange > 0) return 'decline';
        else return 'no_change';
    }

    protected function summarizeChanges(array $changes): array
    {
        $improvements = array_filter($changes, fn($c) => $c['ranking_change'] < 0);
        $declines = array_filter($changes, fn($c) => $c['ranking_change'] > 0);
        $unchanged = array_filter($changes, fn($c) => $c['ranking_change'] == 0);
        
        return [
            'total_employees' => count($changes),
            'improvements' => count($improvements),
            'declines' => count($declines),
            'unchanged' => count($unchanged),
            'average_change' => array_sum(array_column($changes, 'change_magnitude')) / count($changes),
            'max_improvement' => min(array_column($changes, 'ranking_change')),
            'max_decline' => max(array_column($changes, 'ranking_change'))
        ];
    }

    protected function getWinners(array $changes): array
    {
        $winners = array_filter($changes, fn($c) => $c['ranking_change'] < 0);
        usort($winners, fn($a, $b) => $a['ranking_change'] <=> $b['ranking_change']);
        
        return array_slice($winners, 0, 5);
    }

    protected function getLosers(array $changes): array
    {
        $losers = array_filter($changes, fn($c) => $c['ranking_change'] > 0);
        usort($losers, fn($a, $b) => $b['ranking_change'] <=> $a['ranking_change']);
        
        return array_slice($losers, 0, 5);
    }

    protected function getUnchanged(array $changes): array
    {
        return array_filter($changes, fn($c) => $c['ranking_change'] == 0);
    }

    protected function calculateOverallImpact(array $changes): float
    {
        $improvements = array_filter($changes, fn($c) => $c['ranking_change'] < 0);
        $declines = array_filter($changes, fn($c) => $c['ranking_change'] > 0);
        
        $improvementScore = count($improvements) * 0.6;
        $declineScore = count($declines) * -0.4;
        $totalEmployees = count($changes);
        
        return $totalEmployees > 0 ? ($improvementScore + $declineScore) / $totalEmployees : 0;
    }

    protected function analyzeTopPerformersImpact(array $baseResults, array $scenarioResults): array
    {
        $topPerformers = array_slice($baseResults, 0, 5);
        $impact = [];
        
        foreach ($topPerformers as $performer) {
            $scenarioResult = collect($scenarioResults)
                ->where('employee_id', $performer['employee_id'])
                ->first();
            
            if ($scenarioResult) {
                $impact[] = [
                    'employee_name' => $performer['employee_name'],
                    'base_ranking' => $performer['ranking'],
                    'scenario_ranking' => $scenarioResult['ranking'],
                    'change' => $scenarioResult['ranking'] - $performer['ranking']
                ];
            }
        }
        
        return $impact;
    }

    protected function analyzeDistributionChanges(array $baseResults, array $scenarioResults): array
    {
        $baseScores = array_column($baseResults, 'total_score');
        $scenarioScores = array_column($scenarioResults, 'total_score');
        
        return [
            'base_variance' => $this->calculateVariance($baseScores),
            'scenario_variance' => $this->calculateVariance($scenarioScores),
            'variance_change' => $this->calculateVariance($scenarioScores) - $this->calculateVariance($baseScores),
            'distribution_improvement' => $this->calculateVariance($baseScores) > $this->calculateVariance($scenarioScores)
        ];
    }

    protected function analyzeVolatility(array $changes): array
    {
        $changeMagnitudes = array_column($changes, 'change_magnitude');
        
        return [
            'average_volatility' => array_sum($changeMagnitudes) / count($changeMagnitudes),
            'max_volatility' => max($changeMagnitudes),
            'volatility_distribution' => array_count_values($changeMagnitudes),
            'high_volatility_employees' => array_filter($changes, fn($c) => $c['change_magnitude'] > 3)
        ];
    }

    protected function assessFairness(array $baseResults, array $scenarioResults): array
    {
        // Simple fairness assessment based on score distribution
        $baseScores = array_column($baseResults, 'total_score');
        $scenarioScores = array_column($scenarioResults, 'total_score');
        
        $baseGini = $this->calculateGiniCoefficient($baseScores);
        $scenarioGini = $this->calculateGiniCoefficient($scenarioScores);
        
        return [
            'base_gini_coefficient' => $baseGini,
            'scenario_gini_coefficient' => $scenarioGini,
            'fairness_improvement' => $baseGini > $scenarioGini,
            'fairness_change' => $scenarioGini - $baseGini
        ];
    }

    protected function calculateVariance(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(fn($value) => pow($value - $mean, 2), $values);
        
        return array_sum($squaredDifferences) / count($values);
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

    protected function generatePositiveActionItems(array $scenario, array $comparison): array
    {
        return [
            'Implement the proposed changes gradually over 2-3 evaluation periods',
            'Monitor the impact on employee motivation and performance',
            'Communicate changes clearly to all stakeholders',
            'Set up regular review meetings to assess progress'
        ];
    }

    protected function generateModificationActionItems(array $scenario, array $comparison): array
    {
        return [
            'Consider implementing only the most impactful changes first',
            'Pilot the changes with a smaller group before full implementation',
            'Adjust the scenario parameters to minimize negative impacts',
            'Seek feedback from affected employees before implementation'
        ];
    }

    protected function generateAlternativeActionItems(array $scenario): array
    {
        return [
            'Explore alternative scenarios with different parameters',
            'Focus on individual employee development instead of system changes',
            'Consider gradual improvements rather than major adjustments',
            'Analyze what specific factors are causing the negative impact'
        ];
    }

    protected function calculateOptimalWeights(string $evaluationPeriod, string $objective, array $constraints): array
    {
        // Simplified optimization - in reality, this would use more sophisticated algorithms
        $criterias = Criteria::all();
        $baseWeights = $criterias->pluck('weight', 'id')->toArray();
        
        switch ($objective) {
            case 'maximize_top_performer':
                return $this->optimizeForTopPerformer($evaluationPeriod, $baseWeights, $constraints);
            case 'minimize_variance':
                return $this->optimizeForMinVariance($evaluationPeriod, $baseWeights, $constraints);
            default:
                return $baseWeights;
        }
    }

    protected function optimizeForTopPerformer(string $evaluationPeriod, array $baseWeights, array $constraints): array
    {
        // Simple optimization: increase weights for criteria where top performer excels
        $topPerformer = EvaluationResult::with('employee')
            ->where('evaluation_period', $evaluationPeriod)
            ->orderBy('ranking')
            ->first();
        
        if (!$topPerformer) return $baseWeights;
        
        $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)
            ->where('employee_id', $topPerformer->employee_id)
            ->get();
        
        $optimizedWeights = $baseWeights;
        $totalAdjustment = 0;
        
        foreach ($evaluations as $evaluation) {
            if ($evaluation->score > 80) { // High performance
                $increase = min(10, $constraints['max_increase'] ?? 10);
                $optimizedWeights[$evaluation->criteria_id] += $increase;
                $totalAdjustment += $increase;
            }
        }
        
        // Redistribute to maintain total of 100
        if ($totalAdjustment > 0) {
            foreach ($optimizedWeights as $id => $weight) {
                if (!in_array($id, array_column($evaluations->toArray(), 'criteria_id'))) {
                    $reduction = $totalAdjustment / (count($optimizedWeights) - $evaluations->count());
                    $optimizedWeights[$id] = max(5, $weight - $reduction);
                }
            }
        }
        
        return $optimizedWeights;
    }

    protected function optimizeForMinVariance(string $evaluationPeriod, array $baseWeights, array $constraints): array
    {
        // Simple approach: equal weights to minimize variance
        $criterias = Criteria::all();
        $equalWeight = 100 / $criterias->count();
        
        $optimizedWeights = [];
        foreach ($criterias as $criteria) {
            $optimizedWeights[$criteria->id] = round($equalWeight);
        }
        
        return $optimizedWeights;
    }

    protected function calculateWeightChanges(array $currentWeights, array $optimizedWeights): array
    {
        $changes = [];
        
        foreach ($currentWeights as $id => $currentWeight) {
            $optimizedWeight = $optimizedWeights[$id] ?? $currentWeight;
            $changes[$id] = [
                'current' => $currentWeight,
                'optimized' => $optimizedWeight,
                'change' => $optimizedWeight - $currentWeight,
                'change_percent' => $currentWeight > 0 ? (($optimizedWeight - $currentWeight) / $currentWeight) * 100 : 0
            ];
        }
        
        return $changes;
    }

    protected function calculateImprovementScore(array $baseResults, array $optimizedResults, string $objective): float
    {
        switch ($objective) {
            case 'maximize_top_performer':
                $baseTop = $baseResults[0]['total_score'] ?? 0;
                $optimizedTop = $optimizedResults[0]['total_score'] ?? 0;
                return $baseTop > 0 ? ($optimizedTop - $baseTop) / $baseTop : 0;
                
            case 'minimize_variance':
                $baseScores = array_column($baseResults, 'total_score');
                $optimizedScores = array_column($optimizedResults, 'total_score');
                $baseVariance = $this->calculateVariance($baseScores);
                $optimizedVariance = $this->calculateVariance($optimizedScores);
                return $baseVariance > 0 ? ($baseVariance - $optimizedVariance) / $baseVariance : 0;
                
            default:
                return 0;
        }
    }

    protected function selectBestOptimization(array $optimizationResults): array
    {
        if (empty($optimizationResults)) return [];
        
        usort($optimizationResults, fn($a, $b) => $b['improvement_score'] <=> $a['improvement_score']);
        
        return $optimizationResults[0];
    }

    protected function generateImplementationGuide(array $optimizationResults): array
    {
        return [
            'step_1' => 'Review all optimization scenarios and their impacts',
            'step_2' => 'Select the optimization that best aligns with organizational goals',
            'step_3' => 'Plan a gradual implementation over multiple evaluation periods',
            'step_4' => 'Communicate changes to all stakeholders with clear rationale',
            'step_5' => 'Monitor results and be prepared to make adjustments',
            'considerations' => [
                'Employee morale and acceptance',
                'Organizational culture alignment',
                'Long-term sustainability',
                'Fairness and transparency'
            ]
        ];
    }

    protected function getObjectiveName(string $objective): string
    {
        return match($objective) {
            'maximize_top_performer' => 'Maximize Top Performer Score',
            'minimize_variance' => 'Minimize Score Variance',
            'balanced_distribution' => 'Balanced Score Distribution',
            default => ucfirst(str_replace('_', ' ', $objective))
        };
    }

    protected function calculateFeasibilityScore(array $scenarioData): float
    {
        // Simple feasibility assessment based on scenario type and parameters
        switch ($scenarioData['type']) {
            case 'weight_adjustment':
                $weights = $scenarioData['weights'] ?? [];
                $maxWeight = max($weights);
                $minWeight = min($weights);
                return ($maxWeight <= 60 && $minWeight >= 5) ? 0.8 : 0.4;
                
            case 'performance_boost':
                $changes = $scenarioData['performance_changes'] ?? [];
                $avgChange = array_sum(array_map('array_sum', $changes)) / count($changes);
                return ($avgChange <= 15) ? 0.7 : 0.3;
                
            default:
                return 0.5;
        }
    }

    protected function generateScenarioSummary(array $scenarios): array
    {
        return [
            'total_scenarios' => count($scenarios),
            'high_impact_scenarios' => count(array_filter($scenarios, fn($s) => ($s['comparison']['summary']['improvements'] ?? 0) > 3)),
            'feasible_scenarios' => count(array_filter($scenarios, fn($s) => $s['feasibility_score'] > 0.6)),
            'recommended_scenarios' => array_filter($scenarios, fn($s) => $s['feasibility_score'] > 0.6 && ($s['comparison']['summary']['improvements'] ?? 0) > 2)
        ];
    }

    protected function generateMultiScenarioRecommendations(array $scenarios): array
    {
        $recommendations = [];
        
        $bestScenario = collect($scenarios)->sortByDesc('feasibility_score')->first();
        if ($bestScenario) {
            $recommendations[] = [
                'type' => 'best_scenario',
                'title' => 'Most Feasible Scenario',
                'scenario' => $bestScenario['name'],
                'description' => "The '{$bestScenario['name']}' scenario offers the best balance of impact and feasibility."
            ];
        }
        
        $highImpactScenarios = array_filter($scenarios, fn($s) => ($s['comparison']['summary']['improvements'] ?? 0) > 3);
        if (!empty($highImpactScenarios)) {
            $recommendations[] = [
                'type' => 'high_impact',
                'title' => 'High Impact Scenarios',
                'scenarios' => array_column($highImpactScenarios, 'name'),
                'description' => 'These scenarios show significant positive impact on employee rankings.'
            ];
        }
        
        return $recommendations;
    }

    protected function assessPerformanceChangeFeasibility(array $changes): array
    {
        $totalEmployees = count($changes);
        $totalChanges = array_sum(array_map('array_sum', $changes));
        $avgChangePerEmployee = $totalEmployees > 0 ? $totalChanges / $totalEmployees : 0;
        
        return [
            'feasibility_score' => min(1.0, max(0.0, 1 - ($avgChangePerEmployee / 50))),
            'total_employees_affected' => $totalEmployees,
            'average_change_per_employee' => $avgChangePerEmployee,
            'feasibility_level' => $avgChangePerEmployee <= 10 ? 'high' : ($avgChangePerEmployee <= 25 ? 'medium' : 'low'),
            'recommendations' => $this->generateFeasibilityRecommendations($avgChangePerEmployee)
        ];
    }

    protected function generateFeasibilityRecommendations(float $avgChange): array
    {
        if ($avgChange <= 10) {
            return ['Changes are realistic and achievable with proper training and support'];
        } elseif ($avgChange <= 25) {
            return ['Changes are moderately challenging but achievable with significant effort and resources'];
        } else {
            return ['Changes may be too ambitious and should be scaled down or implemented gradually'];
        }
    }

    protected function analyzePerformanceImpact(array $baseResults, array $simulatedResults, array $changes): array
    {
        $comparison = $this->compareScenarios($baseResults, $simulatedResults);
        
        return [
            'overall_improvement' => $comparison['summary']['improvements'],
            'overall_decline' => $comparison['summary']['declines'],
            'net_positive_impact' => $comparison['summary']['improvements'] - $comparison['summary']['declines'],
            'affected_employees' => count($changes),
            'average_score_change' => array_sum(array_column($comparison['individual_changes'], 'score_change')) / count($comparison['individual_changes']),
            'top_beneficiaries' => $comparison['winners'],
            'most_affected' => $comparison['losers']
        ];
    }
}