<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        // Cache dashboard statistics
        $stats = $this->cacheService->getDashboardData('admin_stats', function() {
            return $this->getAdminStatistics();
        });

        // Get recent users (not cached for real-time data)
        $recent_users = User::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_users'));
    }

    /**
     * Get comprehensive admin statistics.
     */
    private function getAdminStatistics()
    {
        // User statistics
        $total_users = User::count();
        $active_users = User::where('is_active', true)->count();
        $inactive_users = $total_users - $active_users;
        $verified_users = User::whereNotNull('email_verified_at')->count();
        $unverified_users = $total_users - $verified_users;

        // Role distribution
        $admin_users = User::where('role', 'admin')->count();
        $manager_users = User::where('role', 'manager')->count();
        $regular_users = User::where('role', 'user')->count();

        // Email verification statistics
        $verification_rate = $total_users > 0 ? round(($verified_users / $total_users) * 100, 2) : 0;
        $recent_verifications = User::whereNotNull('email_verified_at')
            ->where('email_verified_at', '>=', now()->subDays(7))
            ->count();

        // Employee statistics
        $total_employees = Employee::count();
        $departments_count = Employee::distinct('department')->count();

        // Evaluation statistics
        $total_evaluations = Evaluation::count();
        $evaluation_periods = Evaluation::distinct('evaluation_period')->count();

        // Criteria statistics
        $total_criteria = Criteria::count();
        $criteria_weight_sum = Criteria::sum('weight');

        // System statistics (placeholder values)
        $system_stats = [
            'db_size' => $this->getDatabaseSize(),
            'cache_size' => $this->getCacheSize(),
            'uptime' => $this->getSystemUptime(),
            'last_restart' => 'N/A',
            'todays_logins' => $this->getTodaysLogins(),
            'weekly_new_users' => $this->getWeeklyNewUsers(),
            'active_sessions' => $this->getActiveSessions(),
            'failed_logins' => $this->getFailedLogins(),
        ];

        return array_merge([
            'total_users' => $total_users,
            'active_users' => $active_users,
            'inactive_users' => $inactive_users,
            'verified_users' => $verified_users,
            'unverified_users' => $unverified_users,
            'verification_rate' => $verification_rate,
            'recent_verifications' => $recent_verifications,
            'admin_users' => $admin_users,
            'manager_users' => $manager_users,
            'regular_users' => $regular_users,
            'total_employees' => $total_employees,
            'departments_count' => $departments_count,
            'total_evaluations' => $total_evaluations,
            'evaluation_periods' => $evaluation_periods,
            'total_criteria' => $total_criteria,
            'criteria_weight_sum' => $criteria_weight_sum,
        ], $system_stats);
    }

    /**
     * Get database size (simplified)
     */
    private function getDatabaseSize()
    {
        try {
            $size = DB::select("
                SELECT
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
            ");

            return isset($size[0]->size_mb) ? $size[0]->size_mb . ' MB' : 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get cache size (placeholder)
     */
    private function getCacheSize()
    {
        // This is a placeholder - actual implementation would depend on cache driver
        return '15.2 MB';
    }

    /**
     * Get system uptime (placeholder)
     */
    private function getSystemUptime()
    {
        // This is a placeholder - actual implementation would check server uptime
        return '7 days, 14 hours';
    }

    /**
     * Get today's logins (placeholder)
     */
    private function getTodaysLogins()
    {
        // This would require an activity log table
        return rand(15, 50);
    }

    /**
     * Get weekly new users
     */
    private function getWeeklyNewUsers()
    {
        return User::where('created_at', '>=', now()->subWeek())->count();
    }

    /**
     * Get active sessions (placeholder)
     */
    private function getActiveSessions()
    {
        try {
            return DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get failed logins (placeholder)
     */
    private function getFailedLogins()
    {
        // This would require an activity log table
        return rand(0, 5);
    }

    /**
     * System cache management endpoints
     */
    public function clearCache()
    {
        try {
            $this->cacheService->clearAll();

            return response()->json([
                'success' => true,
                'message' => 'All application caches have been cleared successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    public function warmupCache()
    {
        try {
            $this->cacheService->warmUp();

            return response()->json([
                'success' => true,
                'message' => 'System caches have been warmed up successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to warmup cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function cacheStats()
    {
        try {
            $stats = $this->cacheService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * System health check
     */
    public function healthCheck(Request $request)
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->getMemoryUsage(),
            'overall' => 'healthy'
        ];

        // Determine overall health
        $issues = array_filter($health, fn($status) => $status !== 'healthy' && $status !== true);
        if (!empty($issues)) {
            $health['overall'] = 'warning';
        }

        // Add detailed information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $health,
                'system_info' => $systemInfo,
                'timestamp' => now()->toISOString()
            ]);
        }

        return view('admin.health', compact('health', 'systemInfo'));
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'test', 10);
            $result = cache()->get('health_check');
            cache()->forget('health_check');

            return $result === 'test' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth()
    {
        try {
            $testFile = storage_path('logs/health_check.tmp');
            file_put_contents($testFile, 'test');
            $result = file_get_contents($testFile);
            unlink($testFile);

            return $result === 'test' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Get system information
     */
    public function systemInfo(Request $request)
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];

        // Add extended system information
        $extendedInfo = [
            'php_extensions' => get_loaded_extensions(),
            'server_info' => [
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
                'server_port' => $_SERVER['SERVER_PORT'] ?? 'N/A',
                'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            ],
            'laravel_config' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_connection' => config('queue.default'),
                'mail_driver' => config('mail.default'),
            ],
            'database_info' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
            ],
            'disk_usage' => $this->getDiskUsage(),
            'php_ini' => [
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
                'default_timezone' => ini_get('date.timezone'),
                'error_reporting' => ini_get('error_reporting'),
                'display_errors' => ini_get('display_errors'),
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $systemInfo,
                'extended' => $extendedInfo
            ]);
        }

        return view('admin.system-info', compact('systemInfo', 'extendedInfo'));
    }

    /**
     * Check queue health
     */
    private function checkQueueHealth()
    {
        try {
            // Check if there are failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            if ($failedJobs > 10) {
                return 'warning';
            }
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace()
    {
        try {
            $bytes = disk_free_space('/');
            $gb = round($bytes / 1024 / 1024 / 1024, 2);
            
            if ($gb < 1) {
                return 'error';
            } elseif ($gb < 5) {
                return 'warning';
            }
            
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage()
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        
        return [
            'current' => $this->formatBytes($usage),
            'peak' => $this->formatBytes($peak),
            'limit' => ini_get('memory_limit')
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get disk usage information
     */
    private function getDiskUsage()
    {
        try {
            $totalSpace = disk_total_space('/');
            $freeSpace = disk_free_space('/');
            $usedSpace = $totalSpace - $freeSpace;

            return [
                'total' => $this->formatBytes($totalSpace),
                'used' => $this->formatBytes($usedSpace),
                'free' => $this->formatBytes($freeSpace),
                'usage_percentage' => $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            return [
                'total' => 'N/A',
                'used' => 'N/A',
                'free' => 'N/A',
                'usage_percentage' => 0
            ];
        }
    }

    /**
     * Get database version
     */
    private function getDatabaseVersion()
    {
        try {
            $result = DB::select('SELECT VERSION() as version');
            return $result[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}
