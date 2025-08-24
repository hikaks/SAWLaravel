<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        // Create criteria with consistent weights
        $criteria = [
            ['name' => 'Kinerja Kerja', 'weight' => 25, 'type' => 'benefit'],
            ['name' => 'Kehadiran', 'weight' => 20, 'type' => 'benefit'],
            ['name' => 'Kemampuan Teknis', 'weight' => 20, 'type' => 'benefit'],
            ['name' => 'Kerja Sama Tim', 'weight' => 15, 'type' => 'benefit'],
            ['name' => 'Disiplin', 'weight' => 10, 'type' => 'benefit'],
            ['name' => 'Komunikasi', 'weight' => 10, 'type' => 'benefit']
        ];

        foreach ($criteria as $criterion) {
            Criteria::create($criterion);
        }

        // Create employees
        $employees = Employee::factory(10)->create();

        // Create evaluation periods
        $periods = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05'];

        // Create evaluations and results for each period
        foreach ($periods as $period) {
            foreach ($employees as $employee) {
                // Create evaluations for each criteria
                foreach (Criteria::all() as $criterion) {
                    Evaluation::create([
                        'employee_id' => $employee->id,
                        'criteria_id' => $criterion->id,
                        'evaluation_period' => $period,
                        'score' => fake()->numberBetween(60, 100) // Score 60-100
                    ]);
                }

                // Calculate total score using SAW method
                $evaluations = Evaluation::where('employee_id', $employee->id)
                    ->where('evaluation_period', $period)
                    ->with('criteria')
                    ->get();

                $totalScore = 0;
                $totalWeight = 0;

                foreach ($evaluations as $evaluation) {
                    $score = $evaluation->score;
                    $weight = $evaluation->criteria->weight;

                    if ($evaluation->criteria->type === 'cost') {
                        // For cost criteria, invert the score
                        $score = 1 - $score;
                    }

                    $totalScore += $score * $weight;
                    $totalWeight += $weight;
                }

                $finalScore = $totalWeight > 0 ? $totalScore / $totalWeight : 0;

                // Create evaluation result
                EvaluationResult::create([
                    'employee_id' => $employee->id,
                    'evaluation_period' => $period,
                    'total_score' => round($finalScore, 4),
                    'ranking' => 0 // Will be updated below
                ]);
            }

            // Update rankings for this period
            $results = EvaluationResult::where('evaluation_period', $period)
                ->orderByDesc('total_score')
                ->get();

            foreach ($results as $index => $result) {
                $result->update(['ranking' => $index + 1]);
            }
        }

        echo "✅ Database seeded successfully!\n";
        echo "📊 Created:\n";
        echo "  - Users: " . User::count() . "\n";
        echo "  - Employees: " . Employee::count() . "\n";
        echo "  - Criteria: " . Criteria::count() . "\n";
        echo "  - Evaluations: " . Evaluation::count() . "\n";
        echo "  - Evaluation Results: " . EvaluationResult::count() . "\n";
        echo "  - Periods: " . count($periods) . "\n";
    }
}
