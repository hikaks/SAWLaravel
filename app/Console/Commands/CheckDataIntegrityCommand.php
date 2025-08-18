<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Support\Facades\DB;

class CheckDataIntegrityCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'data:check-integrity {--fix : Automatically fix issues where possible}';

    /**
     * The console command description.
     */
    protected $description = 'Check data integrity and consistency';

    private int $issuesFound = 0;
    private int $issuesFixed = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Starting Data Integrity Check...\n");

        $this->checkCriteriaWeights();
        $this->checkOrphanedEvaluations();
        $this->checkIncompleteEvaluations();
        $this->checkDuplicateEvaluations();
        $this->checkInvalidScores();
        $this->checkEvaluationResults();

        $this->info("\n📊 Summary:");
        $this->info("Issues found: {$this->issuesFound}");
        
        if ($this->option('fix')) {
            $this->info("Issues fixed: {$this->issuesFixed}");
        }

        if ($this->issuesFound === 0) {
            $this->info("✅ All data integrity checks passed!");
            return Command::SUCCESS;
        } else {
            $this->warn("⚠️  Found {$this->issuesFound} issues. Run with --fix to auto-fix where possible.");
            return Command::FAILURE;
        }
    }

    private function checkCriteriaWeights(): void
    {
        $this->line("Checking criteria weights...");
        
        $totalWeight = Criteria::sum('weight');
        
        if ($totalWeight != 100) {
            $this->issuesFound++;
            $this->error("❌ Criteria weights total {$totalWeight}% (should be 100%)");
            
            if ($this->option('fix')) {
                // Simple fix: distribute weight evenly
                $criteriaCount = Criteria::count();
                if ($criteriaCount > 0) {
                    $equalWeight = floor(100 / $criteriaCount);
                    $remainder = 100 % $criteriaCount;
                    
                    $criterias = Criteria::all();
                    foreach ($criterias as $index => $criteria) {
                        $weight = $equalWeight + ($index < $remainder ? 1 : 0);
                        $criteria->update(['weight' => $weight]);
                    }
                    
                    $this->issuesFixed++;
                    $this->info("  ✓ Fixed: Redistributed weights evenly");
                }
            }
        } else {
            $this->info("  ✓ Criteria weights are correct (100%)");
        }
    }

    private function checkOrphanedEvaluations(): void
    {
        $this->line("Checking for orphaned evaluations...");
        
        $orphanedByEmployee = Evaluation::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('employees')
                  ->whereColumn('employees.id', 'evaluations.employee_id');
        })->count();
        
        $orphanedByCriteria = Evaluation::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('criterias')
                  ->whereColumn('criterias.id', 'evaluations.criteria_id');
        })->count();

        if ($orphanedByEmployee > 0) {
            $this->issuesFound++;
            $this->error("❌ Found {$orphanedByEmployee} evaluations with invalid employee_id");
            
            if ($this->option('fix')) {
                Evaluation::whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('employees')
                          ->whereColumn('employees.id', 'evaluations.employee_id');
                })->delete();
                
                $this->issuesFixed++;
                $this->info("  ✓ Fixed: Deleted orphaned evaluations");
            }
        }

        if ($orphanedByCriteria > 0) {
            $this->issuesFound++;
            $this->error("❌ Found {$orphanedByCriteria} evaluations with invalid criteria_id");
            
            if ($this->option('fix')) {
                Evaluation::whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('criterias')
                          ->whereColumn('criterias.id', 'evaluations.criteria_id');
                })->delete();
                
                $this->issuesFixed++;
                $this->info("  ✓ Fixed: Deleted orphaned evaluations");
            }
        }

        if ($orphanedByEmployee === 0 && $orphanedByCriteria === 0) {
            $this->info("  ✓ No orphaned evaluations found");
        }
    }

    private function checkIncompleteEvaluations(): void
    {
        $this->line("Checking for incomplete evaluations...");
        
        $periods = Evaluation::distinct('evaluation_period')->pluck('evaluation_period');
        $totalEmployees = Employee::count();
        $totalCriteria = Criteria::count();
        $expectedPerPeriod = $totalEmployees * $totalCriteria;
        
        foreach ($periods as $period) {
            $actualCount = Evaluation::where('evaluation_period', $period)->count();
            
            if ($actualCount < $expectedPerPeriod) {
                $this->issuesFound++;
                $missing = $expectedPerPeriod - $actualCount;
                $this->warn("⚠️  Period {$period}: Missing {$missing} evaluations ({$actualCount}/{$expectedPerPeriod})");
                
                // Show which employees/criteria are missing
                $existingCombos = Evaluation::where('evaluation_period', $period)
                    ->select('employee_id', 'criteria_id')
                    ->get()
                    ->map(fn($e) => "{$e->employee_id}-{$e->criteria_id}")
                    ->toArray();
                
                $missingCount = 0;
                foreach (Employee::all() as $employee) {
                    foreach (Criteria::all() as $criteria) {
                        $combo = "{$employee->id}-{$criteria->id}";
                        if (!in_array($combo, $existingCombos)) {
                            $missingCount++;
                            if ($missingCount <= 5) { // Show first 5 missing
                                $this->line("    Missing: {$employee->name} - {$criteria->name}");
                            }
                        }
                    }
                }
                
                if ($missingCount > 5) {
                    $this->line("    ... and " . ($missingCount - 5) . " more");
                }
            }
        }
        
        if ($this->issuesFound === 0) {
            $this->info("  ✓ All evaluation periods are complete");
        }
    }

    private function checkDuplicateEvaluations(): void
    {
        $this->line("Checking for duplicate evaluations...");
        
        $duplicates = DB::table('evaluations')
            ->select('employee_id', 'criteria_id', 'evaluation_period', DB::raw('COUNT(*) as count'))
            ->groupBy('employee_id', 'criteria_id', 'evaluation_period')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $this->issuesFound++;
            $this->error("❌ Found {$duplicates->count()} duplicate evaluation combinations");
            
            foreach ($duplicates as $duplicate) {
                $employee = Employee::find($duplicate->employee_id);
                $criteria = Criteria::find($duplicate->criteria_id);
                $employeeName = $employee->name ?? 'Unknown';
                $criteriaName = $criteria->name ?? 'Unknown';
                $this->line("    {$employeeName} - {$criteriaName} - {$duplicate->evaluation_period} ({$duplicate->count} records)");
            }
            
            if ($this->option('fix')) {
                foreach ($duplicates as $duplicate) {
                    // Keep the latest record, delete others
                    $evaluations = Evaluation::where([
                        'employee_id' => $duplicate->employee_id,
                        'criteria_id' => $duplicate->criteria_id,
                        'evaluation_period' => $duplicate->evaluation_period,
                    ])->orderBy('created_at', 'desc')->get();
                    
                    // Delete all except the first (latest)
                    $evaluations->skip(1)->each->delete();
                }
                
                $this->issuesFixed++;
                $this->info("  ✓ Fixed: Kept latest records, deleted duplicates");
            }
        } else {
            $this->info("  ✓ No duplicate evaluations found");
        }
    }

    private function checkInvalidScores(): void
    {
        $this->line("Checking for invalid scores...");
        
        $invalidScores = Evaluation::where('score', '<', 1)
            ->orWhere('score', '>', 100)
            ->count();

        if ($invalidScores > 0) {
            $this->issuesFound++;
            $this->error("❌ Found {$invalidScores} evaluations with invalid scores (must be 1-100)");
            
            if ($this->option('fix')) {
                // Fix scores that are out of range
                Evaluation::where('score', '<', 1)->update(['score' => 1]);
                Evaluation::where('score', '>', 100)->update(['score' => 100]);
                
                $this->issuesFixed++;
                $this->info("  ✓ Fixed: Clamped scores to valid range (1-100)");
            }
        } else {
            $this->info("  ✓ All scores are within valid range (1-100)");
        }
    }

    private function checkEvaluationResults(): void
    {
        $this->line("Checking evaluation results consistency...");
        
        $periods = EvaluationResult::distinct('evaluation_period')->pluck('evaluation_period');
        
        foreach ($periods as $period) {
            $resultsCount = EvaluationResult::where('evaluation_period', $period)->count();
            $employeesCount = Employee::count();
            
            if ($resultsCount != $employeesCount) {
                $this->issuesFound++;
                $this->warn("⚠️  Period {$period}: Results count ({$resultsCount}) doesn't match employees count ({$employeesCount})");
            }
            
            // Check if rankings are sequential
            $rankings = EvaluationResult::where('evaluation_period', $period)
                ->orderBy('ranking')
                ->pluck('ranking')
                ->toArray();
            
            $expectedRankings = range(1, count($rankings));
            
            if ($rankings !== $expectedRankings) {
                $this->issuesFound++;
                $this->warn("⚠️  Period {$period}: Rankings are not sequential");
                
                if ($this->option('fix')) {
                    // Recalculate rankings
                    $results = EvaluationResult::where('evaluation_period', $period)
                        ->orderByDesc('total_score')
                        ->get();
                    
                    foreach ($results as $index => $result) {
                        $result->update(['ranking' => $index + 1]);
                    }
                    
                    $this->issuesFixed++;
                    $this->info("  ✓ Fixed: Recalculated rankings for period {$period}");
                }
            }
        }
        
        if ($this->issuesFound === 0) {
            $this->info("  ✓ All evaluation results are consistent");
        }
    }
}