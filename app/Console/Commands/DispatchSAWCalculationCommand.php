<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CalculateSAWJob;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchSAWCalculationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saw:dispatch
                           {period? : Evaluation period (e.g., 2024-01)}
                           {--all : Dispatch for all available periods}
                           {--user-id= : User ID who initiated the calculation}
                           {--force : Force dispatch even if already calculated}
                           {--dry-run : Show what would be dispatched without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch SAW calculation jobs to the queue for background processing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->dispatchForAllPeriods();
        }

        $period = $this->argument('period');
        if (!$period) {
            $period = $this->ask('Enter evaluation period (e.g., 2024-01):');
        }

        if (!$this->validatePeriod($period)) {
            $this->error("❌ Invalid period format. Use format: YYYY-MM");
            return Command::FAILURE;
        }

        return $this->dispatchForPeriod($period);
    }

    /**
     * Dispatch SAW calculation for specific period.
     */
    private function dispatchForPeriod(string $period): int
    {
        $this->info("🚀 Dispatching SAW calculation for period: {$period}");

        // Check if already calculated
        if (!$this->option('force') && $this->isAlreadyCalculated($period)) {
            $this->warn("⚠️  SAW calculation for period {$period} already exists.");

            if (!$this->confirm("Do you want to recalculate anyway?")) {
                $this->info('❌ Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Validate data completeness
        if (!$this->validateDataCompleteness($period)) {
            $this->error("❌ Data incomplete for period {$period}. Cannot dispatch calculation.");
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->showDryRunInfo($period);
            return Command::SUCCESS;
        }

        try {
            $userId = $this->option('user-id') ?? 1; // Default to system user

            // Dispatch the job
            CalculateSAWJob::dispatch($period, $userId);

            $this->info("✅ SAW calculation job dispatched successfully for period: {$period}");
            $this->line("   Job queued with priority: " . $this->getQueuePriority($period));
            $this->line("   Check status with: php artisan queue:status");
            $this->line("   Monitor with: php artisan queue:status --monitor");

            // Log the dispatch
            Log::info("SAW calculation job dispatched via command", [
                'period' => $period,
                'user_id' => $userId,
                'command' => 'saw:dispatch'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to dispatch SAW calculation job: " . $e->getMessage());
            Log::error("Failed to dispatch SAW calculation job", [
                'period' => $period,
                'error' => $e->getMessage(),
                'command' => 'saw:dispatch'
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Dispatch SAW calculation for all available periods.
     */
    private function dispatchForAllPeriods(): int
    {
        $this->info("🚀 Dispatching SAW calculation for all available periods");

        $periods = $this->getAvailablePeriods();

        if ($periods->isEmpty()) {
            $this->warn("⚠️  No evaluation periods found.");
            return Command::SUCCESS;
        }

        $this->info("📊 Found " . $periods->count() . " evaluation periods:");
        foreach ($periods as $period) {
            $this->line("   - {$period}");
        }

        if (!$this->option('force') && !$this->confirm("Do you want to dispatch calculations for all periods?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->showDryRunInfoForAll($periods);
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;
        $userId = $this->option('user-id') ?? 1;

        foreach ($periods as $period) {
            try {
                if ($this->validateDataCompleteness($period)) {
                    CalculateSAWJob::dispatch($period, $userId);
                    $this->line("✅ Dispatched for period: {$period}");
                    $successCount++;
                } else {
                    $this->line("❌ Skipped period {$period} - data incomplete");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->line("❌ Failed to dispatch for period {$period}: " . $e->getMessage());
                $failCount++;
            }
        }

        $this->line('');
        $this->info("📊 Dispatch Results:");
        $this->line("   ✅ Successfully dispatched: {$successCount}");
        $this->line("   ❌ Failed/Skipped: {$failCount}");

        if ($successCount > 0) {
            $this->line('');
            $this->info("💡 Monitor progress with:");
            $this->line("   php artisan queue:status --monitor");
        }

        return $failCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Validate period format.
     */
    private function validatePeriod(string $period): bool
    {
        return preg_match('/^\d{4}-\d{2}$/', $period) === 1;
    }

    /**
     * Check if SAW calculation already exists for period.
     */
    private function isAlreadyCalculated(string $period): bool
    {
        // Check if results exist
        $resultsExist = \App\Models\EvaluationResult::where('evaluation_period', $period)->exists();

        // Check if calculation is in progress
        $inProgress = Cache::has("saw_calculation_status_{$period}");

        return $resultsExist || $inProgress;
    }

    /**
     * Validate data completeness for period.
     */
    private function validateDataCompleteness(string $period): bool
    {
        $employees = \App\Models\Employee::count();
        $criterias = \App\Models\Criteria::count();
        $evaluations = Evaluation::where('evaluation_period', $period)->count();

        // Allow some flexibility - at least 80% of expected evaluations
        $expectedEvaluations = $employees * $criterias;
        $minRequiredEvaluations = (int) ($expectedEvaluations * 0.8);

        if ($evaluations < $minRequiredEvaluations) {
            $this->warn("⚠️  Data incomplete for period {$period}:");
            $this->line("   Expected: {$expectedEvaluations} evaluations");
            $this->line("   Found: {$evaluations} evaluations");
            $this->line("   Minimum Required: {$minRequiredEvaluations} evaluations");
            $this->line("   Missing: " . ($expectedEvaluations - $evaluations) . " evaluations");
            return false;
        }

        // Check total weight - allow some flexibility (±5%)
        $totalWeight = \App\Models\Criteria::sum('weight');
        if ($totalWeight < 95 || $totalWeight > 105) {
            $this->warn("⚠️  Total criteria weight is {$totalWeight}%, should be between 95-105%");
            return false;
        }

        $this->info("✅ Data validation passed for period {$period}:");
        $this->line("   Evaluations: {$evaluations}/{$expectedEvaluations} (".round(($evaluations/$expectedEvaluations)*100, 1)."%)");
        $this->line("   Total Weight: {$totalWeight}%");

        return true;
    }

    /**
     * Get available evaluation periods.
     */
    private function getAvailablePeriods()
    {
        return Evaluation::select('evaluation_period')
            ->distinct()
            ->orderBy('evaluation_period', 'desc')
            ->pluck('evaluation_period');
    }

    /**
     * Get queue priority for period.
     */
    private function getQueuePriority(string $period): string
    {
        if ($period === now()->format('Y-m')) {
            return 'high';
        }

        if ($period === now()->subMonth()->format('Y-m')) {
            return 'default';
        }

        return 'low';
    }

    /**
     * Show dry run information.
     */
    private function showDryRunInfo(string $period): void
    {
        $this->info("🔍 DRY RUN - What would be dispatched:");
        $this->line("   Period: {$period}");
        $this->line("   Queue Priority: " . $this->getQueuePriority($period));
        $this->line("   User ID: " . ($this->option('user-id') ?? 'System (1)'));
        $this->line("   Data Complete: " . ($this->validateDataCompleteness($period) ? 'Yes' : 'No'));
        $this->line("   Already Calculated: " . ($this->isAlreadyCalculated($period) ? 'Yes' : 'No'));
    }

    /**
     * Show dry run information for all periods.
     */
    private function showDryRunInfoForAll($periods): void
    {
        $this->info("🔍 DRY RUN - What would be dispatched:");

        foreach ($periods as $period) {
            $dataComplete = $this->validateDataCompleteness($period);
            $alreadyCalculated = $this->isAlreadyCalculated($period);
            $queuePriority = $this->getQueuePriority($period);

            $this->line("   {$period}:");
            $this->line("     Queue: {$queuePriority}");
            $this->line("     Data Complete: " . ($dataComplete ? 'Yes' : 'No'));
            $this->line("     Already Calculated: " . ($alreadyCalculated ? 'Yes' : 'No'));
            $this->line("     Would Dispatch: " . ($dataComplete ? 'Yes' : 'No'));
        }
    }
}
