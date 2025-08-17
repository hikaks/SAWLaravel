<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-app
                           {--type= : Specific cache type to clear (saw_results, dashboard, chart_data, navigation, etc.)}
                           {--period= : Specific period to clear (for SAW results)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application-specific caches with granular control';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting application cache clearing...');

        $type = $this->option('type');
        $period = $this->option('period');

        try {
            if ($type) {
                $this->clearSpecificCache($type, $period);
            } else {
                $this->clearAllCaches();
            }

            $this->info('✅ Cache clearing completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Cache clearing failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Clear specific cache type
     */
    private function clearSpecificCache(string $type, ?string $period = null)
    {
        $this->info("🎯 Clearing specific cache: {$type}");

        switch ($type) {
            case 'saw_results':
                if ($period) {
                    $this->cacheService->invalidateSAWResults($period);
                    $this->info("Cleared SAW results for period: {$period}");
                } else {
                    $this->cacheService->invalidateSAWResults();
                    $this->info("Cleared all SAW results");
                }
                break;

            case 'dashboard':
                $this->cacheService->invalidateDashboard();
                $this->info("Cleared dashboard cache");
                break;

            case 'chart_data':
                $this->cacheService->invalidateChartData();
                $this->info("Cleared chart data cache");
                break;

            case 'navigation':
                $this->cacheService->invalidateNavigation();
                $this->info("Cleared navigation cache");
                break;

            case 'employee_data':
                $this->cacheService->invalidateEmployeeData();
                $this->info("Cleared employee data cache");
                break;

            case 'criteria_data':
                $this->cacheService->invalidateCriteriaData();
                $this->info("Cleared criteria data cache");
                break;

            case 'evaluation_data':
                $this->cacheService->invalidateEvaluationData();
                $this->info("Cleared evaluation data cache");
                break;

            case 'periods':
                $this->cacheService->invalidatePeriods();
                $this->info("Cleared periods cache");
                break;

            default:
                $this->error("Unknown cache type: {$type}");
                $this->info("Available types: saw_results, dashboard, chart_data, navigation, employee_data, criteria_data, evaluation_data, periods");
                return;
        }
    }

    /**
     * Clear all application caches
     */
    private function clearAllCaches()
    {
        $this->info("🗑️ Clearing all application caches...");

        // Show confirmation
        if (!$this->confirm('This will clear ALL application caches. Are you sure?')) {
            $this->info('Cache clearing cancelled.');
            return;
        }

        // Clear all caches
        $this->cacheService->clearAll();

        $this->info("🔥 All application caches have been cleared!");
    }
}
