<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache duration constants (in seconds)
     */
    const CACHE_DURATION_SHORT = 300;      // 5 minutes
    const CACHE_DURATION_MEDIUM = 1800;    // 30 minutes
    const CACHE_DURATION_LONG = 3600;      // 1 hour
    const CACHE_DURATION_DAILY = 86400;    // 24 hours

    /**
     * Cache key prefixes
     */
    const PREFIX_SAW_RESULTS = 'saw_results';
    const PREFIX_DASHBOARD = 'dashboard';
    const PREFIX_CHART_DATA = 'chart_data';
    const PREFIX_NAVIGATION = 'navigation';
    const PREFIX_EMPLOYEE_DATA = 'employee_data';
    const PREFIX_CRITERIA_DATA = 'criteria_data';
    const PREFIX_EVALUATION_DATA = 'evaluation_data';
    const PREFIX_PERIODS = 'periods';

    /**
     * Get or set cached SAW calculation results
     */
    public function getSAWResults(string $period, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_SAW_RESULTS, $period);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_MEDIUM, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached dashboard data
     */
    public function getDashboardData(string $type, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_DASHBOARD, $type);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_SHORT, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached chart data
     */
    public function getChartData(string $type, string $period = 'all', callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_CHART_DATA, $type, $period);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_MEDIUM, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached navigation data
     */
    public function getNavigationData(string $type, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_NAVIGATION, $type);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_LONG, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached employee data
     */
    public function getEmployeeData(string $identifier, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_EMPLOYEE_DATA, $identifier);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_MEDIUM, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached criteria data
     */
    public function getCriteriaData(string $identifier, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_CRITERIA_DATA, $identifier);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_LONG, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached evaluation periods
     */
    public function getEvaluationPeriods(callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_PERIODS, 'all');

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_MEDIUM, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Get or set cached evaluation data
     */
    public function getEvaluationData(string $identifier, callable $callback = null): mixed
    {
        $key = $this->generateKey(self::PREFIX_EVALUATION_DATA, $identifier);

        if ($callback) {
            return Cache::remember($key, self::CACHE_DURATION_SHORT, $callback);
        }

        return Cache::get($key);
    }

    /**
     * Invalidate SAW results cache
     */
    public function invalidateSAWResults(string $period = null): void
    {
        if ($period) {
            $key = $this->generateKey(self::PREFIX_SAW_RESULTS, $period);
            Cache::forget($key);
            Log::info("Invalidated SAW results cache for period: {$period}");
        } else {
            $this->invalidateByPrefix(self::PREFIX_SAW_RESULTS);
            Log::info("Invalidated all SAW results cache");
        }
    }

    /**
     * Invalidate dashboard cache
     */
    public function invalidateDashboard(): void
    {
        $this->invalidateByPrefix(self::PREFIX_DASHBOARD);
        Log::info("Invalidated dashboard cache");
    }

    /**
     * Invalidate chart data cache
     */
    public function invalidateChartData(string $type = null): void
    {
        if ($type) {
            $pattern = $this->generateKey(self::PREFIX_CHART_DATA, $type, '*');
            $this->invalidateByPattern($pattern);
            Log::info("Invalidated chart data cache for type: {$type}");
        } else {
            $this->invalidateByPrefix(self::PREFIX_CHART_DATA);
            Log::info("Invalidated all chart data cache");
        }
    }

    /**
     * Invalidate navigation cache
     */
    public function invalidateNavigation(): void
    {
        $this->invalidateByPrefix(self::PREFIX_NAVIGATION);
        Log::info("Invalidated navigation cache");
    }

    /**
     * Invalidate employee data cache
     */
    public function invalidateEmployeeData(string $identifier = null): void
    {
        if ($identifier) {
            $key = $this->generateKey(self::PREFIX_EMPLOYEE_DATA, $identifier);
            Cache::forget($key);
            Log::info("Invalidated employee data cache for: {$identifier}");
        } else {
            $this->invalidateByPrefix(self::PREFIX_EMPLOYEE_DATA);
            Log::info("Invalidated all employee data cache");
        }
    }

    /**
     * Invalidate criteria data cache
     */
    public function invalidateCriteriaData(): void
    {
        $this->invalidateByPrefix(self::PREFIX_CRITERIA_DATA);
        Log::info("Invalidated criteria data cache");
    }

    /**
     * Invalidate evaluation data cache
     */
    public function invalidateEvaluationData(string $identifier = null): void
    {
        if ($identifier) {
            $key = $this->generateKey(self::PREFIX_EVALUATION_DATA, $identifier);
            Cache::forget($key);
            Log::info("Invalidated evaluation data cache for: {$identifier}");
        } else {
            $this->invalidateByPrefix(self::PREFIX_EVALUATION_DATA);
            Log::info("Invalidated all evaluation data cache");
        }
    }

    /**
     * Invalidate periods cache
     */
    public function invalidatePeriods(): void
    {
        $this->invalidateByPrefix(self::PREFIX_PERIODS);
        Log::info("Invalidated periods cache");
    }

    /**
     * Clear all application caches
     */
    public function clearAll(): void
    {
        $prefixes = [
            self::PREFIX_SAW_RESULTS,
            self::PREFIX_DASHBOARD,
            self::PREFIX_CHART_DATA,
            self::PREFIX_NAVIGATION,
            self::PREFIX_EMPLOYEE_DATA,
            self::PREFIX_CRITERIA_DATA,
            self::PREFIX_EVALUATION_DATA,
            self::PREFIX_PERIODS
        ];

        foreach ($prefixes as $prefix) {
            $this->invalidateByPrefix($prefix);
        }

        Log::info("Cleared all application caches");
    }

    /**
     * Generate cache key
     */
    private function generateKey(string ...$parts): string
    {
        $sanitizedParts = array_map(function ($part) {
            return str_replace([':', ' ', '/', '\\'], '_', $part);
        }, $parts);

        return 'saw_' . implode('_', $sanitizedParts);
    }

    /**
     * Invalidate cache by prefix
     */
    private function invalidateByPrefix(string $prefix): void
    {
        $pattern = $this->generateKey($prefix, '*');
        $this->invalidateByPattern($pattern);
    }

    /**
     * Invalidate cache by pattern
     */
    private function invalidateByPattern(string $pattern): void
    {
        try {
            // For database cache driver, we need to manually query and delete
            if (config('cache.default') === 'database') {
                $keys = \DB::table('cache')
                    ->where('key', 'like', str_replace('*', '%', $pattern))
                    ->pluck('key');

                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            } else {
                // For other drivers, this might not work perfectly
                // but we'll implement a basic version
                Cache::flush();
            }
        } catch (\Exception $e) {
            Log::warning("Failed to invalidate cache by pattern: {$pattern}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $prefixes = [
                'SAW Results' => self::PREFIX_SAW_RESULTS,
                'Dashboard' => self::PREFIX_DASHBOARD,
                'Chart Data' => self::PREFIX_CHART_DATA,
                'Navigation' => self::PREFIX_NAVIGATION,
                'Employee Data' => self::PREFIX_EMPLOYEE_DATA,
                'Criteria Data' => self::PREFIX_CRITERIA_DATA,
                'Evaluation Data' => self::PREFIX_EVALUATION_DATA,
                'Periods' => self::PREFIX_PERIODS
            ];

            $stats = [];
            foreach ($prefixes as $name => $prefix) {
                $pattern = $this->generateKey($prefix, '%');
                $count = \DB::table('cache')
                    ->where('key', 'like', $pattern)
                    ->count();
                $stats[$name] = $count;
            }

            return $stats;
        } catch (\Exception $e) {
            Log::warning("Failed to get cache stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Warm up critical caches
     */
    public function warmUp(): void
    {
        try {
            Log::info("Starting cache warm up...");

            // Warm up navigation periods
            $this->getEvaluationPeriods(function () {
                return \App\Models\Evaluation::distinct('evaluation_period')
                    ->orderByDesc('evaluation_period')
                    ->pluck('evaluation_period');
            });

            // Warm up criteria data
            $this->getCriteriaData('all', function () {
                return \App\Models\Criteria::orderBy('weight', 'desc')->get();
            });

            // Warm up employees list
            $this->getEmployeeData('all', function () {
                return \App\Models\Employee::orderBy('name')->get(['id', 'name', 'employee_code']);
            });

            Log::info("Cache warm up completed");
        } catch (\Exception $e) {
            Log::error("Cache warm up failed", ['error' => $e->getMessage()]);
        }
    }
}



