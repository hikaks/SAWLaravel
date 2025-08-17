<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SAWCalculationService;
use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessSAWCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $evaluationPeriod,
        private ?int $userId = null
    ) {
        $this->onQueue('saw-calculations');
    }

    /**
     * Execute the job.
     */
    public function handle(SAWCalculationService $sawService, CacheService $cacheService): void
    {
        try {
            Log::info("Starting SAW calculation for period: {$this->evaluationPeriod}");
            
            // Calculate SAW
            $results = $sawService->calculateSAW($this->evaluationPeriod);
            
            // Clear related caches
            $cacheService->invalidateSAWResults($this->evaluationPeriod);
            $cacheService->invalidateChartData();
            $cacheService->invalidateDashboard();
            
            Log::info("SAW calculation completed successfully", [
                'period' => $this->evaluationPeriod,
                'total_results' => count($results)
            ]);
            
            // Notify user if specified
            if ($this->userId) {
                $user = User::find($this->userId);
                if ($user) {
                    // You can create a notification class for this
                    // $user->notify(new SAWCalculationCompleted($this->evaluationPeriod, count($results)));
                }
            }
            
        } catch (\Exception $e) {
            Log::error("SAW calculation failed", [
                'period' => $this->evaluationPeriod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SAW calculation job failed permanently", [
            'period' => $this->evaluationPeriod,
            'error' => $exception->getMessage()
        ]);
        
        // Notify user of failure if specified
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                // $user->notify(new SAWCalculationFailed($this->evaluationPeriod, $exception->getMessage()));
            }
        }
    }
}