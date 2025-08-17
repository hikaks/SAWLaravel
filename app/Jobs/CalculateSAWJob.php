<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Services\SAWCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CalculateSAWJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    /**
     * The evaluation period to calculate.
     */
    protected string $evaluationPeriod;

    /**
     * The user ID who initiated the calculation.
     */
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $evaluationPeriod, ?int $userId = null)
    {
        $this->evaluationPeriod = $evaluationPeriod;
        $this->userId = $userId;

        // Set queue priority based on evaluation period
        $this->onQueue($this->getQueuePriority());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            Log::info("Starting SAW calculation job for period: {$this->evaluationPeriod}", [
                'user_id' => $this->userId,
                'job_id' => $this->job->getJobId(),
                'queue' => $this->queue
            ]);

            // Update job status to processing
            $this->updateJobStatus('processing');

            // Validate data completeness
            $this->validateDataCompleteness();

            // Perform SAW calculation
            $sawService = new SAWCalculationService();
            $results = $sawService->calculateSAW($this->evaluationPeriod);

            // Save results to database
            $this->saveResults($results);

            // Clear related caches
            $this->clearRelatedCaches();

            // Update job status to completed
            $this->updateJobStatus('completed');

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info("SAW calculation job completed successfully", [
                'evaluation_period' => $this->evaluationPeriod,
                'duration_seconds' => $duration,
                'total_results' => count($results),
                'user_id' => $this->userId
            ]);

        } catch (\Exception $e) {
            Log::error("SAW calculation job failed", [
                'evaluation_period' => $this->evaluationPeriod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId
            ]);

            // Update job status to failed
            $this->updateJobStatus('failed', $e->getMessage());

            // Re-throw exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SAW calculation job failed permanently", [
            'evaluation_period' => $this->evaluationPeriod,
            'error' => $exception->getMessage(),
            'user_id' => $this->userId
        ]);

        // Update job status to failed permanently
        $this->updateJobStatus('failed_permanently', $exception->getMessage());

        // Clear any temporary data
        $this->clearTemporaryData();
    }

    /**
     * Validate that all required data is present.
     */
    private function validateDataCompleteness(): void
    {
        $employees = Employee::count();
        $criterias = Criteria::count();
        $evaluations = Evaluation::where('evaluation_period', $this->evaluationPeriod)->count();

        $expectedEvaluations = $employees * $criterias;

        if ($evaluations !== $expectedEvaluations) {
            throw new \Exception(
                "Data evaluasi tidak lengkap untuk periode {$this->evaluationPeriod}. " .
                "Dibutuhkan {$expectedEvaluations} evaluasi, ditemukan {$evaluations}"
            );
        }

        // Validate total weight equals 100
        $totalWeight = (float) Criteria::sum('weight');
        if (abs($totalWeight - 100) > 0.01) {
            throw new \Exception(
                "Total bobot kriteria harus 100%, saat ini: {$totalWeight}%"
            );
        }
    }

    /**
     * Save SAW calculation results to database.
     */
    private function saveResults(array $results): void
    {
        DB::transaction(function () use ($results) {
            // Prepare data for upsert
            $data = [];
            foreach ($results as $result) {
                $data[] = [
                    'employee_id' => $result['employee_id'],
                    'total_score' => $result['total_score'],
                    'ranking' => $result['ranking'],
                    'evaluation_period' => $this->evaluationPeriod,
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
        });
    }

    /**
     * Clear related caches after calculation.
     */
    private function clearRelatedCaches(): void
    {
        $cacheKeys = [
            "saw_results_{$this->evaluationPeriod}",
            "dashboard_top_performers",
            "chart_data_performance_{$this->evaluationPeriod}",
            "evaluation_results_{$this->evaluationPeriod}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear temporary data on failure.
     */
    private function clearTemporaryData(): void
    {
        // Clear any temporary caches or data
        Cache::forget("saw_calculation_progress_{$this->evaluationPeriod}");
    }

    /**
     * Update job status in cache for monitoring.
     */
    private function updateJobStatus(string $status, ?string $errorMessage = null): void
    {
        $statusData = [
            'status' => $status,
            'updated_at' => now()->toISOString(),
            'user_id' => $this->userId,
            'evaluation_period' => $this->evaluationPeriod
        ];

        if ($errorMessage) {
            $statusData['error_message'] = $errorMessage;
        }

        Cache::put(
            "saw_calculation_status_{$this->evaluationPeriod}",
            $statusData,
            now()->addHours(24)
        );
    }

    /**
     * Determine queue priority based on evaluation period.
     */
    private function getQueuePriority(): string
    {
        // Current month gets high priority
        if ($this->evaluationPeriod === now()->format('Y-m')) {
            return 'high';
        }

        // Previous month gets default priority
        if ($this->evaluationPeriod === now()->subMonth()->format('Y-m')) {
            return 'default';
        }

        // Historical data gets low priority
        return 'low';
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'saw_calculation',
            "period:{$this->evaluationPeriod}",
            'user:' . ($this->userId ?? 'system')
        ];
    }
}
