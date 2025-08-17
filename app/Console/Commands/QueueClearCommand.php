<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class QueueClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:clear
                           {--failed : Clear failed jobs only}
                           {--completed : Clear completed job statuses from cache}
                           {--all : Clear all queue data (pending, failed, and cache)}
                           {--queue= : Clear specific queue (high, default, low)}
                           {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear queue data including pending jobs, failed jobs, and cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('failed')) {
            return $this->clearFailedJobs();
        }

        if ($this->option('completed')) {
            return $this->clearCompletedStatuses();
        }

        if ($this->option('all')) {
            return $this->clearAllQueueData();
        }

        if ($queue = $this->option('queue')) {
            return $this->clearSpecificQueue($queue);
        }

        // Default: clear pending jobs
        return $this->clearPendingJobs();
    }

    /**
     * Clear pending jobs.
     */
    private function clearPendingJobs(): int
    {
        $pendingCount = DB::table('jobs')->count();

        if ($pendingCount === 0) {
            $this->info('✅ No pending jobs to clear.');
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to clear {$pendingCount} pending jobs?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            DB::table('jobs')->delete();

            $this->info("✅ Successfully cleared {$pendingCount} pending jobs.");

            // Clear any related cache
            $this->clearRelatedCache();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to clear pending jobs: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear failed jobs.
     */
    private function clearFailedJobs(): int
    {
        $failedCount = DB::table('failed_jobs')->count();

        if ($failedCount === 0) {
            $this->info('✅ No failed jobs to clear.');
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to clear {$failedCount} failed jobs?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            DB::table('failed_jobs')->delete();

            $this->info("✅ Successfully cleared {$failedCount} failed jobs.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to clear failed jobs: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear completed job statuses from cache.
     */
    private function clearCompletedStatuses(): int
    {
        $this->info('🧹 Clearing completed job statuses from cache...');

        $clearedCount = 0;

        // Clear SAW calculation statuses
        $sawKeys = $this->getCacheKeysByPattern('saw_calculation_status_*');
        foreach ($sawKeys as $key) {
            Cache::forget($key);
            $clearedCount++;
        }

        // Clear report generation statuses
        $reportKeys = $this->getCacheKeysByPattern('report_generation_status_*');
        foreach ($reportKeys as $key) {
            Cache::forget($key);
            $clearedCount++;
        }

        // Clear notification statuses
        $notificationKeys = $this->getCacheKeysByPattern('notification_status_*');
        foreach ($notificationKeys as $key) {
            Cache::forget($key);
            $clearedCount++;
        }

        $this->info("✅ Successfully cleared {$clearedCount} completed job statuses from cache.");
        return Command::SUCCESS;
    }

    /**
     * Clear all queue data.
     */
    private function clearAllQueueData(): int
    {
        $pendingCount = DB::table('jobs')->count();
        $failedCount = DB::table('failed_jobs')->count();
        $totalJobs = $pendingCount + $failedCount;

        if ($totalJobs === 0) {
            $this->info('✅ No queue data to clear.');
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to clear ALL queue data? This will remove {$pendingCount} pending jobs and {$failedCount} failed jobs.")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            // Clear pending jobs
            DB::table('jobs')->delete();

            // Clear failed jobs
            DB::table('failed_jobs')->delete();

            // Clear job batches
            DB::table('job_batches')->delete();

            // Clear related cache
            $this->clearRelatedCache();

            $this->info("✅ Successfully cleared all queue data:");
            $this->line("   - {$pendingCount} pending jobs");
            $this->line("   - {$failedCount} failed jobs");
            $this->line("   - Job batches");
            $this->line("   - Related cache");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to clear all queue data: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear specific queue.
     */
    private function clearSpecificQueue(string $queue): int
    {
        if (!in_array($queue, ['high', 'default', 'low'])) {
            $this->error("❌ Invalid queue name. Must be one of: high, default, low");
            return Command::FAILURE;
        }

        $pendingCount = DB::table('jobs')->where('queue', $queue)->count();
        $failedCount = DB::table('failed_jobs')->where('queue', $queue)->count();
        $totalJobs = $pendingCount + $failedCount;

        if ($totalJobs === 0) {
            $this->info("✅ No jobs found in '{$queue}' queue.");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to clear '{$queue}' queue? This will remove {$pendingCount} pending jobs and {$failedCount} failed jobs.")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            // Clear pending jobs from specific queue
            DB::table('jobs')->where('queue', $queue)->delete();

            // Clear failed jobs from specific queue
            DB::table('failed_jobs')->where('queue', $queue)->delete();

            $this->info("✅ Successfully cleared '{$queue}' queue:");
            $this->line("   - {$pendingCount} pending jobs");
            $this->line("   - {$failedCount} failed jobs");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to clear '{$queue}' queue: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear related cache.
     */
    private function clearRelatedCache(): void
    {
        // Clear SAW results cache
        $sawKeys = $this->getCacheKeysByPattern('saw_results_*');
        foreach ($sawKeys as $key) {
            Cache::forget($key);
        }

        // Clear dashboard cache
        $dashboardKeys = $this->getCacheKeysByPattern('dashboard_*');
        foreach ($dashboardKeys as $key) {
            Cache::forget($key);
        }

        // Clear chart data cache
        $chartKeys = $this->getCacheKeysByPattern('chart_data_*');
        foreach ($chartKeys as $key) {
            Cache::forget($key);
        }

        // Clear evaluation results cache
        $evaluationKeys = $this->getCacheKeysByPattern('evaluation_results_*');
        foreach ($evaluationKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cache keys by pattern.
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        // Note: This is a simplified approach. In production, you might want to use Redis SCAN
        // or implement a more sophisticated cache key management system.

        $keys = [];

        // For now, we'll clear some common patterns manually
        if (str_contains($pattern, 'saw_calculation_status_')) {
            // Clear SAW calculation statuses for common periods
            $periods = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'];
            foreach ($periods as $period) {
                $keys[] = "saw_calculation_status_{$period}";
            }
        }

        if (str_contains($pattern, 'report_generation_status_')) {
            // Clear report generation statuses
            $keys[] = 'report_generation_status_*';
        }

        if (str_contains($pattern, 'notification_status_')) {
            // Clear notification statuses
            $keys[] = 'notification_status_*';
        }

        return $keys;
    }
}
