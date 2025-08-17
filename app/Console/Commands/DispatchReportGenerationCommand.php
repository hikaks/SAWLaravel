<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateReportJob;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchReportGenerationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:dispatch
                           {type : Report type (employee_list, evaluation_summary, saw_results, performance_report, custom_report)}
                           {--period= : Evaluation period (e.g., 2024-01)}
                           {--user-id= : User ID who requested the report}
                           {--user-email= : User email for delivery}
                           {--department= : Filter by department (for custom reports)}
                           {--position= : Filter by position (for custom reports)}
                           {--all-periods : Generate for all available periods}
                           {--dry-run : Show what would be dispatched without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch report generation jobs to the queue for background processing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reportType = $this->argument('type');

        if (!$this->validateReportType($reportType)) {
            $this->error("❌ Invalid report type. Available types: employee_list, evaluation_summary, saw_results, performance_report, custom_report");
            return Command::FAILURE;
        }

        if ($this->option('all-periods')) {
            return $this->dispatchForAllPeriods($reportType);
        }

        $period = $this->option('period');
        if (!$period) {
            $period = $this->ask('Enter evaluation period (e.g., 2024-01) or press Enter for all periods:');
            if (empty($period)) {
                return $this->dispatchForAllPeriods($reportType);
            }
        }

        return $this->dispatchForPeriod($reportType, $period);
    }

    /**
     * Dispatch report generation for specific period.
     */
    private function dispatchForPeriod(string $reportType, string $period): int
    {
        $this->info("🚀 Dispatching {$reportType} report generation for period: {$period}");

        // Validate period format
        if (!$this->validatePeriod($period)) {
            $this->error("❌ Invalid period format. Use format: YYYY-MM");
            return Command::FAILURE;
        }

        // Validate data exists for period
        if (!$this->validatePeriodData($period, $reportType)) {
            $this->error("❌ No data available for period {$period} and report type {$reportType}");
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->showDryRunInfo($reportType, $period);
            return Command::SUCCESS;
        }

        try {
            $userId = $this->getUserId();
            $userEmail = $this->getUserEmail($userId);
            $parameters = $this->getReportParameters();

            // Dispatch the job
            GenerateReportJob::dispatch(
                $reportType,
                $userId,
                $userEmail,
                $period,
                $parameters
            );

            $this->info("✅ Report generation job dispatched successfully!");
            $this->line("   Report Type: {$reportType}");
            $this->line("   Period: {$period}");
            $this->line("   User: {$userEmail}");
            $this->line("   Queue Priority: " . $this->getQueuePriority($reportType));
            $this->line("   Check status with: php artisan queue:status");

            // Log the dispatch
            Log::info("Report generation job dispatched via command", [
                'report_type' => $reportType,
                'period' => $period,
                'user_id' => $userId,
                'user_email' => $userEmail,
                'command' => 'report:dispatch'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to dispatch report generation job: " . $e->getMessage());
            Log::error("Failed to dispatch report generation job", [
                'report_type' => $reportType,
                'period' => $period,
                'error' => $e->getMessage(),
                'command' => 'report:dispatch'
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Dispatch report generation for all available periods.
     */
    private function dispatchForAllPeriods(string $reportType): int
    {
        $this->info("🚀 Dispatching {$reportType} report generation for all available periods");

        $periods = $this->getAvailablePeriods($reportType);

        if ($periods->isEmpty()) {
            $this->warn("⚠️  No evaluation periods found for report type '{$reportType}'.");
            return Command::SUCCESS;
        }

        $this->info("📊 Found " . $periods->count() . " evaluation periods:");
        foreach ($periods as $period) {
            $this->line("   - {$period}");
        }

        if (!$this->confirm("Do you want to dispatch report generation for all periods?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->showDryRunInfoForAll($reportType, $periods);
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;
        $userId = $this->getUserId();
        $userEmail = $this->getUserEmail($userId);
        $parameters = $this->getReportParameters();

        foreach ($periods as $period) {
            try {
                if ($this->validatePeriodData($period, $reportType)) {
                    GenerateReportJob::dispatch(
                        $reportType,
                        $userId,
                        $userEmail,
                        $period,
                        $parameters
                    );
                    $this->line("✅ Dispatched for period: {$period}");
                    $successCount++;
                } else {
                    $this->line("❌ Skipped period {$period} - no data available");
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
     * Validate report type.
     */
    private function validateReportType(string $type): bool
    {
        $validTypes = [
            'employee_list',
            'evaluation_summary',
            'saw_results',
            'performance_report',
            'custom_report'
        ];

        return in_array($type, $validTypes);
    }

    /**
     * Validate period format.
     */
    private function validatePeriod(string $period): bool
    {
        return preg_match('/^\d{4}-\d{2}$/', $period) === 1;
    }

    /**
     * Validate data exists for period and report type.
     */
    private function validatePeriodData(string $period, string $reportType): bool
    {
        switch ($reportType) {
            case 'employee_list':
                return \App\Models\Employee::count() > 0;

            case 'evaluation_summary':
                return Evaluation::where('evaluation_period', $period)->exists();

            case 'saw_results':
                return \App\Models\EvaluationResult::where('evaluation_period', $period)->exists();

            case 'performance_report':
                return \App\Models\EvaluationResult::where('evaluation_period', $period)->exists();

            case 'custom_report':
                return \App\Models\Employee::count() > 0;

            default:
                return false;
        }
    }

    /**
     * Get available evaluation periods for report type.
     */
    private function getAvailablePeriods(string $reportType)
    {
        switch ($reportType) {
            case 'employee_list':
            case 'custom_report':
                // For employee reports, return current period
                return collect([now()->format('Y-m')]);

            case 'evaluation_summary':
                return Evaluation::select('evaluation_period')
                    ->distinct()
                    ->orderBy('evaluation_period', 'desc')
                    ->pluck('evaluation_period');

            case 'saw_results':
            case 'performance_report':
                return \App\Models\EvaluationResult::select('evaluation_period')
                    ->distinct()
                    ->orderBy('evaluation_period', 'desc')
                    ->pluck('evaluation_period');

            default:
                return collect();
        }
    }

    /**
     * Get user ID for the report.
     */
    private function getUserId(): int
    {
        $userId = $this->option('user-id');

        if ($userId) {
            return (int) $userId;
        }

        // Try to get from authenticated user or default to admin
        if (auth()->check()) {
            return auth()->id();
        }

        // Get first admin user
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Get user email for delivery.
     */
    private function getUserEmail(int $userId): string
    {
        $userEmail = $this->option('user-email');

        if ($userEmail) {
            return $userEmail;
        }

        // Get from user record
        $user = User::find($userId);
        return $user ? $user->email : 'admin@saw.com';
    }

    /**
     * Get report parameters.
     */
    private function getReportParameters(): array
    {
        $parameters = [];

        if ($department = $this->option('department')) {
            $parameters['department'] = $department;
        }

        if ($position = $this->option('position')) {
            $parameters['position'] = $position;
        }

        return $parameters;
    }

    /**
     * Get queue priority for report type.
     */
    private function getQueuePriority(string $reportType): string
    {
        return match ($reportType) {
            'saw_results', 'performance_report' => 'high',
            'evaluation_summary' => 'default',
            default => 'low'
        };
    }

    /**
     * Show dry run information.
     */
    private function showDryRunInfo(string $reportType, string $period): void
    {
        $this->info("🔍 DRY RUN - What would be dispatched:");
        $this->line("   Report Type: {$reportType}");
        $this->line("   Period: {$period}");
        $this->line("   User ID: " . $this->getUserId());
        $this->line("   User Email: " . $this->getUserEmail($this->getUserId()));
        $this->line("   Queue Priority: " . $this->getQueuePriority($reportType));
        $this->line("   Data Available: " . ($this->validatePeriodData($period, $reportType) ? 'Yes' : 'No'));

        $parameters = $this->getReportParameters();
        if (!empty($parameters)) {
            $this->line("   Parameters: " . json_encode($parameters));
        }
    }

    /**
     * Show dry run information for all periods.
     */
    private function showDryRunInfoForAll(string $reportType, $periods): void
    {
        $this->info("🔍 DRY RUN - What would be dispatched:");
        $this->line("   Report Type: {$reportType}");
        $this->line("   User ID: " . $this->getUserId());
        $this->line("   User Email: " . $this->getUserEmail($this->getUserId()));
        $this->line("   Queue Priority: " . $this->getQueuePriority($reportType));

        $parameters = $this->getReportParameters();
        if (!empty($parameters)) {
            $this->line("   Parameters: " . json_encode($parameters));
        }

        $this->line('');
        $this->line("   Periods:");
        foreach ($periods as $period) {
            $dataAvailable = $this->validatePeriodData($period, $reportType);
            $this->line("     {$period}: " . ($dataAvailable ? '✅ Data Available' : '❌ No Data'));
        }
    }
}
