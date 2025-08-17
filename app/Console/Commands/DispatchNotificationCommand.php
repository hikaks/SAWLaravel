<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Models\Employee;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Log;

class DispatchNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:dispatch
                           {type : Notification type (evaluation_reminder, results_ready, performance_alert, system_announcement, custom_notification)}
                           {--period= : Evaluation period (e.g., 2024-01)}
                           {--user-id= : User ID who initiated the notification}
                           {--recipients= : Comma-separated list of recipient emails}
                           {--subject= : Subject for custom notifications}
                           {--message= : Message content for custom notifications}
                           {--all-users : Send to all active users}
                           {--all-employees : Send to all employees}
                           {--managers-only : Send to managers only}
                           {--admins-only : Send to admins only}
                           {--dry-run : Show what would be dispatched without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch notification jobs to the queue for background processing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notificationType = $this->argument('type');

        if (!$this->validateNotificationType($notificationType)) {
            $this->error("❌ Invalid notification type. Available types: evaluation_reminder, results_ready, performance_alert, system_announcement, custom_notification");
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            return $this->showDryRunInfo($notificationType);
        }

        try {
            $recipients = $this->getRecipients($notificationType);
            $data = $this->getNotificationData($notificationType);
            $userId = $this->option('user-id') ?? 1;
            $period = $this->option('period');

            if (empty($recipients)) {
                $this->error("❌ No recipients found for notification type '{$notificationType}'");
                return Command::FAILURE;
            }

            $this->info("🚀 Dispatching {$notificationType} notification to " . count($recipients) . " recipients");

            // Dispatch the job
            SendNotificationJob::dispatch(
                $notificationType,
                $recipients,
                $data,
                $userId,
                $period
            );

            $this->info("✅ Notification job dispatched successfully!");
            $this->line("   Type: {$notificationType}");
            $this->line("   Recipients: " . count($recipients));
            $this->line("   Period: " . ($period ?? 'N/A'));
            $this->line("   Queue Priority: " . $this->getQueuePriority($notificationType));
            $this->line("   Check status with: php artisan queue:status");

            // Log the dispatch
            Log::info("Notification job dispatched via command", [
                'notification_type' => $notificationType,
                'recipients_count' => count($recipients),
                'period' => $period,
                'user_id' => $userId,
                'command' => 'notification:dispatch'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to dispatch notification job: " . $e->getMessage());
            Log::error("Failed to dispatch notification job", [
                'notification_type' => $notificationType,
                'error' => $e->getMessage(),
                'command' => 'notification:dispatch'
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Validate notification type.
     */
    private function validateNotificationType(string $type): bool
    {
        $validTypes = [
            'evaluation_reminder',
            'results_ready',
            'performance_alert',
            'system_announcement',
            'custom_notification'
        ];

        return in_array($type, $validTypes);
    }

    /**
     * Get recipients based on notification type and options.
     */
    private function getRecipients(string $notificationType): array
    {
        // Handle specific recipients option
        if ($recipients = $this->option('recipients')) {
            $emails = explode(',', $recipients);
            $recipients = [];

            foreach ($emails as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = ['email' => $email, 'name' => 'User'];
                }
            }

            return $recipients;
        }

        // Handle role-based options
        if ($this->option('admins-only')) {
            return $this->getUsersByRole('admin');
        }

        if ($this->option('managers-only')) {
            return $this->getUsersByRole('manager');
        }

        if ($this->option('all-users')) {
            return $this->getAllActiveUsers();
        }

        if ($this->option('all-employees')) {
            return $this->getAllEmployees();
        }

        // Default recipients based on notification type
        return $this->getDefaultRecipients($notificationType);
    }

    /**
     * Get default recipients for notification type.
     */
    private function getDefaultRecipients(string $notificationType): array
    {
        switch ($notificationType) {
            case 'evaluation_reminder':
                return $this->getEvaluationReminderRecipients();

            case 'results_ready':
                return $this->getResultsReadyRecipients();

            case 'performance_alert':
                return $this->getPerformanceAlertRecipients();

            case 'system_announcement':
                return $this->getAllActiveUsers();

            case 'custom_notification':
                return $this->getAllActiveUsers();

            default:
                return [];
        }
    }

    /**
     * Get recipients for evaluation reminders.
     */
    private function getEvaluationReminderRecipients(): array
    {
        $recipients = [];

        // Add employees
        $employees = Employee::all();
        foreach ($employees as $employee) {
            $recipients[] = [
                'email' => $employee->email,
                'name' => $employee->name
            ];
        }

        // Add managers
        $managers = User::where('role', 'manager')->where('is_active', true)->get();
        foreach ($managers as $manager) {
            $recipients[] = [
                'email' => $manager->email,
                'name' => $manager->name
            ];
        }

        return $recipients;
    }

    /**
     * Get recipients for results ready notifications.
     */
    private function getResultsReadyRecipients(): array
    {
        $recipients = [];

        // Add top performers
        $topPerformers = \App\Models\EvaluationResult::with('employee')
            ->orderBy('ranking')
            ->limit(10)
            ->get();

        foreach ($topPerformers as $result) {
            $recipients[] = [
                'email' => $result->employee->email,
                'name' => $result->employee->name
            ];
        }

        // Add managers and admins
        $managers = User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->get();
        foreach ($managers as $manager) {
            $recipients[] = [
                'email' => $manager->email,
                'name' => $manager->name
            ];
        }

        return $recipients;
    }

    /**
     * Get recipients for performance alerts.
     */
    private function getPerformanceAlertRecipients(): array
    {
        $recipients = [];

        // Add low performers
        $lowPerformers = \App\Models\EvaluationResult::with('employee')
            ->where('ranking', '>', 20) // Bottom 20%
            ->get();

        foreach ($lowPerformers as $result) {
            $recipients[] = [
                'email' => $result->employee->email,
                'name' => $result->employee->name
            ];
        }

        // Add admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            $recipients[] = [
                'email' => $admin->email,
                'name' => $admin->name
            ];
        }

        return $recipients;
    }

    /**
     * Get users by role.
     */
    private function getUsersByRole(string $role): array
    {
        $users = User::where('role', $role)->where('is_active', true)->get();
        $recipients = [];

        foreach ($users as $user) {
            $recipients[] = [
                'email' => $user->email,
                'name' => $user->name
            ];
        }

        return $recipients;
    }

    /**
     * Get all active users.
     */
    private function getAllActiveUsers(): array
    {
        $users = User::where('is_active', true)->get();
        $recipients = [];

        foreach ($users as $user) {
            $recipients[] = [
                'email' => $user->email,
                'name' => $user->name
            ];
        }

        return $recipients;
    }

    /**
     * Get all employees.
     */
    private function getAllEmployees(): array
    {
        $employees = Employee::all();
        $recipients = [];

        foreach ($employees as $employee) {
            $recipients[] = [
                'email' => $employee->email,
                'name' => $employee->name
            ];
        }

        return $recipients;
    }

    /**
     * Get notification data.
     */
    private function getNotificationData(string $notificationType): array
    {
        $data = [];

        if ($notificationType === 'custom_notification') {
            $data['subject'] = $this->option('subject') ?? 'System Notification';
            $data['message'] = $this->option('message') ?? 'This is a system notification.';
        }

        if ($notificationType === 'system_announcement') {
            $data['subject'] = $this->option('subject') ?? 'System Announcement';
            $data['message'] = $this->option('message') ?? 'This is a system announcement.';
        }

        return $data;
    }

    /**
     * Get queue priority for notification type.
     */
    private function getQueuePriority(string $notificationType): string
    {
        return match ($notificationType) {
            'performance_alert', 'results_ready' => 'high',
            'evaluation_reminder' => 'default',
            default => 'low'
        };
    }

    /**
     * Show dry run information.
     */
    private function showDryRunInfo(string $notificationType): int
    {
        $this->info("🔍 DRY RUN - What would be dispatched:");
        $this->line("   Notification Type: {$notificationType}");
        $this->line("   Period: " . ($this->option('period') ?? 'N/A'));
        $this->line("   User ID: " . ($this->option('user-id') ?? 'System (1)'));
        $this->line("   Queue Priority: " . $this->getQueuePriority($notificationType));

        $recipients = $this->getRecipients($notificationType);
        $this->line("   Recipients: " . count($recipients));

        if (count($recipients) <= 10) {
            foreach ($recipients as $recipient) {
                $this->line("     - {$recipient['email']} ({$recipient['name']})");
            }
        } else {
            $this->line("     - First 10 recipients:");
            for ($i = 0; $i < 10; $i++) {
                $this->line("       {$recipients[$i]['email']} ({$recipients[$i]['name']})");
            }
            $this->line("     - ... and " . (count($recipients) - 10) . " more");
        }

        $data = $this->getNotificationData($notificationType);
        if (!empty($data)) {
            $this->line("   Data: " . json_encode($data));
        }

        return Command::SUCCESS;
    }
}
