<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Jobs\CalculateSAWJob;
use App\Jobs\GenerateReportJob;
use App\Jobs\SendNotificationJob;

class JobMonitorController extends Controller
{
    /**
     * Display a listing of jobs.
     */
    public function index()
    {
        $stats = $this->getJobStatistics();
        $recentJobs = $this->getRecentJobs();
        $failedJobs = $this->getFailedJobs();
        $queueStatus = $this->getQueueStatus();

        return view('admin.jobs.index', compact('stats', 'recentJobs', 'failedJobs', 'queueStatus'));
    }

    /**
     * Display the specified job.
     */
    public function show($id)
    {
        $job = DB::table('jobs')->find($id);

        if (!$job) {
            return redirect()->route('admin.jobs.index')
                ->with('error', 'Job not found.');
        }

        $jobDetails = $this->parseJobDetails($job);
        $relatedJobs = $this->getRelatedJobs($job);

        return view('admin.jobs.show', compact('job', 'jobDetails', 'relatedJobs'));
    }

    /**
     * Retry a failed job.
     */
    public function retry($id)
    {
        $failedJob = DB::table('failed_jobs')->find($id);

        if (!$failedJob) {
            return redirect()->route('admin.jobs.index')
                ->with('error', 'Failed job not found.');
        }

        try {
            // Use Laravel's built-in retry command
            $result = \Artisan::call('queue:retry', ['id' => [$id]]);

            if ($result === 0) {
                return redirect()->route('admin.jobs.index')
                    ->with('success', 'Job retried successfully.');
            } else {
                return redirect()->route('admin.jobs.index')
                    ->with('error', 'Failed to retry job.');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.jobs.index')
                ->with('error', 'Error retrying job: ' . $e->getMessage());
        }
    }

    /**
     * Clear completed jobs.
     */
    public function clearCompleted()
    {
        try {
            // Clear completed job statuses from cache
            $this->clearCompletedStatuses();

            return redirect()->route('admin.jobs.index')
                ->with('success', 'Completed jobs cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.jobs.index')
                ->with('error', 'Error clearing completed jobs: ' . $e->getMessage());
        }
    }

    /**
     * Clear all jobs.
     */
    public function clearAll()
    {
        try {
            DB::table('jobs')->delete();
            DB::table('failed_jobs')->delete();
            DB::table('job_batches')->delete();

            // Clear related cache
            $this->clearRelatedCache();

            return redirect()->route('admin.jobs.index')
                ->with('success', 'All jobs cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.jobs.index')
                ->with('error', 'Error clearing jobs: ' . $e->getMessage());
        }
    }

    /**
     * Get job statistics.
     */
    private function getJobStatistics()
    {
        $stats = [
            'total_pending' => DB::table('jobs')->count(),
            'total_running' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
            'total_failed' => DB::table('failed_jobs')->count(),
            'total_completed' => $this->getCompletedJobsCount(),
            'queue_distribution' => $this->getQueueDistribution(),
            'job_types' => $this->getJobTypeDistribution(),
            'success_rate' => $this->getSuccessRate(),
        ];

        return $stats;
    }

    /**
     * Get recent jobs.
     */
    private function getRecentJobs()
    {
        return DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                return $this->parseJobDetails($job);
            });
    }

    /**
     * Get failed jobs.
     */
    private function getFailedJobs()
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                return $this->parseJobDetails($job);
            });
    }

    /**
     * Get queue status.
     */
    private function getQueueStatus()
    {
        $queues = ['high', 'default', 'low'];
        $status = [];

        foreach ($queues as $queue) {
            $status[$queue] = [
                'pending' => DB::table('jobs')->where('queue', $queue)->count(),
                'running' => DB::table('jobs')->where('queue', $queue)->whereNotNull('reserved_at')->count(),
                'failed' => DB::table('failed_jobs')->where('queue', $queue)->count(),
            ];
        }

        return $status;
    }

    /**
     * Parse job details.
     */
    private function parseJobDetails($job)
    {
        $payload = json_decode($job->payload);

        $details = [
            'id' => $job->id,
            'queue' => $job->queue ?? 'default',
            'attempts' => $job->attempts ?? 0,
            'created_at' => $job->created_at ? date('Y-m-d H:i:s', $job->created_at) : 'N/A',
            'reserved_at' => $job->reserved_at ? date('Y-m-d H:i:s', $job->reserved_at) : null,
            'failed_at' => $job->failed_at ?? null,
            'job_class' => $payload->displayName ?? 'Unknown',
            'data' => $payload->data ?? [],
            'status' => $this->getJobStatus($job),
        ];

        // Add specific data based on job class
        if (str_contains($details['job_class'], 'CalculateSAWJob')) {
            $details['type'] = 'SAW Calculation';
            $details['period'] = $details['data']['evaluationPeriod'] ?? 'N/A';
        } elseif (str_contains($details['job_class'], 'GenerateReportJob')) {
            $details['type'] = 'Report Generation';
            $details['report_type'] = $details['data']['reportType'] ?? 'N/A';
            $details['period'] = $details['data']['evaluationPeriod'] ?? 'N/A';
        } elseif (str_contains($details['job_class'], 'SendNotificationJob')) {
            $details['type'] = 'Notification';
            $details['notification_type'] = $details['data']['notificationType'] ?? 'N/A';
            $details['recipients_count'] = count($details['data']['recipients'] ?? []);
        }

        return $details;
    }

    /**
     * Get job status.
     */
    private function getJobStatus($job)
    {
        if (isset($job->failed_at)) {
            return 'failed';
        }

        if (isset($job->reserved_at)) {
            return 'running';
        }

        return 'pending';
    }

    /**
     * Get related jobs.
     */
    private function getRelatedJobs($job)
    {
        $payload = json_decode($job->payload);
        $jobClass = $payload->displayName ?? '';

        if (str_contains($jobClass, 'CalculateSAWJob')) {
            $period = $payload->data['evaluationPeriod'] ?? '';
            return DB::table('jobs')
                ->where('id', '!=', $job->id)
                ->where('payload', 'like', '%' . $period . '%')
                ->limit(5)
                ->get();
        }

        return collect();
    }

    /**
     * Get completed jobs count.
     */
    private function getCompletedJobsCount()
    {
        // This would need to be implemented with a proper tracking system
        // For now, we'll estimate based on cache
        $count = 0;

        // Count SAW calculation statuses
        $sawKeys = $this->getCacheKeysByPattern('saw_calculation_status_*');
        foreach ($sawKeys as $key) {
            $status = Cache::get($key);
            if ($status && $status['status'] === 'completed') {
                $count++;
            }
        }

        // Count report generation statuses
        $reportKeys = $this->getCacheKeysByPattern('report_generation_status_*');
        foreach ($reportKeys as $key) {
            $status = Cache::get($key);
            if ($status && $status['status'] === 'completed') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get queue distribution.
     */
    private function getQueueDistribution()
    {
        $distribution = [];

        foreach (['high', 'default', 'low'] as $queue) {
            $distribution[$queue] = [
                'pending' => DB::table('jobs')->where('queue', $queue)->count(),
                'failed' => DB::table('failed_jobs')->where('queue', $queue)->count(),
            ];
        }

        return $distribution;
    }

    /**
     * Get job type distribution.
     */
    private function getJobTypeDistribution()
    {
        $distribution = [];

        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload);
            $jobClass = $payload->displayName ?? 'Unknown';
            $distribution[$jobClass] = ($distribution[$jobClass] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Get success rate.
     */
    private function getSuccessRate()
    {
        $totalJobs = DB::table('jobs')->count() + DB::table('failed_jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        if ($totalJobs === 0) {
            return 100;
        }

        return round((($totalJobs - $failedJobs) / $totalJobs) * 100, 2);
    }

    /**
     * Clear completed statuses.
     */
    private function clearCompletedStatuses()
    {
        $patterns = [
            'saw_calculation_status_*',
            'report_generation_status_*',
            'notification_status_*'
        ];

        foreach ($patterns as $pattern) {
            $keys = $this->getCacheKeysByPattern($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Clear related cache.
     */
    private function clearRelatedCache()
    {
        $patterns = [
            'saw_results_*',
            'dashboard_*',
            'chart_data_*',
            'evaluation_results_*'
        ];

        foreach ($patterns as $pattern) {
            $keys = $this->getCacheKeysByPattern($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Get cache keys by pattern.
     */
    private function getCacheKeysByPattern($pattern)
    {
        // Simplified approach - in production use Redis SCAN
        $keys = [];

        if (str_contains($pattern, 'saw_calculation_status_')) {
            $periods = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'];
            foreach ($periods as $period) {
                $keys[] = "saw_calculation_status_{$period}";
            }
        }

        if (str_contains($pattern, 'report_generation_status_')) {
            $keys[] = 'report_generation_status_*';
        }

        if (str_contains($pattern, 'notification_status_')) {
            $keys[] = 'notification_status_*';
        }

        return $keys;
    }
}
