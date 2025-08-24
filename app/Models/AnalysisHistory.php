<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalysisHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'analysis_history';

    protected $fillable = [
        'user_id',
        'analysis_type',
        'evaluation_period',
        'parameters',
        'results_summary',
        'execution_time_ms',
        'status',
        'error_message'
    ];

    protected $casts = [
        'parameters' => 'array',
        'results_summary' => 'array',
        'execution_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the user that performed this analysis
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get analysis type display name
     */
    public function getAnalysisTypeDisplayAttribute(): string
    {
        return match($this->analysis_type) {
            'sensitivity' => 'Sensitivity Analysis',
            'what-if' => 'What-if Scenarios',
            'comparison' => 'Multi-period Comparison',
            'forecast' => 'Performance Forecasting',
            'statistics' => 'Advanced Statistics',
            default => ucfirst(str_replace('-', ' ', $this->analysis_type))
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'warning',
            'running' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get execution time in readable format
     */
    public function getExecutionTimeReadableAttribute(): string
    {
        if ($this->execution_time_ms < 1000) {
            return $this->execution_time_ms . 'ms';
        } elseif ($this->execution_time_ms < 60000) {
            return round($this->execution_time_ms / 1000, 2) . 's';
        } else {
            return round($this->execution_time_ms / 60000, 2) . 'm';
        }
    }

    /**
     * Get parameters summary for display
     */
    public function getParametersSummaryAttribute(): string
    {
        if (empty($this->parameters)) {
            return 'No parameters';
        }

        $summary = [];
        
        if (isset($this->parameters['evaluation_period'])) {
            $summary[] = 'Period: ' . $this->parameters['evaluation_period'];
        }
        
        if (isset($this->parameters['weight_changes'])) {
            $summary[] = 'Weight changes: ' . count($this->parameters['weight_changes']) . ' criteria';
        }
        
        if (isset($this->parameters['periods'])) {
            $summary[] = 'Periods: ' . count($this->parameters['periods']);
        }
        
        if (isset($this->parameters['employee_id'])) {
            $summary[] = 'Employee: ' . $this->parameters['employee_id'];
        }

        return implode(', ', $summary);
    }

    /**
     * Get results summary for display
     */
    public function getResultsSummaryAttribute(): string
    {
        if (empty($this->results_summary)) {
            return 'No results';
        }

        $summary = [];
        
        if (isset($this->results_summary['total_scenarios'])) {
            $summary[] = $this->results_summary['total_scenarios'] . ' scenarios';
        }
        
        if (isset($this->results_summary['total_employees'])) {
            $summary[] = $this->results_summary['total_employees'] . ' employees';
        }
        
        if (isset($this->results_summary['stability_index'])) {
            $summary[] = 'Stability: ' . round($this->results_summary['stability_index'] * 100, 1) . '%';
        }

        return implode(', ', $summary);
    }

    /**
     * Scope for specific analysis type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('analysis_type', $type);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific evaluation period
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('evaluation_period', $period);
    }

    /**
     * Scope for completed analyses
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed analyses
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for recent analyses
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get analysis statistics
     */
    public static function getStatistics(int $userId = null): array
    {
        $query = static::query();
        
        if ($userId) {
            $query->forUser($userId);
        }

        $total = $query->count();
        $completed = $query->clone()->completed()->count();
        $failed = $query->clone()->failed()->count();
        $avgExecutionTime = $query->clone()->completed()->avg('execution_time_ms');

        return [
            'total_analyses' => $total,
            'completed_analyses' => $completed,
            'failed_analyses' => $failed,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'avg_execution_time_ms' => round($avgExecutionTime ?? 0, 2),
            'avg_execution_time_readable' => $total > 0 ? static::formatExecutionTime($avgExecutionTime ?? 0) : '0ms'
        ];
    }

    /**
     * Format execution time
     */
    private static function formatExecutionTime(int $ms): string
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

