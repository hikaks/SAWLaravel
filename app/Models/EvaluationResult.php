<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'total_score',
        'ranking',
        'evaluation_period',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'total_score' => 'decimal:4',
        'ranking' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the employee that owns this result.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get formatted total score as percentage.
     */
    public function getScorePercentageAttribute(): float
    {
        return round($this->total_score * 100, 2);
    }

    /**
     * Get ranking with ordinal suffix.
     */
    public function getRankingTextAttribute(): string
    {
        $suffix = match (true) {
            $this->ranking % 100 >= 11 && $this->ranking % 100 <= 13 => 'th',
            $this->ranking % 10 == 1 => 'st',
            $this->ranking % 10 == 2 => 'nd',
            $this->ranking % 10 == 3 => 'rd',
            default => 'th'
        };

        return $this->ranking . $suffix;
    }

    /**
     * Get ranking category.
     */
    public function getRankingCategoryAttribute(): string
    {
        return match (true) {
            $this->ranking <= 3 => 'Excellent',
            $this->ranking <= 10 => 'Good',
            $this->ranking <= 20 => 'Average',
            default => 'Needs Improvement'
        };
    }

    /**
     * Scope for specific evaluation period.
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('evaluation_period', $period);
    }

    /**
     * Scope for top performers.
     */
    public function scopeTopPerformers($query, int $limit = 10)
    {
        return $query->orderBy('ranking')->limit($limit);
    }

    /**
     * Scope ordered by ranking.
     */
    public function scopeOrderedByRanking($query)
    {
        return $query->orderBy('ranking');
    }

    /**
     * Scope ordered by score.
     */
    public function scopeOrderedByScore($query)
    {
        return $query->orderByDesc('total_score');
    }
}
