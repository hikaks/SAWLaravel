<?php

namespace App\Services;

use App\Models\AnalysisHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AnalysisHistoryService
{
    /**
     * Record a new analysis
     */
    public function recordAnalysis(
        string $analysisType,
        array $parameters = [],
        array $resultsSummary = [],
        int $executionTimeMs = 0,
        string $status = 'completed',
        ?string $errorMessage = null
    ): AnalysisHistory {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            $analysisHistory = AnalysisHistory::create([
                'user_id' => $userId,
                'analysis_type' => $analysisType,
                'evaluation_period' => $parameters['evaluation_period'] ?? null,
                'parameters' => $parameters,
                'results_summary' => $resultsSummary,
                'execution_time_ms' => $executionTimeMs,
                'status' => $status,
                'error_message' => $errorMessage
            ]);

            Log::info("Analysis recorded", [
                'id' => $analysisHistory->id,
                'type' => $analysisType,
                'user_id' => $userId,
                'execution_time' => $executionTimeMs
            ]);

            return $analysisHistory;

        } catch (\Exception $e) {
            Log::error("Failed to record analysis", [
                'type' => $analysisType,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get analysis history for current user
     */
    public function getUserHistory(
        int $limit = 20,
        ?string $analysisType = null,
        ?string $period = null
    ): Collection {
        $query = AnalysisHistory::forUser(Auth::id())
            ->with('user')
            ->orderByDesc('created_at');

        if ($analysisType) {
            $query->ofType($analysisType);
        }

        if ($period) {
            $query->forPeriod($period);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get analysis history for admin (all users)
     */
    public function getAllHistory(
        int $limit = 50,
        ?string $analysisType = null,
        ?string $period = null,
        ?int $userId = null
    ): Collection {
        $query = AnalysisHistory::with('user')
            ->orderByDesc('created_at');

        if ($analysisType) {
            $query->ofType($analysisType);
        }

        if ($period) {
            $query->forPeriod($period);
        }

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get analysis statistics
     */
    public function getStatistics(?int $userId = null): array
    {
        return AnalysisHistory::getStatistics($userId);
    }

    /**
     * Get analysis by ID
     */
    public function getAnalysis(int $id): ?AnalysisHistory
    {
        $analysis = AnalysisHistory::with('user')->find($id);
        
        // Check if user can access this analysis
        if ($analysis && !Auth::user()->isAdmin() && $analysis->user_id !== Auth::id()) {
            return null;
        }
        
        return $analysis;
    }

    /**
     * Delete analysis
     */
    public function deleteAnalysis(int $id): bool
    {
        $analysis = AnalysisHistory::find($id);
        
        if (!$analysis) {
            return false;
        }

        // Check if user can delete this analysis
        if (!Auth::user()->isAdmin() && $analysis->user_id !== Auth::id()) {
            return false;
        }

        try {
            $analysis->delete();
            
            Log::info("Analysis deleted", [
                'id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to delete analysis", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get recent analysis trends
     */
    public function getRecentTrends(int $days = 30): array
    {
        $query = AnalysisHistory::query();
        
        if (!Auth::user()->isAdmin()) {
            $query->forUser(Auth::id());
        }

        $recentAnalyses = $query->recent($days)
            ->selectRaw('DATE(created_at) as date, analysis_type, COUNT(*) as count')
            ->groupBy('date', 'analysis_type')
            ->orderBy('date')
            ->get();

        $trends = [];
        $analysisTypes = ['sensitivity', 'what-if', 'comparison', 'forecast', 'statistics'];

        foreach ($recentAnalyses as $analysis) {
            if (!isset($trends[$analysis->date])) {
                $trends[$analysis->date] = array_fill_keys($analysisTypes, 0);
            }
            $trends[$analysis->date][$analysis->analysis_type] = $analysis->count;
        }

        return $trends;
    }

    /**
     * Get analysis performance metrics
     */
    public function getPerformanceMetrics(int $days = 30): array
    {
        $query = AnalysisHistory::query();
        
        if (!Auth::user()->isAdmin()) {
            $query->forUser(Auth::id());
        }

        $metrics = $query->recent($days)
            ->selectRaw('
                analysis_type,
                COUNT(*) as total,
                AVG(execution_time_ms) as avg_execution_time,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
                COUNT(CASE WHEN status = "failed" THEN 1 END) as failed
            ')
            ->groupBy('analysis_type')
            ->get();

        $performance = [];
        foreach ($metrics as $metric) {
            $performance[$metric->analysis_type] = [
                'total' => $metric->total,
                'completed' => $metric->completed,
                'failed' => $metric->failed,
                'success_rate' => $metric->total > 0 ? round(($metric->completed / $metric->total) * 100, 2) : 0,
                'avg_execution_time_ms' => round($metric->avg_execution_time ?? 0, 2),
                'avg_execution_time_readable' => $this->formatExecutionTime($metric->avg_execution_time ?? 0)
            ];
        }

        return $performance;
    }

    /**
     * Clean up old analysis history
     */
    public function cleanupOldHistory(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        $deletedCount = AnalysisHistory::where('created_at', '<', $cutoffDate)->delete();
        
        Log::info("Cleaned up old analysis history", [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateString()
        ]);
        
        return $deletedCount;
    }

    /**
     * Export analysis history
     */
    public function exportHistory(
        ?string $analysisType = null,
        ?string $period = null,
        ?int $userId = null
    ): Collection {
        $query = AnalysisHistory::with('user')
            ->orderByDesc('created_at');

        if ($analysisType) {
            $query->ofType($analysisType);
        }

        if ($period) {
            $query->forPeriod($period);
        }

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->get();
    }

    /**
     * Format execution time
     */
    private function formatExecutionTime(int $ms): string
    {
        if ($ms < 1000) {
            return $ms . 'ms';
        } elseif ($ms < 60000) {
            return round($ms / 1000, 2) . 's';
        } else {
            return round($ms / 60000, 2) . 'm';
        }
    }
}

