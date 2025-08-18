<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'criteria_id',
        'score',
        'evaluation_period',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'criteria_id' => 'integer',
        'score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the employee that owns this evaluation.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the criteria that owns this evaluation.
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }

    /**
     * Get normalized score for SAW calculation.
     */
    public function getNormalizedScore(array $allScores): float
    {
        if ($this->criteria->isBenefit()) {
            // For benefit criteria: Rij = Xij / Max(Xij)
            $maxScore = max($allScores);
            return $maxScore > 0 ? $this->score / $maxScore : 0;
        } else {
            // For cost criteria: Rij = Min(Xij) / Xij
            $minScore = min($allScores);
            return $this->score > 0 ? $minScore / $this->score : 0;
        }
    }

    /**
     * Scope for specific evaluation period.
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('evaluation_period', $period);
    }

    /**
     * Scope for specific employee.
     */
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for specific criteria.
     */
    public function scopeForCriteria($query, int $criteriaId)
    {
        return $query->where('criteria_id', $criteriaId);
    }
}
