<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class SendNotificationJob implements ShouldQueue
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
     * The notification type to send.
     */
    protected string $notificationType;

    /**
     * The recipients of the notification.
     */
    protected array $recipients;

    /**
     * The notification data.
     */
    protected array $data;

    /**
     * The user ID who initiated the notification.
     */
    protected ?int $userId;

    /**
     * The evaluation period if applicable.
     */
    protected ?string $evaluationPeriod;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $notificationType,
        array $recipients,
        array $data = [],
        ?int $userId = null,
        ?string $evaluationPeriod = null
    ) {
        $this->notificationType = $notificationType;
        $this->recipients = $recipients;
        $this->data = $data;
        $this->userId = $userId;
        $this->evaluationPeriod = $evaluationPeriod;

        // Set queue priority based on notification type
        $this->onQueue($this->getQueuePriority());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            Log::info("Starting notification job", [
                'notification_type' => $this->notificationType,
                'recipients_count' => count($this->recipients),
                'evaluation_period' => $this->evaluationPeriod,
                'user_id' => $this->userId,
                'job_id' => $this->job->getJobId()
            ]);

            // Update job status to processing
            $this->updateJobStatus('processing');

            // Send notifications based on type
            $this->sendNotifications();

            // Update job status to completed
            $this->updateJobStatus('completed');

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info("Notification job completed successfully", [
                'notification_type' => $this->notificationType,
                'recipients_count' => count($this->recipients),
                'duration_seconds' => $duration,
                'user_id' => $this->userId
            ]);

        } catch (\Exception $e) {
            Log::error("Notification job failed", [
                'notification_type' => $this->notificationType,
                'recipients_count' => count($this->recipients),
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
        Log::error("Notification job failed permanently", [
            'notification_type' => $this->notificationType,
            'recipients_count' => count($this->recipients),
            'error' => $exception->getMessage(),
            'user_id' => $this->userId
        ]);

        // Update job status to failed permanently
        $this->updateJobStatus('failed_permanently', $exception->getMessage());

        // Clear any temporary data
        $this->clearTemporaryData();
    }

    /**
     * Send notifications based on type.
     */
    private function sendNotifications(): void
    {
        switch ($this->notificationType) {
            case 'evaluation_reminder':
                $this->sendEvaluationReminders();
                break;

            case 'results_ready':
                $this->sendResultsReadyNotifications();
                break;

            case 'performance_alert':
                $this->sendPerformanceAlerts();
                break;

            case 'system_announcement':
                $this->sendSystemAnnouncements();
                break;

            case 'custom_notification':
                $this->sendCustomNotifications();
                break;

            default:
                throw new \Exception("Notification type '{$this->notificationType}' not supported");
        }
    }

    /**
     * Send evaluation reminders to employees/managers.
     */
    private function sendEvaluationReminders(): void
    {
        if (!$this->evaluationPeriod) {
            throw new \Exception("Evaluation period is required for evaluation reminders");
        }

        $employees = Employee::whereHas('evaluations', function ($query) {
            $query->where('evaluation_period', $this->evaluationPeriod);
        })->orWhereDoesntHave('evaluations', function ($query) {
            $query->where('evaluation_period', $this->evaluationPeriod);
        })->get();

        foreach ($employees as $employee) {
            $this->sendEvaluationReminderToEmployee($employee);
        }

        // Send reminders to managers
        $managers = User::where('role', 'manager')->where('is_active', true)->get();
        foreach ($managers as $manager) {
            $this->sendEvaluationReminderToManager($manager);
        }
    }

    /**
     * Send evaluation reminder to specific employee.
     */
    private function sendEvaluationReminderToEmployee(Employee $employee): void
    {
        $evaluations = $employee->evaluationsForPeriod($this->evaluationPeriod);
        $completedCount = $evaluations->count();
        $totalCriteria = Criteria::count();

        $data = [
            'employee_name' => $employee->name,
            'evaluation_period' => $this->evaluationPeriod,
            'completed_evaluations' => $completedCount,
            'total_criteria' => $totalCriteria,
            'completion_percentage' => round(($completedCount / $totalCriteria) * 100, 2),
            'remaining_criteria' => $totalCriteria - $completedCount
        ];

        // Send email reminder
        Mail::send('emails.evaluation-reminder', $data, function ($message) use ($employee) {
            $message->to($employee->email)
                    ->subject("Reminder: Evaluation {$this->evaluationPeriod} - {$employee->name}");
        });

        Log::info("Evaluation reminder sent to employee", [
            'employee_id' => $employee->id,
            'employee_email' => $employee->email,
            'evaluation_period' => $this->evaluationPeriod
        ]);
    }

    /**
     * Send evaluation reminder to manager.
     */
    private function sendEvaluationReminderToManager(User $manager): void
    {
        $pendingEmployees = Employee::whereDoesntHave('evaluations', function ($query) {
            $query->where('evaluation_period', $this->evaluationPeriod);
        })->orWhereHas('evaluations', function ($query) {
            $query->where('evaluation_period', $this->evaluationPeriod);
        }, '<', Criteria::count())->get();

        $data = [
            'manager_name' => $manager->name,
            'evaluation_period' => $this->evaluationPeriod,
            'pending_employees' => $pendingEmployees,
            'total_employees' => Employee::count(),
            'pending_count' => $pendingEmployees->count()
        ];

        Mail::send('emails.manager-evaluation-reminder', $data, function ($message) use ($manager) {
            $message->to($manager->email)
                    ->subject("Manager Alert: Evaluation {$this->evaluationPeriod} Status");
        });
    }

    /**
     * Send notifications when SAW results are ready.
     */
    private function sendResultsReadyNotifications(): void
    {
        if (!$this->evaluationPeriod) {
            throw new \Exception("Evaluation period is required for results ready notifications");
        }

        $results = EvaluationResult::with('employee')
            ->where('evaluation_period', $this->evaluationPeriod)
            ->orderBy('ranking')
            ->get();

        // Send to top performers
        $topPerformers = $results->take(5);
        foreach ($topPerformers as $result) {
            $this->sendTopPerformerNotification($result);
        }

        // Send to managers and admins
        $managers = User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->get();
        foreach ($managers as $manager) {
            $this->sendResultsSummaryToManager($manager, $results);
        }
    }

    /**
     * Send top performer notification.
     */
    private function sendTopPerformerNotification(EvaluationResult $result): void
    {
        $data = [
            'employee_name' => $result->employee->name,
            'ranking' => $result->ranking,
            'total_score' => $result->total_score,
            'evaluation_period' => $this->evaluationPeriod,
            'ranking_text' => $result->ranking_text,
            'ranking_category' => $result->ranking_category
        ];

        Mail::send('emails.top-performer', $data, function ($message) use ($result) {
            $message->to($result->employee->email)
                    ->subject("Congratulations! You're in Top {$result->ranking} - {$this->evaluationPeriod}");
        });
    }

    /**
     * Send results summary to manager.
     */
    private function sendResultsSummaryToManager(User $manager, $results): void
    {
        $data = [
            'manager_name' => $manager->name,
            'evaluation_period' => $this->evaluationPeriod,
            'total_employees' => $results->count(),
            'top_performers' => $results->take(10),
            'department_stats' => $this->getDepartmentStats($results),
            'average_score' => round($results->avg('total_score'), 2)
        ];

        Mail::send('emails.results-summary', $data, function ($message) use ($manager) {
            $message->to($manager->email)
                    ->subject("SAW Results Summary - {$this->evaluationPeriod}");
        });
    }

    /**
     * Send performance alerts for low performers.
     */
    private function sendPerformanceAlerts(): void
    {
        if (!$this->evaluationPeriod) {
            throw new \Exception("Evaluation period is required for performance alerts");
        }

        $lowPerformers = EvaluationResult::with('employee')
            ->where('evaluation_period', $this->evaluationPeriod)
            ->where('ranking', '>', 20) // Bottom 20%
            ->get();

        foreach ($lowPerformers as $result) {
            $this->sendPerformanceAlert($result);
        }

        // Send summary to HR/Admin
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            $this->sendPerformanceAlertSummary($admin, $lowPerformers);
        }
    }

    /**
     * Send performance alert to employee.
     */
    private function sendPerformanceAlert(EvaluationResult $result): void
    {
        $data = [
            'employee_name' => $result->employee->name,
            'ranking' => $result->ranking,
            'total_score' => $result->total_score,
            'evaluation_period' => $this->evaluationPeriod,
            'ranking_text' => $result->ranking_text,
            'improvement_suggestions' => $this->getImprovementSuggestions($result)
        ];

        Mail::send('emails.performance-alert', $data, function ($message) use ($result) {
            $message->to($result->employee->email)
                    ->subject("Performance Review - {$this->evaluationPeriod}");
        });
    }

    /**
     * Send performance alert summary to admin.
     */
    private function sendPerformanceAlertSummary(User $admin, $lowPerformers): void
    {
        $data = [
            'admin_name' => $admin->name,
            'evaluation_period' => $this->evaluationPeriod,
            'low_performers' => $lowPerformers,
            'total_low_performers' => $lowPerformers->count(),
            'total_employees' => Employee::count()
        ];

        Mail::send('emails.performance-alert-summary', $data, function ($message) use ($admin) {
            $message->to($admin->email)
                    ->subject("Performance Alert Summary - {$this->evaluationPeriod}");
        });
    }

    /**
     * Send system announcements.
     */
    private function sendSystemAnnouncements(): void
    {
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            $data = array_merge($this->data, [
                'user_name' => $user->name,
                'user_role' => $user->role
            ]);

            Mail::send('emails.system-announcement', $data, function ($message) use ($user) {
                $message->to($user->email)
                        ->subject($this->data['subject'] ?? 'System Announcement');
            });
        }
    }

    /**
     * Send custom notifications.
     */
    private function sendCustomNotifications(): void
    {
        foreach ($this->recipients as $recipient) {
            $data = array_merge($this->data, [
                'recipient_name' => $recipient['name'] ?? 'User',
                'recipient_email' => $recipient['email']
            ]);

            Mail::send('emails.custom-notification', $data, function ($message) use ($recipient) {
                $message->to($recipient['email'])
                        ->subject($this->data['subject'] ?? 'Notification');
            });
        }
    }

    /**
     * Get department statistics for results.
     */
    private function getDepartmentStats($results): array
    {
        return $results->groupBy('employee.department')
            ->map(function ($departmentResults) {
                return [
                    'count' => $departmentResults->count(),
                    'average_score' => round($departmentResults->avg('total_score'), 2),
                    'top_ranking' => $departmentResults->min('ranking')
                ];
            })->toArray();
    }

    /**
     * Get improvement suggestions for low performers.
     */
    private function getImprovementSuggestions(EvaluationResult $result): array
    {
        $suggestions = [];

        if ($result->ranking > 30) {
            $suggestions[] = 'Consider additional training in your area of expertise';
        }

        if ($result->total_score < 0.6) {
            $suggestions[] = 'Focus on improving core competencies';
        }

        $suggestions[] = 'Schedule a meeting with your supervisor for guidance';
        $suggestions[] = 'Review previous evaluation feedback for improvement areas';

        return $suggestions;
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
            'notification_type' => $this->notificationType,
            'recipients_count' => count($this->recipients),
            'evaluation_period' => $this->evaluationPeriod
        ];

        if ($errorMessage) {
            $statusData['error_message'] = $errorMessage;
        }

        Cache::put(
            "notification_status_{$this->userId}_{$this->notificationType}",
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
        Cache::forget("notification_progress_{$this->userId}_{$this->notificationType}");
    }

    /**
     * Determine queue priority based on notification type.
     */
    private function getQueuePriority(): string
    {
        return match ($this->notificationType) {
            'performance_alert', 'results_ready' => 'high',
            'evaluation_reminder' => 'default',
            default => 'low'
        };
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'notification',
            "type:{$this->notificationType}",
            "period:" . ($this->evaluationPeriod ?? 'all'),
            "user:" . ($this->userId ?? 'system'),
            "recipients:" . count($this->recipients)
        ];
    }
}
