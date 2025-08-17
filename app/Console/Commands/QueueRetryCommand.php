<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class QueueRetryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:retry
                           {--id= : Retry specific failed job by ID}
                           {--queue= : Retry failed jobs from specific queue}
                           {--all : Retry all failed jobs}
                           {--force : Force retry without confirmation}
                           {--max-attempts=3 : Maximum number of retry attempts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed jobs with various options';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($id = $this->option('id')) {
            return $this->retrySpecificJob($id);
        }

        if ($queue = $this->option('queue')) {
            return $this->retryJobsByQueue($queue);
        }

        if ($this->option('all')) {
            return $this->retryAllFailedJobs();
        }

        // Default: show failed jobs and ask which to retry
        return $this->interactiveRetry();
    }

    /**
     * Retry specific failed job by ID.
     */
    private function retrySpecificJob(string $id): int
    {
        $failedJob = DB::table('failed_jobs')->find($id);

        if (!$failedJob) {
            $this->error("❌ Failed job with ID {$id} not found.");
            return Command::FAILURE;
        }

        $this->info("🔄 Retrying failed job ID: {$id}");
        $this->line("Job Class: " . ($this->getJobClass($failedJob->payload) ?? 'Unknown'));
        $this->line("Queue: {$failedJob->queue}");
        $this->line("Failed At: {$failedJob->failed_at}");
        $this->line("Exception: " . substr($failedJob->exception, 0, 100) . '...');

        if (!$this->option('force') && !$this->confirm("Are you sure you want to retry this job?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            // Use Laravel's built-in retry command
            $result = Artisan::call('queue:retry', ['id' => [$id]]);

            if ($result === 0) {
                $this->info("✅ Successfully retried job ID: {$id}");
                return Command::SUCCESS;
            } else {
                $this->error("❌ Failed to retry job ID: {$id}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error retrying job: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Retry failed jobs by queue.
     */
    private function retryJobsByQueue(string $queue): int
    {
        if (!in_array($queue, ['high', 'default', 'low'])) {
            $this->error("❌ Invalid queue name. Must be one of: high, default, low");
            return Command::FAILURE;
        }

        $failedJobs = DB::table('failed_jobs')
            ->where('queue', $queue)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info("✅ No failed jobs found in '{$queue}' queue.");
            return Command::SUCCESS;
        }

        $this->info("🔄 Found " . $failedJobs->count() . " failed jobs in '{$queue}' queue");

        if (!$this->option('force') && !$this->confirm("Are you sure you want to retry all failed jobs in '{$queue}' queue?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($failedJobs as $job) {
            try {
                $result = Artisan::call('queue:retry', ['id' => [$job->id]]);

                if ($result === 0) {
                    $successCount++;
                    $this->line("✅ Retried job ID: {$job->id}");
                } else {
                    $failCount++;
                    $this->line("❌ Failed to retry job ID: {$job->id}");
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->line("❌ Error retrying job ID {$job->id}: " . $e->getMessage());
            }
        }

        $this->line('');
        $this->info("📊 Retry Results:");
        $this->line("   ✅ Successfully retried: {$successCount}");
        $this->line("   ❌ Failed to retry: {$failCount}");

        return $failCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Retry all failed jobs.
     */
    private function retryAllFailedJobs(): int
    {
        $failedCount = DB::table('failed_jobs')->count();

        if ($failedCount === 0) {
            $this->info('✅ No failed jobs to retry.');
            return Command::SUCCESS;
        }

        $this->info("🔄 Found {$failedCount} failed jobs to retry");

        if (!$this->option('force') && !$this->confirm("Are you sure you want to retry ALL {$failedCount} failed jobs?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            // Use Laravel's built-in retry command for all failed jobs
            $result = Artisan::call('queue:retry', ['id' => 'all']);

            if ($result === 0) {
                $this->info("✅ Successfully retried all {$failedCount} failed jobs");
                return Command::SUCCESS;
            } else {
                $this->error("❌ Failed to retry all failed jobs");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error retrying all failed jobs: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Interactive retry mode.
     */
    private function interactiveRetry(): int
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info('✅ No failed jobs found.');
            return Command::SUCCESS;
        }

        $this->info('❌ FAILED JOBS - Select which ones to retry:');
        $this->line('');

        $choices = [];
        foreach ($failedJobs as $index => $job) {
            $jobClass = $this->getJobClass($job->payload) ?? 'Unknown';
            $choices[$index + 1] = "ID: {$job->id} | {$jobClass} | Queue: {$job->queue} | Failed: {$job->failed_at}";
        }

        $choices['all'] = 'Retry ALL failed jobs';
        $choices['none'] = 'Cancel';

        $selected = $this->choice(
            'Select jobs to retry:',
            $choices
        );

        if ($selected === 'none') {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        if ($selected === 'all') {
            return $this->retryAllFailedJobs();
        }

        // Retry specific selected job
        $jobIndex = array_search($selected, $choices) - 1;
        $job = $failedJobs[$jobIndex];

        return $this->retrySpecificJob($job->id);
    }

    /**
     * Get job class name from payload.
     */
    private function getJobClass(string $payload): ?string
    {
        try {
            $decoded = json_decode($payload);
            return $decoded->displayName ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Show failed jobs summary.
     */
    private function showFailedJobsSummary(): void
    {
        $totalFailed = DB::table('failed_jobs')->count();
        $failedByQueue = DB::table('failed_jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->get();

        $this->info("📊 Failed Jobs Summary:");
        $this->line("   Total Failed: {$totalFailed}");

        foreach ($failedByQueue as $queue) {
            $this->line("   {$queue->queue}: {$queue->count}");
        }
    }
}
