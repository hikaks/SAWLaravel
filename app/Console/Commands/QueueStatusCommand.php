<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CalculateSAWJob;
use App\Jobs\GenerateReportJob;
use App\Jobs\SendNotificationJob;

class QueueStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status
                           {--detailed : Show detailed information about each queue}
                           {--failed : Show only failed jobs}
                           {--monitor : Continuous monitoring mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status, job counts, and failed jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('monitor')) {
            return $this->monitorMode();
        }

        if ($this->option('failed')) {
            return $this->showFailedJobs();
        }

        $this->showQueueOverview();

        if ($this->option('detailed')) {
            $this->showDetailedStatus();
        }

        return Command::SUCCESS;
    }

    /**
     * Show queue overview.
     */
    private function showQueueOverview(): void
    {
        $this->info('🚀 QUEUE STATUS OVERVIEW');
        $this->line('');

        // Get queue statistics
        $stats = $this->getQueueStatistics();

        $this->table(
            ['Queue', 'Pending', 'Running', 'Completed', 'Failed'],
            [
                ['high', $stats['high']['pending'], $stats['high']['running'], $stats['high']['completed'], $stats['high']['failed']],
                ['default', $stats['default']['pending'], $stats['default']['running'], $stats['default']['completed'], $stats['default']['failed']],
                ['low', $stats['low']['pending'], $stats['low']['running'], $stats['low']['completed'], $stats['low']['failed']],
            ]
        );

        $this->line('');
        $this->info("📊 Total Jobs: {$stats['total']['pending']} pending, {$stats['total']['running']} running, {$stats['total']['completed']} completed, {$stats['total']['failed']} failed");
    }

    /**
     * Show detailed queue status.
     */
    private function showDetailedStatus(): void
    {
        $this->line('');
        $this->info('🔍 DETAILED QUEUE STATUS');
        $this->line('');

        // Show pending jobs
        $this->showPendingJobs();

        // Show recent completed jobs
        $this->showRecentCompletedJobs();

        // Show job type distribution
        $this->showJobTypeDistribution();

        // Show performance metrics
        $this->showPerformanceMetrics();
    }

    /**
     * Show failed jobs.
     */
    private function showFailedJobs(): int
    {
        $this->info('❌ FAILED JOBS');
        $this->line('');

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info('✅ No failed jobs found.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($failedJobs as $job) {
            $payload = json_decode($job->payload);
            $jobClass = $payload->displayName ?? 'Unknown';

            $rows[] = [
                $job->id,
                $jobClass,
                $job->queue,
                $job->failed_at,
                substr($job->exception, 0, 100) . '...'
            ];
        }

        $this->table(
            ['ID', 'Job Class', 'Queue', 'Failed At', 'Exception'],
            $rows
        );

        $this->line('');
        $this->warn("💡 Use 'php artisan queue:retry' to retry failed jobs");

        return Command::SUCCESS;
    }

    /**
     * Show pending jobs.
     */
    private function showPendingJobs(): void
    {
        $this->info('⏳ PENDING JOBS');

        $pendingJobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($pendingJobs->isEmpty()) {
            $this->line('   No pending jobs.');
            return;
        }

        $rows = [];
        foreach ($pendingJobs as $job) {
            $payload = json_decode($job->payload);
            $jobClass = $payload->displayName ?? 'Unknown';
            $createdAt = date('Y-m-d H:i:s', $job->created_at);

            $rows[] = [
                $job->id,
                $jobClass,
                $job->queue,
                $createdAt,
                $job->attempts
            ];
        }

        $this->table(
            ['ID', 'Job Class', 'Queue', 'Created At', 'Attempts'],
            $rows
        );
    }

    /**
     * Show recent completed jobs.
     */
    private function showRecentCompletedJobs(): void
    {
        $this->line('');
        $this->info('✅ RECENT COMPLETED JOBS');

        // Get from cache (jobs that have completed)
        $completedJobs = $this->getCompletedJobsFromCache();

        if (empty($completedJobs)) {
            $this->line('   No completed jobs found in cache.');
            return;
        }

        $rows = [];
        foreach (array_slice($completedJobs, 0, 10) as $job) {
            $rows[] = [
                $job['type'] ?? 'Unknown',
                $job['period'] ?? 'N/A',
                $job['user_id'] ?? 'System',
                $job['completed_at'] ?? 'N/A',
                $job['duration'] ?? 'N/A'
            ];
        }

        $this->table(
            ['Job Type', 'Period', 'User ID', 'Completed At', 'Duration'],
            $rows
        );
    }

    /**
     * Show job type distribution.
     */
    private function showJobTypeDistribution(): void
    {
        $this->line('');
        $this->info('📊 JOB TYPE DISTRIBUTION');

        $distribution = $this->getJobTypeDistribution();

        $rows = [];
        foreach ($distribution as $type => $count) {
            $rows[] = [$type, $count];
        }

        $this->table(
            ['Job Type', 'Count'],
            $rows
        );
    }

    /**
     * Show performance metrics.
     */
    private function showPerformanceMetrics(): void
    {
        $this->line('');
        $this->info('⚡ PERFORMANCE METRICS');

        $metrics = $this->getPerformanceMetrics();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Job Duration', $metrics['avg_duration'] . 's'],
                ['Jobs per Minute', $metrics['jobs_per_minute']],
                ['Success Rate', $metrics['success_rate'] . '%'],
                ['Queue Depth', $metrics['queue_depth']],
                ['Worker Status', $metrics['worker_status']]
            ]
        );
    }

    /**
     * Continuous monitoring mode.
     */
    private function monitorMode(): int
    {
        $this->info('🔄 QUEUE MONITORING MODE (Press Ctrl+C to stop)');
        $this->line('');

        $startTime = time();
        $lastStats = null;

        while (true) {
            $currentStats = $this->getQueueStatistics();

            // Only show if stats changed
            if ($lastStats !== $currentStats) {
                $this->output->write("\033[2J\033[H"); // Clear screen

                $this->info('🔄 QUEUE MONITORING - ' . date('Y-m-d H:i:s'));
                $this->line('');

                $this->table(
                    ['Queue', 'Pending', 'Running', 'Completed', 'Failed'],
                    [
                        ['high', $currentStats['high']['pending'], $currentStats['high']['running'], $currentStats['high']['completed'], $currentStats['high']['failed']],
                        ['default', $currentStats['default']['pending'], $currentStats['default']['running'], $currentStats['default']['completed'], $currentStats['default']['failed']],
                        ['low', $currentStats['low']['pending'], $currentStats['low']['running'], $currentStats['low']['completed'], $currentStats['low']['failed']],
                    ]
                );

                $this->line('');
                $this->info("📊 Total: {$currentStats['total']['pending']} pending, {$currentStats['total']['running']} running, {$currentStats['total']['completed']} completed, {$currentStats['total']['failed']} failed");
                $this->line("⏱️  Uptime: " . $this->formatUptime(time() - $startTime));

                $lastStats = $currentStats;
            }

            sleep(2); // Update every 2 seconds
        }
    }

    /**
     * Get queue statistics.
     */
    private function getQueueStatistics(): array
    {
        $stats = [
            'high' => ['pending' => 0, 'running' => 0, 'completed' => 0, 'failed' => 0],
            'default' => ['pending' => 0, 'running' => 0, 'completed' => 0, 'failed' => 0],
            'low' => ['pending' => 0, 'running' => 0, 'completed' => 0, 'failed' => 0],
            'total' => ['pending' => 0, 'running' => 0, 'completed' => 0, 'failed' => 0]
        ];

        // Get pending jobs by queue
        foreach (['high', 'default', 'low'] as $queue) {
            $stats[$queue]['pending'] = DB::table('jobs')
                ->where('queue', $queue)
                ->count();

            $stats[$queue]['running'] = DB::table('jobs')
                ->where('queue', $queue)
                ->whereNotNull('reserved_at')
                ->count();
        }

        // Get failed jobs by queue
        foreach (['high', 'default', 'low'] as $queue) {
            $stats[$queue]['failed'] = DB::table('failed_jobs')
                ->where('queue', $queue)
                ->count();
        }

        // Calculate totals
        foreach (['pending', 'running', 'completed', 'failed'] as $status) {
            $stats['total'][$status] = array_sum(array_column($stats, $status));
        }

        return $stats;
    }

    /**
     * Get completed jobs from cache.
     */
    private function getCompletedJobsFromCache(): array
    {
        $completedJobs = [];

        // Check for SAW calculation status
        $sawStatuses = Cache::get('saw_calculation_status_*');
        if ($sawStatuses) {
            foreach ($sawStatuses as $key => $status) {
                if ($status['status'] === 'completed') {
                    $completedJobs[] = [
                        'type' => 'SAW Calculation',
                        'period' => $status['evaluation_period'] ?? 'N/A',
                        'user_id' => $status['user_id'] ?? 'System',
                        'completed_at' => $status['updated_at'] ?? 'N/A',
                        'duration' => 'N/A'
                    ];
                }
            }
        }

        // Check for report generation status
        $reportStatuses = Cache::get('report_generation_status_*');
        if ($reportStatuses) {
            foreach ($reportStatuses as $key => $status) {
                if ($status['status'] === 'completed') {
                    $completedJobs[] = [
                        'type' => 'Report Generation',
                        'period' => $status['evaluation_period'] ?? 'N/A',
                        'user_id' => $status['user_id'] ?? 'System',
                        'completed_at' => $status['updated_at'] ?? 'N/A',
                        'duration' => 'N/A'
                    ];
                }
            }
        }

        return $completedJobs;
    }

    /**
     * Get job type distribution.
     */
    private function getJobTypeDistribution(): array
    {
        $distribution = [];

        // Count by job class in pending jobs
        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload);
            $jobClass = class_basename($payload->displayName ?? 'Unknown');
            $distribution[$jobClass] = ($distribution[$jobClass] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Get performance metrics.
     */
    private function getPerformanceMetrics(): array
    {
        $totalJobs = DB::table('jobs')->count() + DB::table('failed_jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $successRate = $totalJobs > 0 ? round((($totalJobs - $failedJobs) / $totalJobs) * 100, 2) : 100;

        return [
            'avg_duration' => 'N/A', // Would need to implement duration tracking
            'jobs_per_minute' => 'N/A', // Would need to implement rate tracking
            'success_rate' => $successRate,
            'queue_depth' => DB::table('jobs')->count(),
            'worker_status' => $this->getWorkerStatus()
        ];
    }

    /**
     * Get worker status.
     */
    private function getWorkerStatus(): string
    {
        // Check if any jobs are being processed
        $runningJobs = DB::table('jobs')->whereNotNull('reserved_at')->count();

        if ($runningJobs > 0) {
            return '🟢 Active';
        }

        $pendingJobs = DB::table('jobs')->count();
        if ($pendingJobs > 0) {
            return '🟡 Idle (Jobs Pending)';
        }

        return '🔴 Inactive';
    }

    /**
     * Format uptime.
     */
    private function formatUptime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
