<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SAWCalculationService
{
    /**
     * Calculate SAW (Simple Additive Weighting) for given evaluation period.
     */
    public function calculateSAW(string $evaluationPeriod): array
    {
        try {
            DB::beginTransaction();

            // Step 1: Get all data
            $employees = Employee::all();
            $criterias = Criteria::all();
            $evaluations = Evaluation::with(['employee', 'criteria'])
                ->where('evaluation_period', $evaluationPeriod)
                ->get();

            // Step 2: Validate data completeness
            $expectedEvaluations = $employees->count() * $criterias->count();
            if ($evaluations->count() !== $expectedEvaluations) {
                throw new \Exception("Data evaluasi tidak lengkap. Dibutuhkan {$expectedEvaluations} evaluasi, ditemukan {$evaluations->count()}");
            }

            // Step 3: Group evaluations by criteria for normalization
            $evaluationsByCriteria = $evaluations->groupBy('criteria_id');
            $normalizedScores = [];

            // Step 4: Normalize scores for each criteria
            foreach ($criterias as $criteria) {
                $criteriaEvaluations = $evaluationsByCriteria->get($criteria->id, collect());
                $scores = $criteriaEvaluations->pluck('score')->toArray();

                if (empty($scores)) {
                    continue;
                }

                // Normalization based on criteria type
                if ($criteria->isBenefit()) {
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

            // Step 5: Calculate weighted scores (SAW)
            $sawResults = [];
            foreach ($employees as $employee) {
                $totalScore = 0;

                foreach ($criterias as $criteria) {
                    $normalizedScore = $normalizedScores[$employee->id][$criteria->id] ?? 0;
                    $weightedScore = $normalizedScore * ($criteria->weight / 100);
                    $totalScore += $weightedScore;
                }

                $sawResults[] = [
                    'employee_id' => $employee->id,
                    'employee' => $employee,
                    'total_score' => round($totalScore, 4),
                    'normalized_scores' => $normalizedScores[$employee->id] ?? [],
                ];
            }

            // Step 6: Sort by total score (descending) and assign rankings
            usort($sawResults, function ($a, $b) {
                return $b['total_score'] <=> $a['total_score'];
            });

            $ranking = 1;
            for ($i = 0; $i < count($sawResults); $i++) {
                // Handle tied scores
                if ($i > 0 && $sawResults[$i]['total_score'] != $sawResults[$i - 1]['total_score']) {
                    $ranking = $i + 1;
                }
                $sawResults[$i]['ranking'] = $ranking;
            }

            // Step 7: Save results to database
            $this->saveResults($sawResults, $evaluationPeriod);

            DB::commit();

            Log::info("SAW calculation completed for period: {$evaluationPeriod}", [
                'total_employees' => count($sawResults),
                'evaluation_period' => $evaluationPeriod
            ]);

            return $sawResults;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SAW calculation failed: " . $e->getMessage(), [
                'evaluation_period' => $evaluationPeriod,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Save SAW calculation results to database.
     */
    private function saveResults(array $sawResults, string $evaluationPeriod): void
    {
        // Prepare data for upsert
        $data = [];
        foreach ($sawResults as $result) {
            $data[] = [
                'employee_id' => $result['employee_id'],
                'total_score' => $result['total_score'],
                'ranking' => $result['ranking'],
                'evaluation_period' => $evaluationPeriod,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        // Use upsert to handle duplicates gracefully
        EvaluationResult::upsert(
            $data,
            ['employee_id', 'evaluation_period'], // Unique keys
            ['total_score', 'ranking', 'updated_at'] // Fields to update
        );
    }

    /**
     * Get detailed calculation steps for transparency.
     */
    public function getCalculationDetails(string $evaluationPeriod): array
    {
        $employees = Employee::all();
        $criterias = Criteria::all();
        $evaluations = Evaluation::with(['employee', 'criteria'])
            ->where('evaluation_period', $evaluationPeriod)
            ->get();

        $details = [
            'raw_data' => [],
            'normalization_steps' => [],
            'weighted_calculations' => [],
            'final_rankings' => []
        ];

        // Raw data matrix
        foreach ($employees as $employee) {
            $details['raw_data'][$employee->id] = [
                'employee' => $employee->name,
                'scores' => []
            ];

            foreach ($criterias as $criteria) {
                $evaluation = $evaluations->where('employee_id', $employee->id)
                    ->where('criteria_id', $criteria->id)
                    ->first();

                $details['raw_data'][$employee->id]['scores'][$criteria->id] = [
                    'criteria' => $criteria->name,
                    'score' => $evaluation ? $evaluation->score : 0,
                    'weight' => $criteria->weight,
                    'type' => $criteria->type
                ];
            }
        }

        return $details;
    }

    /**
     * Validate if evaluation period is ready for SAW calculation.
     */
    public function validateEvaluationPeriod(string $evaluationPeriod): array
    {
        $employees = Employee::count();
        $criterias = Criteria::count();
        $evaluations = Evaluation::where('evaluation_period', $evaluationPeriod)->count();
        $totalWeight = Criteria::sum('weight');

        $expectedEvaluations = $employees * $criterias;
        $isComplete = $evaluations === $expectedEvaluations;
        $isWeightValid = (int)$totalWeight === 100;

        return [
            'is_ready' => $isComplete && $isWeightValid,
            'total_employees' => $employees,
            'total_criterias' => $criterias,
            'total_evaluations' => $evaluations,
            'expected_evaluations' => $expectedEvaluations,
            'completion_percentage' => $expectedEvaluations > 0 ? round(($evaluations / $expectedEvaluations) * 100, 2) : 0,
            'total_weight' => $totalWeight,
            'is_weight_valid' => $isWeightValid,
            'missing_evaluations' => max(0, $expectedEvaluations - $evaluations),
            'errors' => []
        ];
    }

    /**
     * Get available evaluation periods.
     */
    public function getAvailablePeriods(): array
    {
        return Evaluation::select('evaluation_period')
            ->distinct()
            ->orderByDesc('evaluation_period')
            ->pluck('evaluation_period')
            ->toArray();
    }

    /**
     * Compare rankings between different periods.
     */
    public function compareRankings(array $periods): array
    {
        $comparison = [];

        foreach ($periods as $period) {
            $results = EvaluationResult::with('employee')
                ->where('evaluation_period', $period)
                ->orderBy('ranking')
                ->get();

            $comparison[$period] = $results->map(function ($result) {
                return [
                    'employee_id' => $result->employee_id,
                    'employee_name' => $result->employee->name,
                    'ranking' => $result->ranking,
                    'total_score' => $result->total_score
                ];
            })->toArray();
        }

        return $comparison;
    }
}
