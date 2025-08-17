<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Exports\EmployeeExport;
use App\Exports\EvaluationExport;
use App\Exports\ReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600; // 10 minutes

    /**
     * The report type to generate.
     */
    protected string $reportType;

    /**
     * The evaluation period for the report.
     */
    protected ?string $evaluationPeriod;

    /**
     * The user ID who requested the report.
     */
    protected int $userId;

    /**
     * The user email for delivery.
     */
    protected string $userEmail;

    /**
     * Additional parameters for the report.
     */
    protected array $parameters;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $reportType,
        int $userId,
        string $userEmail,
        ?string $evaluationPeriod = null,
        array $parameters = []
    ) {
        $this->reportType = $reportType;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->evaluationPeriod = $evaluationPeriod;
        $this->parameters = $parameters;

        // Set queue priority based on report type
        $this->onQueue($this->getQueuePriority());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            Log::info("Starting report generation job", [
                'report_type' => $this->reportType,
                'evaluation_period' => $this->evaluationPeriod,
                'user_id' => $this->userId,
                'user_email' => $this->userEmail,
                'job_id' => $this->job->getJobId()
            ]);

            // Update job status to processing
            $this->updateJobStatus('processing');

            // Generate the report based on type
            $reportPath = $this->generateReport();

            // Store the report file
            $storedPath = $this->storeReport($reportPath);

            // Send email notification with download link
            $this->sendEmailNotification($storedPath);

            // Update job status to completed
            $this->updateJobStatus('completed', $storedPath);

            // Clean up temporary file
            if (file_exists($reportPath)) {
                unlink($reportPath);
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info("Report generation job completed successfully", [
                'report_type' => $this->reportType,
                'evaluation_period' => $this->evaluationPeriod,
                'duration_seconds' => $duration,
                'file_path' => $storedPath,
                'user_id' => $this->userId
            ]);

        } catch (\Exception $e) {
            Log::error("Report generation job failed", [
                'report_type' => $this->reportType,
                'evaluation_period' => $this->evaluationPeriod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId
            ]);

            // Update job status to failed
            $this->updateJobStatus('failed', null, $e->getMessage());

            // Send failure notification
            $this->sendFailureNotification($e->getMessage());

            // Re-throw exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Report generation job failed permanently", [
            'report_type' => $this->reportType,
            'evaluation_period' => $this->evaluationPeriod,
            'error' => $exception->getMessage(),
            'user_id' => $this->userId
        ]);

        // Update job status to failed permanently
        $this->updateJobStatus('failed_permanently', null, $exception->getMessage());

        // Send permanent failure notification
        $this->sendFailureNotification($exception->getMessage(), true);

        // Clear any temporary data
        $this->clearTemporaryData();
    }

    /**
     * Generate report based on type.
     */
    private function generateReport(): string
    {
        $fileName = $this->generateFileName();
        $tempPath = storage_path("app/temp/{$fileName}");

        // Ensure temp directory exists
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        switch ($this->reportType) {
            case 'employee_list':
                return $this->generateEmployeeList($tempPath);

            case 'evaluation_summary':
                return $this->generateEvaluationSummary($tempPath);

            case 'saw_results':
                return $this->generateSAWResults($tempPath);

            case 'performance_report':
                return $this->generatePerformanceReport($tempPath);

            case 'custom_report':
                return $this->generateCustomReport($tempPath);

            default:
                throw new \Exception("Report type '{$this->reportType}' not supported");
        }
    }

    /**
     * Generate employee list report.
     */
    private function generateEmployeeList(string $tempPath): string
    {
        $employees = Employee::with(['evaluations', 'evaluationResults'])
            ->when($this->evaluationPeriod, function ($query) {
                return $query->whereHas('evaluations', function ($q) {
                    $q->where('evaluation_period', $this->evaluationPeriod);
                });
            })
            ->get();

        return Excel::raw(new EmployeeExport($employees), 'Xlsx');
    }

    /**
     * Generate evaluation summary report.
     */
    private function generateEvaluationSummary(string $tempPath): string
    {
        if (!$this->evaluationPeriod) {
            throw new \Exception("Evaluation period is required for evaluation summary report");
        }

        $evaluations = Evaluation::with(['employee', 'criteria'])
            ->where('evaluation_period', $this->evaluationPeriod)
            ->get();

        return Excel::raw(new EvaluationExport($evaluations), 'Xlsx');
    }

    /**
     * Generate SAW results report.
     */
    private function generateSAWResults(string $tempPath): string
    {
        if (!$this->evaluationPeriod) {
            throw new \Exception("Evaluation period is required for SAW results report");
        }

        $results = EvaluationResult::with(['employee'])
            ->where('evaluation_period', $this->evaluationPeriod)
            ->orderBy('ranking')
            ->get();

        $export = new ReportExport($results, 'saw_results', $this->evaluationPeriod);
        return $export->exportToCsv();
    }

    /**
     * Generate performance report.
     */
    private function generatePerformanceReport(string $tempPath): string
    {
        $periods = $this->evaluationPeriod
            ? [$this->evaluationPeriod]
            : EvaluationResult::distinct('evaluation_period')->pluck('evaluation_period')->toArray();

        $performanceData = [];
        foreach ($periods as $period) {
            $results = EvaluationResult::with(['employee'])
                ->where('evaluation_period', $period)
                ->orderBy('ranking')
                ->get();

            $performanceData[$period] = $results;
        }

        $export = new ReportExport($performanceData, 'performance', $this->evaluationPeriod);
        return $export->exportToCsv();
    }

    /**
     * Generate custom report based on parameters.
     */
    private function generateCustomReport(string $tempPath): string
    {
        // Custom report logic based on parameters
        $query = Employee::query();

        if (isset($this->parameters['department'])) {
            $query->where('department', $this->parameters['department']);
        }

        if (isset($this->parameters['position'])) {
            $query->where('position', $this->parameters['position']);
        }

        $employees = $query->with(['evaluations', 'evaluationResults'])->get();

        $export = new ReportExport($employees, 'custom', $this->evaluationPeriod);
        return $export->exportToCsv();
    }

    /**
     * Store the generated report.
     */
    private function storeReport(string $reportContent): string
    {
        $fileName = $this->generateFileName();
        $directory = "reports/{$this->reportType}";
        $fullPath = "{$directory}/{$fileName}";

        // Store the report
        Storage::put($fullPath, $reportContent);

        // Set file to expire in 7 days
        $this->scheduleFileCleanup($fullPath);

        return $fullPath;
    }

    /**
     * Generate filename for the report.
     */
    private function generateFileName(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $period = $this->evaluationPeriod ? "_{$this->evaluationPeriod}" : '';

        return "{$this->reportType}{$period}_{$timestamp}.csv";
    }

    /**
     * Send email notification with download link.
     */
    private function sendEmailNotification(string $filePath): void
    {
        $downloadUrl = route('reports.download', [
            'file' => base64_encode($filePath),
            'token' => $this->generateDownloadToken($filePath)
        ]);

        $data = [
            'report_type' => ucfirst(str_replace('_', ' ', $this->reportType)),
            'evaluation_period' => $this->evaluationPeriod,
            'download_url' => $downloadUrl,
            'expires_at' => now()->addDays(7)->format('d M Y H:i')
        ];

        // Send email (you can customize this based on your mail setup)
        Mail::send('emails.report-ready', $data, function ($message) {
            $message->to($this->userEmail)
                    ->subject("Report {$this->reportType} Ready for Download");
        });
    }

    /**
     * Send failure notification.
     */
    private function sendFailureNotification(string $errorMessage, bool $permanent = false): void
    {
        $data = [
            'report_type' => ucfirst(str_replace('_', ' ', $this->reportType)),
            'evaluation_period' => $this->evaluationPeriod,
            'error_message' => $errorMessage,
            'permanent' => $permanent
        ];

        Mail::send('emails.report-failed', $data, function ($message) {
            $message->to($this->userEmail)
                    ->subject("Report Generation Failed - {$this->reportType}");
        });
    }

    /**
     * Generate download token for security.
     */
    private function generateDownloadToken(string $filePath): string
    {
        $token = hash('sha256', $filePath . $this->userId . config('app.key'));

        // Store token in cache for validation
        Cache::put("download_token_{$token}", $filePath, now()->addDays(7));

        return $token;
    }

    /**
     * Schedule file cleanup.
     */
    private function scheduleFileCleanup(string $filePath): void
    {
        // For now, we'll just log the cleanup task
        // In production, you might want to use a proper job class or cron job
        Log::info("File cleanup scheduled for: {$filePath}", [
            'file_path' => $filePath,
            'cleanup_time' => now()->addDays(7)->toISOString()
        ]);

        // TODO: Implement proper file cleanup scheduling
        // This could be done with a dedicated cleanup job or cron task
    }

    /**
     * Update job status in cache for monitoring.
     */
    private function updateJobStatus(string $status, ?string $filePath = null, ?string $errorMessage = null): void
    {
        $statusData = [
            'status' => $status,
            'updated_at' => now()->toISOString(),
            'user_id' => $this->userId,
            'report_type' => $this->reportType,
            'evaluation_period' => $this->evaluationPeriod
        ];

        if ($filePath) {
            $statusData['file_path'] = $filePath;
        }

        if ($errorMessage) {
            $statusData['error_message'] = $errorMessage;
        }

        Cache::put(
            "report_generation_status_{$this->userId}_{$this->reportType}",
            $statusData,
            now()->addHours(24)
        );
    }

    /**
     * Clear temporary data on failure.
     */
    private function clearTemporaryData(): void
    {
        // Clear any temporary caches or data
        Cache::forget("report_generation_progress_{$this->userId}_{$this->reportType}");
    }

    /**
     * Determine queue priority based on report type.
     */
    private function getQueuePriority(): string
    {
        return match ($this->reportType) {
            'saw_results', 'performance_report' => 'high',
            'evaluation_summary' => 'default',
            default => 'low'
        };
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'report_generation',
            "type:{$this->reportType}",
            "period:" . ($this->evaluationPeriod ?? 'all'),
            "user:{$this->userId}"
        ];
    }
}
